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

            // Récupérer les findings avec leurs URLs
            $stmt3 = db()->prepare(
                "SELECT source, title, url, source_url FROM findings WHERE session_id = ? AND (url != '' OR source_url != '') ORDER BY source LIMIT 100"
            );
            $stmt3->execute([$art['session_id']]);
            $findings_with_links = $stmt3->fetchAll();

            send(['success' => true, 'data' => [
                'article'             => $art,
                'by_source'           => $by_source,
                'findings_with_links' => $findings_with_links,
            ]]);

        // ── ÉTAPE 1 : CHOISIR UN SUJET ─────────────────────────────────
        case 'step_pick_topic':
            $question = trim($_POST['question'] ?? '');
            app_log("Pick topic, question: '{$question}'", 'pick_topic');

            if ($question) {
                // Mode question : on utilise directement la question comme sujet
                $topic = $question;
            } else {
                // Mode auto : Mistral choisit
                $done  = db()->query("SELECT topic FROM articles ORDER BY created_at DESC LIMIT 30")->fetchAll(PDO::FETCH_COLUMN);
                $avoid = $done ? 'Évite absolument ces sujets déjà traités: ' . implode(', ', $done) . '.' : '';

                $raw = mistral(
                    "Choisis UN sujet de recherche scientifique ou médicale précis, récent (2020–2025), avec des données disponibles dans les grandes bases scientifiques. {$avoid}\n" .
                    "Réponds UNIQUEMENT avec le sujet en anglais, 3–7 mots, sans ponctuation ni guillemets. Exemple: CRISPR off-target liver effects 2024",
                    'Tu es un directeur de recherche biomédicale qui choisit des sujets à fort impact scientifique.',
                    80
                );

                $topic = trim(preg_replace('/[^\w\s\-]/u', '', $raw));
                if (strlen($topic) < 4) $topic = 'Neuroinflammation mechanisms 2024';
            }

            $sid = 'sess_' . bin2hex(random_bytes(8));
            $mode = $question ? 'question' : 'auto';
            db()->prepare("INSERT INTO sessions (id, topic, status, mode) VALUES (?, ?, 'running', ?)")->execute([$sid, $topic, $mode]);

            app_log("Topic: {$topic} | Session: {$sid} | Mode: {$mode}", 'pick_topic');
            send(['success' => true, 'data' => ['session_id' => $sid, 'topic' => $topic]]);

        // ── ÉTAPE 2 : PRÉPARER LES REQUÊTES ────────────────────────────
        case 'step_prepare_queries':
            $sid   = $_POST['session_id'] ?? '';
            $topic = $_POST['topic']      ?? '';
            if (!$sid || !$topic) throw new Exception('Missing session_id or topic');

            app_log("Preparing queries for: {$topic}", 'prepare');

            // Construire le prompt expliquant les formats de chaque API
            $format_guide = build_query_format_prompt();

            // Mistral génère les termes pour CHAQUE source
            $prompt = <<<PROMPT
Tu es un expert en APIs scientifiques. Pour le sujet de recherche: "{$topic}"

Tu dois générer le terme de recherche optimal pour chacune des 36 sources suivantes.
Voici les formats et contraintes pour chaque source :

{$format_guide}

RÈGLES IMPORTANTES :
- Pour chaque source, fournis UNIQUEMENT le terme à insérer à la place de {TERM}
- Le terme doit respecter le format décrit pour chaque source
- Pour les sources basées sur un nom de gène (Ensembl, StringDB, NCBI_Gene), utilise le symbole officiel du gène le plus pertinent pour le sujet
- Pour Reactome, utilise l'accession UniProt de la protéine principale (ex: P04637)
- Pour WorldBank, utilise un code indicateur valide (ex: SH.XPD.CHEX.GD.ZS)
- Pour BioGRID, retourne exactement "STATIC" (source sans variable)
- Pour Unpaywall, retourne "SKIP" si aucun DOI spécifique n'est pertinent
- N'invente PAS de termes pour des sources non-biomédicales si le sujet est médical
- Favorise l'anglais, les termes simples et précis

Réponds en JSON strict, une clé par source, valeur = terme à utiliser :
{
  "PubMed": "terme recherche pubmed",
  "EuropePMC": "terme europepmc",
  ...
}

IMPORTANT: Réponds UNIQUEMENT avec le JSON, sans texte avant ni après.
PROMPT;

            $raw = mistral($prompt, 'Expert API scientifiques. Réponds uniquement en JSON valide.', 2000);

            // Parser le JSON
            $raw = preg_replace('/^```json\s*/m', '', $raw);
            $raw = preg_replace('/^```\s*/m', '', $raw);
            $terms = @json_decode(trim($raw), true);

            // Fallback si JSON invalide : utiliser un terme générique
            if (!is_array($terms)) {
                app_log("Mistral JSON parse failed, using generic term", 'prepare');
                $generic = strtolower(str_replace(' ', '+', $topic));
                $terms = [];
                foreach (array_keys(SOURCES_CONFIG) as $src) {
                    $terms[$src] = $generic;
                }
            }

            // Construire les requêtes avec les termes générés
            $ins = db()->prepare("INSERT INTO queries (session_id, source, url, term, status) VALUES (?, ?, ?, ?, 'pending')");
            $prepared = [];

            foreach (SOURCES_CONFIG as $src_name => $cfg) {
                $term = $terms[$src_name] ?? '';

                // Gérer les cas spéciaux
                if ($src_name === 'BioGRID' || $term === 'STATIC') {
                    // URL statique sans variable
                    $url = $cfg['url'];
                    $term = 'static';
                } elseif (empty($term) || $term === 'SKIP') {
                    // Skipper cette source
                    continue;
                } else {
                    // Encoder le terme et l'insérer dans l'URL
                    $encoded = urlencode($term);
                    $url = str_replace('{TERM}', $encoded, $cfg['url']);

                    // Cas spécial PDB — le JSON dans l'URL doit rester intact
                    if ($src_name === 'PDB') {
                        $url = str_replace('{TERM}', addslashes($term), $cfg['url']);
                    }
                }

                $ins->execute([$sid, $src_name, $url, $term]);
                $prepared[] = ['id' => (int)db()->lastInsertId(), 'source' => $src_name];
            }

            app_log(count($prepared) . " queries prepared for: {$topic}", 'prepare');
            send(['success' => true, 'data' => ['term' => $topic, 'queries' => $prepared]]);

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

            $items = $ok ? parse_response($q['source'], $res['body'], $q['url']) : [];
            $hits  = count($items);

            if ($hits > 0) {
                $ins = db()->prepare(
                    "INSERT INTO findings (session_id, source, title, abstract, year, url, source_url) VALUES (?, ?, ?, ?, ?, ?, ?)"
                );
                foreach ($items as $item) {
                    $ins->execute([
                        $q['session_id'],
                        $q['source'],
                        substr($item['title']      ?? '', 0, 400),
                        substr($item['abstract']   ?? '', 0, 800),
                        substr($item['year']       ?? '', 0,  10),
                        substr($item['url']        ?? '', 0, 500),
                        substr($item['source_url'] ?? '', 0, 500),
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
                'url'      => $q['url'],
            ]]);

        // ── ÉTAPE 4 : RÉDIGER L'ARTICLE ────────────────────────────────
        case 'step_write_article':
            $sid   = $_POST['session_id'] ?? '';
            $topic = $_POST['topic']      ?? '';
            if (!$sid || !$topic) throw new Exception('Missing params');

            app_log("Writing article for: {$topic}", 'write');

            $stmt = db()->prepare(
                "SELECT source, title, abstract, year, url, source_url FROM findings WHERE session_id = ? AND title != '' ORDER BY source, id"
            );
            $stmt->execute([$sid]);
            $findings = $stmt->fetchAll();

            $stat_stmt = db()->prepare("SELECT source, status, hits FROM queries WHERE session_id = ?");
            $stat_stmt->execute([$sid]);
            $stats = $stat_stmt->fetchAll();
            $sources_ok = array_filter($stats, fn($s) => $s['status'] === 'ok' && $s['hits'] > 0);

            $grouped = [];
            foreach ($findings as $f) $grouped[$f['source']][] = $f;

            $ctx_parts = [];
            foreach ($grouped as $src => $items) {
                $ctx_parts[] = "\n### {$src}";
                foreach (array_slice($items, 0, 5) as $item) {
                    $line = '- ' . $item['title'];
                    if ($item['year']) $line .= " ({$item['year']})";
                    if ($item['abstract']) $line .= "\n  > " . substr($item['abstract'], 0, 300);
                    if ($item['source_url']) $line .= "\n  URL: " . $item['source_url'];
                    $ctx_parts[] = $line;
                }
            }
            $ctx = substr(implode("\n", $ctx_parts), 0, 14000);

            $n_ok       = count($sources_ok);
            $n_findings = count($findings);

            $content = mistral(
                "Tu es un journaliste scientifique senior. Rédige un article de synthèse COMPLET sur:\n\n**{$topic}**\n\n" .
                "Tu disposes de {$n_findings} résultats collectés depuis {$n_ok} bases de données scientifiques internationales:\n{$ctx}\n\n" .
                "CONSIGNES:\n" .
                "- Minimum 3000 mots\n" .
                "- Format Markdown avec ces sections:\n" .
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
                "- Dans la section Sources consultées, liste TOUTES les bases interrogées avec leurs URLs si disponibles\n" .
                "- Cite nommément les bases de données utilisées dans le texte\n" .
                "- Inclus des données chiffrées quand disponibles\n" .
                "- Niveau: Nature/Science, accessible au grand public instruit\n\n" .
                "Rédige l'article complet maintenant:",
                'Tu es un expert mondial en synthèse de littérature scientifique. Tu maîtrises toutes les disciplines.',
                8000
            );

            if (!$content) throw new Exception('Mistral returned empty content');

            $title = trim(mistral(
                "Génère un titre court, percutant et scientifique (max 90 caractères) pour un article sur: {$topic}. Réponds UNIQUEMENT avec le titre.",
                'Expert en communication scientifique.',
                80
            ));
            if (strlen($title) < 5) $title = $topic;

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

        // ── RECHERCHE APPROFONDIE ───────────────────────────────────────
        case 'step_deep_research':
            $article_id = (int)($_POST['article_id'] ?? 0);
            if (!$article_id) throw new Exception('Missing article_id');

            $stmt = db()->prepare("SELECT * FROM articles WHERE id = ?");
            $stmt->execute([$article_id]);
            $art = $stmt->fetch();
            if (!$art) throw new Exception("Article not found");

            $topic      = $art['topic'];
            $n_findings = $art['total_hits'];
            $n_ok       = $art['sources_ok'];

            app_log("Deep research for article #{$article_id}: {$topic}", 'deep');

            // Étape 1 : Mistral évalue les gaps et choisit les sources prioritaires
            $format_guide = build_query_format_prompt();

            $plan_raw = mistral(
                "Tu es un directeur de recherche biomédicale expert.\n\n" .
                "Nous avons fait une première recherche sur: **{$topic}**\n" .
                "Résultats: {$n_findings} données de {$n_ok} sources.\n\n" .
                "Analyse les lacunes et définis un plan de recherche APPROFONDIE.\n\n" .
                "Sources disponibles et leurs formats:\n{$format_guide}\n\n" .
                "CONSIGNES:\n" .
                "1. Identifie les 10-15 sources les PLUS pertinentes pour approfondir ce sujet spécifique\n" .
                "2. Pour les maladies génétiques → priorise UniProt, ClinVar, Ensembl, StringDB, Reactome\n" .
                "3. Pour les médicaments → priorise ChEMBL, PubChem, OpenFDA, ClinicalTrials, RxNorm\n" .
                "4. Pour les mécanismes → priorise KEGG, Reactome, GeneOntology, DisGeNET\n" .
                "5. Pour la littérature → priorise PubMed, EuropePMC, OpenAlex, CrossRef, SemanticScholar\n\n" .
                "Génère EXACTEMENT le JSON suivant:\n" .
                "{\n" .
                "  \"estimated_new_queries\": 12,\n" .
                "  \"rationale\": \"Explication en 2-3 phrases\",\n" .
                "  \"priority_sources\": {\n" .
                "    \"PubMed\": \"nouveau terme de recherche plus spécifique\",\n" .
                "    \"ClinVar\": \"terme spécifique\"\n" .
                "  }\n" .
                "}\n\n" .
                "IMPORTANT: Réponds UNIQUEMENT avec le JSON, sans texte avant ni après.",
                'Expert en recherche biomédicale et stratégie de découverte scientifique.',
                2000
            );

            $plan_raw = preg_replace('/^```json\s*/m', '', $plan_raw);
            $plan_raw = preg_replace('/^```\s*/m', '', $plan_raw);
            $plan = @json_decode(trim($plan_raw), true);

            if (!is_array($plan) || empty($plan['priority_sources'])) {
                throw new Exception('Plan de recherche invalide');
            }

            // Créer une nouvelle session pour la recherche approfondie
            $sid = 'deep_' . bin2hex(random_bytes(8));
            db()->prepare("INSERT INTO sessions (id, topic, status, mode) VALUES (?, ?, 'running', 'deep')")->execute([$sid, $topic]);

            // Construire les requêtes prioritaires
            $ins = db()->prepare("INSERT INTO queries (session_id, source, url, term, status) VALUES (?, ?, ?, ?, 'pending')");
            $prepared = [];

            foreach ($plan['priority_sources'] as $src_name => $term) {
                if (!isset(SOURCES_CONFIG[$src_name]) || empty($term)) continue;
                $cfg = SOURCES_CONFIG[$src_name];

                if ($term === 'STATIC') {
                    $url = $cfg['url'];
                    $term = 'static';
                } else {
                    $encoded = urlencode($term);
                    $url = str_replace('{TERM}', $encoded, $cfg['url']);
                }

                $ins->execute([$sid, $src_name, $url, $term]);
                $prepared[] = ['id' => (int)db()->lastInsertId(), 'source' => $src_name];
            }

            send(['success' => true, 'data' => [
                'session_id'          => $sid,
                'article_id'          => $article_id,
                'topic'               => $topic,
                'queries'             => $prepared,
                'rationale'           => $plan['rationale'] ?? '',
                'estimated_queries'   => $plan['estimated_new_queries'] ?? count($prepared),
            ]]);

        // ── FINALISER RECHERCHE APPROFONDIE ───────────────────────────
        case 'step_deep_finalize':
            $sid        = $_POST['session_id'] ?? '';
            $article_id = (int)($_POST['article_id'] ?? 0);
            $topic      = $_POST['topic'] ?? '';
            if (!$sid || !$article_id || !$topic) throw new Exception('Missing params');

            // Récupérer les nouveaux findings
            $stmt = db()->prepare(
                "SELECT source, title, abstract, year, url, source_url FROM findings WHERE session_id = ? AND title != '' ORDER BY source"
            );
            $stmt->execute([$sid]);
            $new_findings = $stmt->fetchAll();

            // Récupérer les anciens findings de l'article
            $stmt2 = db()->prepare("SELECT session_id FROM articles WHERE id = ?");
            $stmt2->execute([$article_id]);
            $old_art = $stmt2->fetch();
            $old_sid = $old_art['session_id'] ?? '';

            $stmt3 = db()->prepare(
                "SELECT source, title, abstract, year FROM findings WHERE session_id = ? AND title != '' ORDER BY source LIMIT 30"
            );
            $stmt3->execute([$old_sid]);
            $old_findings = $stmt3->fetchAll();

            $n_new  = count($new_findings);
            $n_old  = count($old_findings);

            // Construire contexte enrichi
            $old_ctx = '';
            foreach (array_slice($old_findings, 0, 15) as $f) {
                $old_ctx .= "- [{$f['source']}] {$f['title']}";
                if ($f['year']) $old_ctx .= " ({$f['year']})";
                if ($f['abstract']) $old_ctx .= ": " . substr($f['abstract'], 0, 200);
                $old_ctx .= "\n";
            }

            $new_ctx = '';
            foreach ($new_findings as $f) {
                $new_ctx .= "- [{$f['source']}] {$f['title']}";
                if ($f['year']) $new_ctx .= " ({$f['year']})";
                if ($f['abstract']) $new_ctx .= ": " . substr($f['abstract'], 0, 300);
                if ($f['source_url']) $new_ctx .= " → " . $f['source_url'];
                $new_ctx .= "\n";
            }

            $deep_analysis = mistral(
                "Tu es un expert scientifique. Suite à une RECHERCHE APPROFONDIE sur **{$topic}**, " .
                "tu dois rédiger une analyse complémentaire de 1500+ mots.\n\n" .
                "DONNÉES INITIALES ({$n_old} résultats):\n{$old_ctx}\n\n" .
                "NOUVELLES DONNÉES APPROFONDIES ({$n_new} résultats):\n{$new_ctx}\n\n" .
                "Rédige une section '## Analyse Approfondie' avec:\n" .
                "- Les nouvelles découvertes issues de la recherche spécialisée\n" .
                "- Les connexions inter-sources (ex: variant ClinVar → protéine UniProt → médicament ChEMBL)\n" .
                "- Les implications cliniques avancées\n" .
                "- Les pistes de recherche identifiées\n" .
                "- Un tableau comparatif des données par type de source\n\n" .
                "Format Markdown, minimum 1500 mots, niveau scientifique élevé.",
                'Expert en synthèse de recherche biomédicale multi-sources.',
                5000
            );

            // Mettre à jour l'article avec l'analyse approfondie
            db()->prepare("UPDATE articles SET deep_analysis = ? WHERE id = ?")->execute([$deep_analysis, $article_id]);
            db()->prepare("UPDATE sessions SET status='done' WHERE id=?")->execute([$sid]);

            send(['success' => true, 'data' => [
                'article_id'    => $article_id,
                'new_findings'  => $n_new,
                'deep_analysis' => $deep_analysis,
            ]]);

        default:
            throw new Exception("Unknown action: {$action}");
    }
} catch (Throwable $e) {
    app_log('ERROR: ' . $e->getMessage() . ' @ ' . basename($e->getFile()) . ':' . $e->getLine(), 'error');
    send(['success' => false, 'error' => $e->getMessage()]);
}
