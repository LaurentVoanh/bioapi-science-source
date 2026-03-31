<?php
declare(strict_types=1);

define('APP_VERSION', '3.0.0');
define('DB_PATH',     __DIR__ . '/genesis.sqlite');
define('LOG_PATH',    __DIR__ . '/logs/app.log');

// Mistral AI - rotation automatique des clés
define('MISTRAL_KEYS', [
    'your api key mistral 1',
    'your api key mistral 2',
    'your api key mistral 3',
]);
define('MISTRAL_API',   'https://api.mistral.ai/v1/chat/completions');
define('MISTRAL_MODEL', 'pixtral-12b-2409');

// ============================================================
// 36 SOURCES — format : url avec {query} comme placeholder
// ============================================================
define('SOURCES', [
    'PubMed'         => 'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?db=pubmed&term={query}&retmode=json&retmax=8&sort=relevance',
    'EuropePMC'      => 'https://www.ebi.ac.uk/europepmc/webservices/rest/search?query={query}&format=json&pageSize=8&sort=CITED',
    'SemanticScholar' => 'https://api.semanticscholar.org/graph/v1/paper/search?query={query}&limit=8&fields=title,year,abstract,citationCount,authors',
    'OpenAlex'       => 'https://api.openalex.org/works?search={query}&per_page=8&sort=cited_by_count:desc',
    'CrossRef'       => 'https://api.crossref.org/works?query={query}&rows=6&sort=relevance',
    'arXiv'          => 'https://export.arxiv.org/api/query?search_query=all:{query}&max_results=6&sortBy=relevance',
    'BioRxiv'        => 'https://api.biorxiv.org/details/biorxiv/2022-01-01/2025-12-31/{query}/0/json',
    'MedRxiv'        => 'https://api.biorxiv.org/details/medrxiv/2022-01-01/2025-12-31/{query}/0/json',
    'Zenodo'         => 'https://zenodo.org/api/records/?q={query}&size=6&sort=mostrecent',
    'DataCite'       => 'https://api.datacite.org/dois?query={query}&page[size]=5',
    'DOAJ'           => 'https://doaj.org/api/v3/search/articles/{query}?pageSize=5',
    'INSPIRE-HEP'    => 'https://inspirehep.net/api/literature?q={query}&size=5&sort=mostrecent',
    'UniProt'        => 'https://rest.uniprot.org/uniprotkb/search?query={query}&format=json&size=5',
    'Ensembl'        => 'https://rest.ensembl.org/xrefs/symbol/homo_sapiens/{query}?content-type=application/json',
    'ClinVar'        => 'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?db=clinvar&term={query}&retmode=json&retmax=6',
    'ChEMBL'         => 'https://www.ebi.ac.uk/chembl/api/data/molecule/search?q={query}&format=json&limit=5',
    'PubChem'        => 'https://pubchem.ncbi.nlm.nih.gov/rest/pug/compound/name/{query}/cids/JSON',
    'ChEBI'          => 'https://www.ebi.ac.uk/chebi/restService/search/getEntities?search={query}&output=json&pageSize=5',
    'KEGG'           => 'https://rest.kegg.jp/find/hsa/{query}',
    'Reactome'       => 'https://reactome.org/ContentService/content/search?q={query}&format=json&cluster=true',
    'StringDB'       => 'https://string-db.org/api/json/identifier?identifiers={query}&species=9606',
    'GBIF'           => 'https://api.gbif.org/v1/species/search?q={query}&limit=5',
    'Wikidata'       => 'https://www.wikidata.org/w/api.php?action=wbsearchentities&search={query}&language=en&format=json&limit=5',
    'Wikipedia'      => 'https://en.wikipedia.org/w/api.php?action=query&list=search&srsearch={query}&format=json&srlimit=3&srprop=snippet',
    'HuggingFace'    => 'https://huggingface.co/api/models?search={query}&limit=5&sort=downloads',
    'PapersWithCode' => 'https://paperswithcode.com/api/v1/papers/?search={query}&page_size=5',
    'ClinicalTrials' => 'https://clinicaltrials.gov/api/query/full_studies?expr={query}&min_rnk=1&max_rnk=5&fmt=json',
    'OpenFDA'        => 'https://api.fda.gov/drug/label.json?search={query}&limit=5',
    'GeneOntology'   => 'https://api.geneontology.org/api/search/entity/autocomplete/{query}?rows=5',
    'PharmGKB'       => 'https://api.pharmgkb.org/v1/data/gene?symbol={query}&view=base',
    'NASAExoplanet'  => 'https://exoplanetarchive.ipac.caltech.edu/TAP/sync?query=SELECT+pl_name,disc_year+FROM+pscompfnpars+WHERE+upper(pl_name)+LIKE+upper(%27%25{query}%25%27)+LIMIT+5&format=json',
    'IntAct'         => 'https://www.ebi.ac.uk/intact/ws/interaction/findInteractions?query={query}&firstResult=0&maxResults=5&format=json',
    'DisGeNET'       => 'https://www.disgenet.org/api/gda/disease/{query}?limit=5',
    'GEO'            => 'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?db=gds&term={query}&retmode=json&retmax=5',
    'ArrayExpress'   => 'https://www.ebi.ac.uk/biostudies/api/v1/search?query={query}&pageSize=5&type=study',
    'PDB'            => 'https://search.rcsb.org/rcsbsearch/v2/query?json={"query":{"type":"terminal","service":"full_text","parameters":{"value":"{query}"}},"return_type":"entry","request_options":{"paginate":{"start":0,"rows":5}}}',
]);

// ============================================================
// PDO — connexion unique, schéma auto-créé
// ============================================================
function db(): PDO
{
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $dir = dirname(DB_PATH);
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE,            PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA journal_mode=WAL');
    $pdo->exec('PRAGMA foreign_keys=ON');

    // Schéma inline — pas besoin de fichier .sql externe
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS sessions (
            id          TEXT PRIMARY KEY,
            topic       TEXT NOT NULL,
            status      TEXT NOT NULL DEFAULT 'running',
            created_at  TEXT NOT NULL DEFAULT (datetime('now'))
        );

        CREATE TABLE IF NOT EXISTS queries (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            session_id  TEXT NOT NULL,
            source      TEXT NOT NULL,
            url         TEXT NOT NULL,
            status      TEXT NOT NULL DEFAULT 'pending',
            http_code   INTEGER,
            duration_ms INTEGER,
            hits        INTEGER DEFAULT 0,
            FOREIGN KEY (session_id) REFERENCES sessions(id)
        );

        CREATE TABLE IF NOT EXISTS findings (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            session_id  TEXT NOT NULL,
            source      TEXT NOT NULL,
            title       TEXT,
            abstract    TEXT,
            year        TEXT,
            url         TEXT,
            FOREIGN KEY (session_id) REFERENCES sessions(id)
        );

        CREATE TABLE IF NOT EXISTS articles (
            id           INTEGER PRIMARY KEY AUTOINCREMENT,
            session_id   TEXT NOT NULL,
            topic        TEXT NOT NULL,
            title        TEXT NOT NULL,
            summary      TEXT,
            content      TEXT NOT NULL,
            sources_ok   INTEGER DEFAULT 0,
            total_hits   INTEGER DEFAULT 0,
            word_count   INTEGER DEFAULT 0,
            created_at   TEXT NOT NULL DEFAULT (datetime('now')),
            FOREIGN KEY (session_id) REFERENCES sessions(id)
        );

        CREATE INDEX IF NOT EXISTS idx_queries_sess   ON queries(session_id);
        CREATE INDEX IF NOT EXISTS idx_findings_sess  ON findings(session_id);
        CREATE INDEX IF NOT EXISTS idx_articles_date  ON articles(created_at DESC);
    ");

    return $pdo;
}

// ============================================================
// Logging
// ============================================================
function app_log(string $msg, string $step = 'info'): void
{
    $dir = dirname(LOG_PATH);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    file_put_contents(
        LOG_PATH,
        sprintf("[%s][%-12s] %s\n", date('H:i:s'), $step, $msg),
        FILE_APPEND | LOCK_EX
    );
}

// ============================================================
// Mistral — rotation de clé + retry 429
// ============================================================
function mistral(string $prompt, string $system = 'Tu es un expert en synthèse de recherche scientifique mondiale.', int $max_tokens = 4096): string
{
    $keys = MISTRAL_KEYS;
    shuffle($keys);

    foreach ($keys as $attempt => $key) {
        if ($attempt > 0) usleep(2_000_000);

        $ch = curl_init(MISTRAL_API);
        curl_setopt_array($ch, [
            CURLOPT_POST            => true,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_TIMEOUT         => 90,
            CURLOPT_HTTPHEADER      => [
                "Authorization: Bearer {$key}",
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'model'       => MISTRAL_MODEL,
                'messages'    => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user',   'content' => $prompt],
                ],
                'temperature' => 0.35,
                'max_tokens'  => $max_tokens,
            ], JSON_UNESCAPED_UNICODE),
        ]);

        $body = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code === 200) {
            $data = json_decode($body, true);
            return $data['choices'][0]['message']['content'] ?? '';
        }
        if ($code !== 429) break;
    }

    return '';
}

// ============================================================
// HTTP GET
// ============================================================
function http_get(string $url, int $timeout = 14): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 4,
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_USERAGENT      => 'GENESIS-ULTRA/3.0 (research-bot; contact@example.com)',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER     => ['Accept: application/json, application/xml, text/plain, */*'],
        CURLOPT_ENCODING       => '',
    ]);
    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'body' => (string)($body ?: '')];
}

// ============================================================
// Parser universel — extrait titres/abstracts selon la source
// ============================================================
function parse_response(string $source, string $body): array
{
    $items = [];

    // JSON générique
    $j = @json_decode($body, true);

    switch ($source) {

        case 'PubMed': case 'ClinVar': case 'GEO':
            $ids = $j['esearchresult']['idlist'] ?? [];
            foreach ($ids as $id) {
                $items[] = ['title' => "{$source}:{$id}", 'url' => "https://pubmed.ncbi.nlm.nih.gov/{$id}/"];
            }
            break;

        case 'EuropePMC':
            foreach ($j['resultList']['result'] ?? [] as $r) {
                $items[] = [
                    'title'    => $r['title']        ?? '',
                    'abstract' => substr($r['abstractText'] ?? '', 0, 600),
                    'year'     => (string)($r['pubYear'] ?? ''),
                    'url'      => $r['fullTextUrlList']['fullTextUrl'][0]['url'] ?? '',
                ];
            }
            break;

        case 'SemanticScholar':
            foreach ($j['data'] ?? [] as $r) {
                $items[] = [
                    'title'    => $r['title']    ?? '',
                    'abstract' => substr($r['abstract'] ?? '', 0, 600),
                    'year'     => (string)($r['year'] ?? ''),
                ];
            }
            break;

        case 'OpenAlex':
            foreach ($j['results'] ?? [] as $r) {
                $items[] = [
                    'title' => $r['display_name'] ?? '',
                    'year'  => (string)($r['publication_year'] ?? ''),
                ];
            }
            break;

        case 'CrossRef':
            foreach ($j['message']['items'] ?? [] as $r) {
                $items[] = [
                    'title' => implode(' ', $r['title'] ?? ['']),
                    'year'  => (string)($r['published']['date-parts'][0][0] ?? ''),
                    'url'   => $r['URL'] ?? '',
                ];
            }
            break;

        case 'arXiv':
            preg_match_all('/<entry>(.*?)<\/entry>/s', $body, $m);
            foreach ($m[1] as $entry) {
                preg_match('/<title>(.*?)<\/title>/s',     $entry, $t);
                preg_match('/<summary>(.*?)<\/summary>/s', $entry, $s);
                $items[] = [
                    'title'    => trim(preg_replace('/\s+/', ' ', $t[1] ?? '')),
                    'abstract' => substr(trim(preg_replace('/\s+/', ' ', $s[1] ?? '')), 0, 600),
                ];
            }
            break;

        case 'BioRxiv': case 'MedRxiv':
            foreach ($j['collection'] ?? [] as $r) {
                $items[] = [
                    'title'    => $r['title']    ?? '',
                    'abstract' => substr($r['abstract'] ?? '', 0, 600),
                    'year'     => substr($r['date'] ?? '', 0, 4),
                ];
            }
            break;

        case 'Zenodo':
            foreach ($j['hits']['hits'] ?? [] as $r) {
                $items[] = [
                    'title' => $r['metadata']['title'] ?? '',
                    'year'  => substr($r['metadata']['publication_date'] ?? '', 0, 4),
                ];
            }
            break;

        case 'INSPIRE-HEP':
            foreach ($j['hits']['hits'] ?? [] as $r) {
                $items[] = ['title' => $r['metadata']['titles'][0]['title'] ?? ''];
            }
            break;

        case 'UniProt':
            foreach ($j['results'] ?? [] as $r) {
                $items[] = [
                    'title' => ($r['genes'][0]['geneName']['value'] ?? '') . ' — ' . ($r['organism']['scientificName'] ?? ''),
                ];
            }
            break;

        case 'ChEMBL':
            foreach ($j['molecules'] ?? [] as $r) {
                $items[] = ['title' => $r['pref_name'] ?? $r['molecule_chembl_id'] ?? ''];
            }
            break;

        case 'PubChem':
            $cids = $j['IdentifierList']['CID'] ?? [];
            foreach (array_slice($cids, 0, 5) as $cid) {
                $items[] = ['title' => "PubChem CID:{$cid}", 'url' => "https://pubchem.ncbi.nlm.nih.gov/compound/{$cid}"];
            }
            break;

        case 'Wikidata':
            foreach ($j['search'] ?? [] as $r) {
                $items[] = ['title' => $r['label'] ?? '', 'abstract' => $r['description'] ?? ''];
            }
            break;

        case 'Wikipedia':
            foreach ($j['query']['search'] ?? [] as $r) {
                $items[] = [
                    'title'    => $r['title']   ?? '',
                    'abstract' => strip_tags($r['snippet'] ?? ''),
                ];
            }
            break;

        case 'ClinicalTrials':
            $studies = $j['FullStudiesResponse']['FullStudies'] ?? [];
            foreach ($studies as $s) {
                $mod = $s['Study']['ProtocolSection']['IdentificationModule'] ?? [];
                $items[] = ['title' => $mod['BriefTitle'] ?? ''];
            }
            break;

        case 'HuggingFace':
            foreach (array_slice(is_array($j) ? $j : [], 0, 5) as $r) {
                $items[] = ['title' => $r['modelId'] ?? ''];
            }
            break;

        case 'PapersWithCode':
            foreach ($j['results'] ?? [] as $r) {
                $items[] = ['title' => $r['title'] ?? '', 'year' => substr($r['published'] ?? '', 0, 4)];
            }
            break;

        case 'ArrayExpress':
            foreach ($j['hits']['hits'] ?? [] as $r) {
                $items[] = ['title' => $r['_source']['title'] ?? ''];
            }
            break;

        default:
            // Fallback générique JSON
            if ($j && is_array($j)) {
                $flat = array_values($j);
                if (isset($flat[0]) && is_array($flat[0])) {
                    foreach (array_slice($flat, 0, 5) as $r) {
                        $title = $r['title'] ?? $r['name'] ?? $r['label'] ?? '';
                        if ($title) $items[] = ['title' => (string)$title];
                    }
                } elseif (isset($flat[0]) && is_string($flat[0])) {
                    $items[] = ['title' => "{$source}: " . count($flat) . " résultats"];
                }
            } elseif (strlen($body) > 10) {
                // Texte brut (KEGG, etc.)
                $lines = array_filter(explode("\n", trim($body)));
                foreach (array_slice($lines, 0, 5) as $line) {
                    $items[] = ['title' => substr(trim($line), 0, 200)];
                }
            }
    }

    return array_filter($items, fn($i) => !empty($i['title']));
}
