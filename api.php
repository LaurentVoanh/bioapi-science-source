<?php
declare(strict_types=1);
error_reporting(0);
ini_set('display_errors', '0');
ob_start();

require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

// Initialise toujours la DB (idempotent grâce à IF NOT EXISTS)
db();

function send(array $d): void
{
    while (ob_get_level()) ob_end_clean();
    echo json_encode($d, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    if (!$action) throw new Exception('Missing action');

    switch ($action) {

        // ── SANITY CHECK ───────────────────────────────────────────────
        case 'health':
            send([
                'success' => true,
                'data'    => [
                    'status'  => 'ok',
                    'php'     => PHP_VERSION,
                    'sources' => count(SOURCES),
                    'version' => APP_VERSION,
                    'db'      => DB_PATH,
                ],
            ]);

        // ── LISTE ARTICLES ─────────────────────────────────────────────
        case 'get_articles':
            $rows = db()
                ->query("SELECT id, topic, title, summary, sources_ok, total_hits, word_count, created_at FROM articles ORDER BY created_at DESC LIMIT 100")
                ->fetchAll();
            send(['success' => true, 'data' => $rows]);

        // ── UN ARTICLE ─────────────────────────────────────────────────
        case 'get_article':
            $id   = (int)($_GET['id'] ?? 0);
            $stmt = db()->prepare("SELECT * FROM articles WHERE id = ?");
            $stmt->execute([$id]);
            $art  = $stmt->fetch();
            if (!$art) throw new Exception("Article {$id} not found");

            $stmt2 = db()->prepare(
                "SELECT source, COUNT(*) as cnt FROM findings WHERE session_id = ? GROUP BY source ORDER BY cnt DESC"
            );
            $stmt2->execute([$art['session_id']]);
            $by_source = $stmt2->fetchAll();

            send(['success' => true, 'data' => ['article' => $art, 'by_source' => $by_source]]);

        // ── ÉTAPE 1 : CHOISIR UN SUJET ─────────────────────────────────
        case 'step_pick_topic':
            app_log('Asking Mistral for topic', 'pick_topic');

            // Éviter les doublons récents
            $done = db()->query("SELECT topic FROM articles ORDER BY created_at DESC LIMIT 30")->fetchAll(PDO::FETCH_COLUMN);
            $avoid = $done ? 'Évite absolument: ' . implode(', ', $done) . '.' : '';

            $raw = mistral(
                "Choisis UN sujet de recherche scientifique ou médicale précis, récent (2020–2025), avec des données disponibles dans les grandes bases. {$avoid}\n" .
                "Réponds UNIQUEMENT avec le sujet en anglais, 3–6 mots, sans ponctuation. Exemple: 'CRISPR off-target liver effects'",
                'Tu es un directeur de recherche qui choisit des sujets à fort impact.',
                60
            );

            $topic = trim(preg_replace('/[^\w\s\-]/u', '', $raw));
            if (strlen($topic) < 4) $topic = 'Neuroinflammation mechanisms 2024';

            // Créer la session
            $sid = 'sess_' . bin2hex(random_bytes(8));
            db()->prepare("INSERT INTO sessions (id, topic, status) VALUES (?, ?, 'running')")->execute([$sid, $topic]);

            app_log("Topic: {$topic} | Session: {$sid}", 'pick_topic');
            send(['success' => true, 'data' => ['session_id' => $sid, 'topic' => $topic]]);

        // ── ÉTAPE 2 : PRÉPARER LES REQUÊTES ────────────────────────────
        case 'step_prepare_queries':
            $sid   = $_POST['session_id'] ?? '';
            $topic = $_POST['topic']      ?? '';
            if (!$sid || !$topic) throw new Exception('Missing session_id or topic');

            app_log("Preparing queries for: {$topic}", 'prepare');

            // Mistral génère un terme de recherche court et efficace
            $term_raw = mistral(
                "Pour rechercher '{$topic}' dans des APIs scientifiques REST, donne-moi le meilleur terme de recherche: court (2-4 mots), en anglais, sans opérateurs. Réponds UNIQUEMENT avec le terme.",
                'Expert en recherche bibliographique.',
                40
            );
            $term = trim(preg_replace('/[^\w\s\-]/u', '', $term_raw));
            if (strlen($term) < 2) $term = $topic;

            $encoded = urlencode($term);
            $sources = SOURCES;
            $prepared = [];

            $ins = db()->prepare("INSERT INTO queries (session_id, source, url, status) VALUES (?, ?, ?, 'pending')");
            foreach ($sources as $src_name => $url_tpl) {
                $url = str_replace('{query}', $encoded, $url_tpl);
                $ins->execute([$sid, $src_name, $url]);
                $prepared[] = ['id' => (int)db()->lastInsertId(), 'source' => $src_name];
            }

            app_log(count($prepared) . " queries prepared, term: {$term}", 'prepare');
            send(['success' => true, 'data' => ['term' => $term, 'queries' => $prepared]]);

        // ── ÉTAPE 3 : EXÉCUTER UNE REQUÊTE ─────────────────────────────
        case 'step_exec_query':
            $qid = (int)($_POST['query_id'] ?? 0);
            if ($qid <= 0) throw new Exception('Invalid query_id');

            $stmt = db()->prepare("SELECT * FROM queries WHERE id = ?");
            $stmt->execute([$qid]);
            $q = $stmt->fetch();
            if (!$q) throw new Exception("Query {$qid} not found");

            $t0  = microtime(true);
            $res = http_get($q['url']);
            $dur = (int)round((microtime(true) - $t0) * 1000);
            $ok  = ($res['code'] >= 200 && $res['code'] < 300);

            $items = $ok ? parse_response($q['source'], $res['body']) : [];
            $hits  = count($items);

            // Persister les findings
            if ($hits > 0) {
                $ins = db()->prepare(
                    "INSERT INTO findings (session_id, source, title, abstract, year, url) VALUES (?, ?, ?, ?, ?, ?)"
                );
                foreach ($items as $item) {
                    $ins->execute([
                        $q['session_id'],
                        $q['source'],
                        substr($item['title']    ?? '', 0, 400),
                        substr($item['abstract'] ?? '', 0, 800),
                        substr($item['year']     ?? '', 0,  10),
                        substr($item['url']      ?? '', 0, 500),
                    ]);
                }
            }

            db()->prepare(
                "UPDATE queries SET status=?, http_code=?, duration_ms=?, hits=? WHERE id=?"
            )->execute([$ok ? 'ok' : 'fail', $res['code'], $dur, $hits, $qid]);

            app_log("[{$q['source']}] HTTP {$res['code']} {$dur}ms hits={$hits}", 'exec');

            send(['success' => true, 'data' => [
                'query_id' => $qid,
                'source'   => $q['source'],
                'ok'       => $ok,
                'code'     => $res['code'],
                'ms'       => $dur,
                'hits'     => $hits,
            ]]);

        // ── ÉTAPE 4 : RÉDIGER L'ARTICLE ────────────────────────────────
        case 'step_write_article':
            $sid   = $_POST['session_id'] ?? '';
            $topic = $_POST['topic']      ?? '';
            if (!$sid || !$topic) throw new Exception('Missing params');

            app_log("Writing article for: {$topic}", 'write');

            // Récupérer tous les findings
            $stmt = db()->prepare(
                "SELECT source, title, abstract, year FROM findings WHERE session_id = ? AND title != '' ORDER BY source, id"
            );
            $stmt->execute([$sid]);
            $findings = $stmt->fetchAll();

            // Stats sources
            $stat_stmt = db()->prepare("SELECT source, status, hits FROM queries WHERE session_id = ?");
            $stat_stmt->execute([$sid]);
            $stats = $stat_stmt->fetchAll();
            $sources_ok = array_filter($stats, fn($s) => $s['status'] === 'ok' && $s['hits'] > 0);

            // Construire le contexte pour Mistral (groupé par source)
            $grouped = [];
            foreach ($findings as $f) $grouped[$f['source']][] = $f;

            $ctx_parts = [];
            foreach ($grouped as $src => $items) {
                $ctx_parts[] = "\n### {$src}";
                foreach (array_slice($items, 0, 5) as $item) {
                    $line = '- ' . $item['title'];
                    if ($item['year']) $line .= " ({$item['year']})";
                    if ($item['abstract']) $line .= "\n  > " . substr($item['abstract'], 0, 250);
                    $ctx_parts[] = $line;
                }
            }
            $ctx = substr(implode("\n", $ctx_parts), 0, 14000);

            $n_ok      = count($sources_ok);
            $n_findings = count($findings);

            // Rédaction
            $content = mistral(
                "Tu es un journaliste scientifique senior. Rédige un article de synthèse COMPLET sur:\n\n**{$topic}**\n\n" .
                "Tu disposes de {$n_findings} résultats collectés depuis {$n_ok} bases de données scientifiques internationales:\n{$ctx}\n\n" .
                "CONSIGNES:\n" .
                "- Minimum 3000 mots\n" .
                "- Format Markdown strict avec ces sections:\n" .
                "  ## Résumé exécutif\n" .
                "  ## Introduction\n" .
                "  ## État de l'art\n" .
                "  ## Mécanismes et données clés\n" .
                "  ## Avancées récentes (2022–2025)\n" .
                "  ## Implications cliniques et thérapeutiques\n" .
                "  ## Lacunes et défis\n" .
                "  ## Perspectives\n" .
                "  ## Conclusion\n" .
                "  ## Sources consultées\n" .
                "- Cite nommément les bases de données utilisées\n" .
                "- Inclus des données chiffrées quand disponibles\n" .
                "- Niveau: Nature/Science, accessible au grand public instruit\n\n" .
                "Rédige l'article complet maintenant:",
                'Tu es un expert mondial en synthèse de littérature scientifique. Tu maîtrises toutes les disciplines.',
                8000
            );

            if (!$content) throw new Exception('Mistral returned empty content');

            // Titre court
            $title = trim(mistral(
                "Génère un titre court, percutant et scientifique (max 90 caractères) pour un article sur: {$topic}. Réponds UNIQUEMENT avec le titre.",
                'Expert en communication scientifique.',
                80
            ));
            if (strlen($title) < 5) $title = $topic;

            // Résumé
            $summary = trim(mistral(
                "Résume en 2 phrases cet article sur '{$topic}' en mentionnant les {$n_ok} sources et {$n_findings} données collectées. Max 280 caractères.",
                'Expert en communication scientifique.',
                150
            ));

            $words = str_word_count(strip_tags($content));

            $ins = db()->prepare(
                "INSERT INTO articles (session_id, topic, title, summary, content, sources_ok, total_hits, word_count) VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $ins->execute([$sid, $topic, substr($title, 0, 200), substr($summary, 0, 500), $content, $n_ok, $n_findings, $words]);
            $article_id = (int)db()->lastInsertId();

            db()->prepare("UPDATE sessions SET status='done' WHERE id=?")->execute([$sid]);

            app_log("Article #{$article_id} written: {$words} words, {$n_ok} sources", 'write');

            send(['success' => true, 'data' => [
                'article_id' => $article_id,
                'title'      => $title,
                'word_count' => $words,
                'sources_ok' => $n_ok,
                'total_hits' => $n_findings,
            ]]);

        default:
            throw new Exception("Unknown action: {$action}");
    }
} catch (Throwable $e) {
    app_log('ERROR: ' . $e->getMessage() . ' @ ' . basename($e->getFile()) . ':' . $e->getLine(), 'error');
    send(['success' => false, 'error' => $e->getMessage()]);
}
