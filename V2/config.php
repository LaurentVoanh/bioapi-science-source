<?php
declare(strict_types=1);

define('APP_VERSION', '4.0.0');
define('DB_PATH',     __DIR__ . '/genesis.sqlite');
define('LOG_PATH',    __DIR__ . '/logs/app.log');

// Mistral AI — rotation automatique des clés
define('MISTRAL_KEYS', [
    'ENTER YOUR API KEY MISTRAL',
    'ENTER YOUR API KEY MISTRAL',
    'ENTER YOUR API KEY MISTRAL',
]);
define('MISTRAL_API',   'https://api.mistral.ai/v1/chat/completions');
define('MISTRAL_MODEL', 'pixtral-12b-2409');

// ============================================================
// 36 SOURCES — URLs validées + explications pour l'IA
// Chaque source a : url_template, description, query_note
// ============================================================
define('SOURCES_CONFIG', [

    // ─── LITTÉRATURE BIOMÉDICALE ────────────────────────────
    'PubMed' => [
        'url'   => 'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?db=pubmed&retmode=json&retmax=8&sort=relevance&term={TERM}',
        'desc'  => 'Base de données biomédicale principale NCBI',
        'query' => 'Terme de recherche PubMed. Ex: "myocarditis mRNA vaccine" ou "BRCA1 cancer 2023". Accepte des termes simples en anglais. Variable: {TERM}',
        'type'  => 'literature',
    ],
    'EuropePMC' => [
        'url'   => 'https://www.ebi.ac.uk/europepmc/webservices/rest/search?format=json&pageSize=8&sort=CITED&query={TERM}',
        'desc'  => 'Europe PubMed Central — texte intégral annoté',
        'query' => 'Recherche libre en anglais. Ex: "COVID long term neurological". Variable: {TERM}',
        'type'  => 'literature',
    ],
    'OpenAlex' => [
        'url'   => 'https://api.openalex.org/works?per_page=8&sort=cited_by_count:desc&search={TERM}',
        'desc'  => '250M+ publications avec graphe de citations',
        'query' => 'Terme de recherche en anglais. Ex: "alzheimer biomarkers". Variable: {TERM}',
        'type'  => 'literature',
    ],
    'CrossRef' => [
        'url'   => 'https://api.crossref.org/works?rows=6&sort=relevance&query={TERM}',
        'desc'  => 'Métadonnées DOI — CrossRef',
        'query' => 'Recherche libre. Ex: "insulin resistance type 2 diabetes". Variable: {TERM}',
        'type'  => 'literature',
    ],
    'arXiv' => [
        'url'   => 'https://export.arxiv.org/api/query?max_results=6&sortBy=relevance&search_query=all:{TERM}',
        'desc'  => 'Préprints scientifiques (biologie, physique, IA)',
        'query' => 'Termes en anglais sans guillemets. Ex: CRISPR+genome+editing. Variable: {TERM} (utiliser + entre les mots)',
        'type'  => 'preprint',
    ],
    'Zenodo' => [
        'url'   => 'https://zenodo.org/api/records?size=6&sort=mostrecent&q={TERM}',
        'desc'  => 'Dépôt de datasets, codes et publications',
        'query' => 'Recherche libre. Ex: "genomics dataset RNA-seq". Variable: {TERM}',
        'type'  => 'data',
    ],
    'INSPIRE-HEP' => [
        'url'   => 'https://inspirehep.net/api/literature?size=5&sort=mostrecent&q={TERM}',
        'desc'  => 'Physique des hautes énergies et biophysique',
        'query' => 'Terme en anglais. Ex: "biophysics protein structure". Variable: {TERM}',
        'type'  => 'literature',
    ],
    'DataCite' => [
        'url'   => 'https://api.datacite.org/works?page[size]=5&query={TERM}',
        'desc'  => 'Datasets avec DOI — DataCite',
        'query' => 'Recherche de datasets scientifiques. Ex: "genomics cancer dataset". Variable: {TERM}',
        'type'  => 'data',
    ],

    // ─── GÉNÉTIQUE & PROTÉINES ──────────────────────────────
    'UniProt' => [
        'url'   => 'https://rest.uniprot.org/uniprotkb/search?format=json&size=5&query={TERM}+AND+reviewed:true',
        'desc'  => 'Base de données universelle des protéines',
        'query' => 'Nom de gène ou protéine. Ex: TP53, BRCA1, insulin. Variable: {TERM} — utiliser le NOM du gène/protéine lié au sujet',
        'type'  => 'protein',
    ],
    'Ensembl' => [
        'url'   => 'https://rest.ensembl.org/lookup/symbol/homo_sapiens/{TERM}?content-type=application/json',
        'desc'  => 'Génomique — coordonnées chromosomiques',
        'query' => 'NOM exact du gène humain (symbole officiel HGNC). Ex: BRCA1, TP53, ACE2. Variable: {TERM} — UN SEUL gène pertinent pour le sujet',
        'type'  => 'genomics',
    ],
    'ClinVar' => [
        'url'   => 'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?db=clinvar&retmode=json&retmax=6&term={TERM}',
        'desc'  => 'Variants génétiques cliniques NCBI',
        'query' => 'Terme de recherche clinvar. Ex: "BRCA1 pathogenic" ou "myocarditis". Variable: {TERM}',
        'type'  => 'genomics',
    ],
    'GEO' => [
        'url'   => 'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?db=gds&retmode=json&retmax=5&term={TERM}',
        'desc'  => 'Gene Expression Omnibus — données d\'expression',
        'query' => 'Terme de recherche GEO. Ex: "myocarditis RNA-seq". Variable: {TERM}',
        'type'  => 'genomics',
    ],
    'ArrayExpress' => [
        'url'   => 'https://www.ebi.ac.uk/biostudies/api/v1/search?pageSize=5&type=study&query={TERM}',
        'desc'  => 'Données d\'expression génique EBI',
        'query' => 'Recherche libre. Ex: "heart inflammation transcriptomics". Variable: {TERM}',
        'type'  => 'genomics',
    ],

    // ─── CHIMIE & PHARMACOLOGIE ─────────────────────────────
    'ChEMBL' => [
        'url'   => 'https://www.ebi.ac.uk/chembl/api/data/molecule.json?pref_name__icontains={TERM}&limit=5',
        'desc'  => 'Base de données pharmacologique ChEMBL',
        'query' => 'NOM d\'une molécule ou médicament lié au sujet. Ex: ibuprofen, aspirin, remdesivir. Variable: {TERM} — NOM en anglais simple',
        'type'  => 'chemistry',
    ],
    'PubChem' => [
        'url'   => 'https://pubchem.ncbi.nlm.nih.gov/rest/pug/compound/name/{TERM}/cids/JSON',
        'desc'  => 'Propriétés chimiques PubChem',
        'query' => 'NOM d\'un composé chimique ou médicament. Ex: aspirin, glucose, troponin. Variable: {TERM}',
        'type'  => 'chemistry',
    ],
    'KEGG' => [
        'url'   => 'https://rest.kegg.jp/find/hsa/{TERM}',
        'desc'  => 'Voies métaboliques humaines KEGG',
        'query' => 'Terme de recherche KEGG (gène ou maladie humaine). Ex: myocarditis, cardiac, insulin. Variable: {TERM}',
        'type'  => 'pathway',
    ],

    // ─── INTERACTIONS & VOIES ───────────────────────────────
    'StringDB' => [
        'url'   => 'https://string-db.org/api/json/network?species=9606&required_score=700&identifiers={TERM}',
        'desc'  => 'Réseau d\'interactions protéines STRING',
        'query' => 'NOM d\'une PROTÉINE (symbole de gène humain). Ex: TP53, MYH7, TNNT2. Variable: {TERM} — UN SEUL symbole de gène',
        'type'  => 'network',
    ],
    'Reactome' => [
        'url'   => 'https://reactome.org/ContentService/data/pathways/low/entity/{TERM}/allForms?species=9606',
        'desc'  => 'Voies de signalisation Reactome',
        'query' => 'ACCESSION UniProt d\'une protéine humaine. Ex: P04637 (TP53), P38398 (BRCA1), P00533 (EGFR). Variable: {TERM} — utiliser l\'accession UniProt du gène principal du sujet',
        'type'  => 'pathway',
    ],
    'GeneOntology' => [
        'url'   => 'https://api.geneontology.org/api/search/entity/autocomplete/{TERM}?rows=5',
        'desc'  => 'Ontologie des fonctions géniques GO',
        'query' => 'Terme biologique en anglais. Ex: cardiac muscle contraction, inflammation. Variable: {TERM} (remplacer espaces par %20)',
        'type'  => 'ontology',
    ],
    'DisGeNET' => [
        'url'   => 'https://www.disgenet.org/api/gda/disease/{TERM}?limit=5',
        'desc'  => 'Associations gènes-maladies DisGeNET',
        'query' => 'NOM de la maladie (en anglais, traits d\'union). Ex: myocarditis, cardiomyopathy, heart-failure. Variable: {TERM}',
        'type'  => 'disease',
    ],

    // ─── ESSAIS CLINIQUES & MÉDICAMENTS ─────────────────────
    'ClinicalTrials' => [
        'url'   => 'https://clinicaltrials.gov/api/v2/studies?pageSize=5&fields=NCTId,BriefTitle,Phase,OverallStatus&query.cond={TERM}',
        'desc'  => 'Essais cliniques ClinicalTrials.gov',
        'query' => 'Condition médicale en anglais. Ex: myocarditis, COVID-19, type 2 diabetes. Variable: {TERM}',
        'type'  => 'clinical',
    ],
    'OpenFDA' => [
        'url'   => 'https://api.fda.gov/drug/label.json?limit=5&search=indications_and_usage:{TERM}',
        'desc'  => 'Données FDA médicaments et pharmacovigilance',
        'query' => 'Indication médicale ou médicament en anglais. Ex: myocarditis, cardiac arrest. Variable: {TERM}',
        'type'  => 'clinical',
    ],

    // ─── ENCYCLOPÉDIES & ONTOLOGIES ─────────────────────────
    'Wikidata' => [
        'url'   => 'https://www.wikidata.org/w/api.php?action=wbsearchentities&language=en&format=json&limit=5&search={TERM}',
        'desc'  => 'Graphe de connaissances Wikidata',
        'query' => 'Terme de recherche en anglais. Ex: myocarditis, mRNA vaccine. Variable: {TERM}',
        'type'  => 'encyclopedia',
    ],
    'Wikipedia' => [
        'url'   => 'https://en.wikipedia.org/w/api.php?action=query&list=search&format=json&srlimit=3&srprop=snippet&srsearch={TERM}',
        'desc'  => 'Encyclopédie Wikipedia anglophone',
        'query' => 'Terme de recherche en anglais. Ex: myocarditis vaccine. Variable: {TERM}',
        'type'  => 'encyclopedia',
    ],

    // ─── IA & MODÈLES ───────────────────────────────────────
    'HuggingFace' => [
        'url'   => 'https://huggingface.co/api/models?limit=5&sort=downloads&search={TERM}',
        'desc'  => 'Modèles IA biologiques et biomédicaux',
        'query' => 'Terme lié à l\'IA ou la bioinformatique. Ex: protein structure, biomedical NLP. Variable: {TERM}',
        'type'  => 'ai',
    ],
    'PapersWithCode' => [
        'url'   => 'https://paperswithcode.com/api/v1/papers/?page_size=5&search={TERM}',
        'desc'  => 'Articles ML avec implémentation',
        'query' => 'Terme de méthode ou domaine. Ex: protein folding, medical imaging. Variable: {TERM}',
        'type'  => 'ai',
    ],

    // ─── PHYSIQUE & STRUCTURES ──────────────────────────────
    'PDB' => [
        'url'   => 'https://search.rcsb.org/rcsbsearch/v2/query?json={"query":{"type":"terminal","service":"full_text","parameters":{"value":"{TERM}"}},"return_type":"entry","request_options":{"paginate":{"start":0,"rows":5}}}',
        'desc'  => 'Structures 3D protéines RCSB PDB',
        'query' => 'Nom de protéine ou molécule en anglais. Ex: "cardiac troponin" ou "spike protein". Variable: {TERM}',
        'type'  => 'structure',
    ],
    'Unpaywall' => [
        'url'   => 'https://api.unpaywall.org/v2/{TERM}?email=research@genesis.bio',
        'desc'  => 'Accès libre aux publications (OA)',
        'query' => 'DOI d\'un article. Ex: 10.1038/nature12373. Variable: {TERM} — UTILISER UNIQUEMENT si un DOI spécifique est pertinent, sinon laisser vide',
        'type'  => 'access',
    ],

    // ─── BIODIVERSITÉ & SANTÉ MONDIALE ─────────────────────
    'GBIF' => [
        'url'   => 'https://api.gbif.org/v1/species/search?limit=5&q={TERM}',
        'desc'  => 'Biodiversité mondiale (vecteurs de maladies)',
        'query' => 'Nom d\'espèce ou de pathogène. Ex: "SARS-CoV-2" ou "Aedes aegypti". Variable: {TERM}',
        'type'  => 'ecology',
    ],
    'WHOGHO' => [
        'url'   => 'https://ghoapi.azureedge.net/api/Indicator?$top=5&$filter=contains(IndicatorName,\'{TERM}\')',
        'desc'  => 'Indicateurs santé mondiale OMS',
        'query' => 'Terme de santé publique en anglais. Ex: cardiac, mortality, vaccine. Variable: {TERM}',
        'type'  => 'public_health',
    ],

    // ─── PHARMACOGÉNOMIQUE ──────────────────────────────────
    'RxNorm' => [
        'url'   => 'https://rxnav.nlm.nih.gov/REST/rxcui.json?name={TERM}',
        'desc'  => 'Identifiants médicaments FDA RxNorm',
        'query' => 'NOM d\'un médicament approuvé en anglais. Ex: ibuprofen, metformin. Variable: {TERM}',
        'type'  => 'drug',
    ],

    // ─── BIOINFORMATIQUE ────────────────────────────────────
    'BioGRID' => [
        'url'   => 'https://downloads.thebiogrid.org/BioGRID/CHANGELOG.txt',
        'desc'  => 'Interactions protéines-protéines BioGRID (index)',
        'query' => 'Source statique — pas de terme variable',
        'type'  => 'network',
    ],
    'NCBI_Gene' => [
        'url'   => 'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?db=gene&retmode=json&retmax=5&term={TERM}[gene]+AND+9606[taxid]',
        'desc'  => 'Gènes humains NCBI Gene',
        'query' => 'NOM du gène humain officiel. Ex: MYH7, TNNT2, SCN5A. Variable: {TERM}',
        'type'  => 'genomics',
    ],
    'NCBI_Protein' => [
        'url'   => 'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?db=protein&retmode=json&retmax=5&term={TERM}[titl]+AND+human[organism]',
        'desc'  => 'Protéines humaines NCBI',
        'query' => 'Nom de la protéine. Ex: troponin, myosin heavy chain. Variable: {TERM}',
        'type'  => 'protein',
    ],

    // ─── DONNÉES MONDIALES ──────────────────────────────────
    'WorldBank' => [
        'url'   => 'https://api.worldbank.org/v2/country/all/indicator/{TERM}?format=json&mrv=3&per_page=5',
        'desc'  => 'Indicateurs santé Banque mondiale',
        'query' => 'CODE indicateur Banque Mondiale. Ex: SH.XPD.CHEX.GD.ZS (health expenditure), SH.DYN.MORT (mortality). Variable: {TERM}',
        'type'  => 'public_health',
    ],

    // ─── PHYSIQUE ───────────────────────────────────────────
    'NASA_ADS' => [
        'url'   => 'https://ui.adsabs.harvard.edu/api/search/query?rows=3&fl=title,author,year&q={TERM}',
        'desc'  => 'NASA ADS — astrophysique et biophysique',
        'query' => 'Terme scientifique. Ex: "biophysics" ou "medical imaging". Variable: {TERM}',
        'type'  => 'physics',
    ],
    'SemanticScholar' => [
        'url'   => 'https://api.semanticscholar.org/graph/v1/paper/search?limit=5&fields=title,year,abstract,citationCount&query={TERM}',
        'desc'  => 'Semantic Scholar — graphe de citations IA',
        'query' => 'Terme de recherche en anglais. Ex: "myocarditis biomarkers". Variable: {TERM}',
        'type'  => 'literature',
    ],
]);

// Alias pour compatibilité (SOURCES = tableau simple pour les URLs)
define('SOURCES', array_map(fn($v) => $v['url'], SOURCES_CONFIG));

// ============================================================
// PDO — SQLite
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

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS sessions (
            id          TEXT PRIMARY KEY,
            topic       TEXT NOT NULL,
            status      TEXT NOT NULL DEFAULT 'running',
            mode        TEXT NOT NULL DEFAULT 'auto',
            created_at  TEXT NOT NULL DEFAULT (datetime('now'))
        );

        CREATE TABLE IF NOT EXISTS queries (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            session_id  TEXT NOT NULL,
            source      TEXT NOT NULL,
            url         TEXT NOT NULL,
            term        TEXT,
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
            source_url  TEXT,
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
            deep_analysis TEXT,
            created_at   TEXT NOT NULL DEFAULT (datetime('now')),
            FOREIGN KEY (session_id) REFERENCES sessions(id)
        );
    ");

    return $pdo;
}

// ============================================================
// Logging
// ============================================================
function app_log(string $msg, string $step = 'info'): void
{
    $line = '[' . date('Y-m-d H:i:s') . '][' . strtoupper($step) . '] ' . $msg . PHP_EOL;
    $dir  = dirname(LOG_PATH);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    file_put_contents(LOG_PATH, $line, FILE_APPEND | LOCK_EX);
}

// ============================================================
// Mistral AI — file_get_contents (Hostinger compatible)
// ============================================================
function mistral(string $prompt, string $system = 'Tu es un expert en synthèse de recherche scientifique mondiale.', int $max_tokens = 4096): string
{
    static $key_index = 0;
    $keys = MISTRAL_KEYS;
    $attempts = count($keys);

    for ($i = 0; $i < $attempts; $i++) {
        $key = $keys[$key_index % count($keys)];
        $key_index++;

        $payload = json_encode([
            'model'       => MISTRAL_MODEL,
            'max_tokens'  => $max_tokens,
            'temperature' => 0.7,
            'messages'    => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user',   'content' => $prompt],
            ],
        ], JSON_UNESCAPED_UNICODE);

        $ctx = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => implode("\r\n", [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $key,
                    'Accept: application/json',
                    'User-Agent: GENESIS-ULTRA/4.0',
                ]),
                'content' => $payload,
                'timeout' => 90,
                'ignore_errors' => true,
            ],
            'ssl' => ['verify_peer' => false],
        ]);

        $raw = @file_get_contents(MISTRAL_API, false, $ctx);
        if ($raw === false) continue;

        $d = @json_decode($raw, true);
        $content = $d['choices'][0]['message']['content'] ?? null;
        if ($content && strlen($content) > 2) {
            return trim($content);
        }
    }

    app_log('Mistral: all keys failed', 'mistral_error');
    return '';
}

// ============================================================
// HTTP GET — file_get_contents (Hostinger compatible)
// ============================================================
function http_get(string $url, int $timeout = 15): array
{
    $ctx = stream_context_create([
        'http' => [
            'method'         => 'GET',
            'header'         => implode("\r\n", [
                'Accept: application/json, application/xml, text/plain, */*',
                'User-Agent: GENESIS-ULTRA/4.0 (research; contact@genesis.bio)',
            ]),
            'timeout'        => $timeout,
            'follow_location' => 1,
            'max_redirects'  => 4,
            'ignore_errors'  => true,
        ],
        'ssl' => ['verify_peer' => false],
    ]);

    $body = @file_get_contents($url, false, $ctx);
    $code = 0;

    // Extraire le code HTTP depuis les headers
    if (isset($http_response_header) && is_array($http_response_header)) {
        foreach ($http_response_header as $h) {
            if (preg_match('#^HTTP/\d+\.?\d*\s+(\d+)#', $h, $m)) {
                $code = (int)$m[1];
            }
        }
    }

    if ($body === false) {
        return ['code' => 0, 'body' => ''];
    }

    return ['code' => $code ?: 200, 'body' => (string)$body];
}

// ============================================================
// Prompt expliquant à l'IA comment formuler les requêtes
// ============================================================
function build_query_format_prompt(): string
{
    $lines = [];
    foreach (SOURCES_CONFIG as $name => $cfg) {
        $lines[] = "- **{$name}** ({$cfg['type']}): {$cfg['query']}";
    }
    return implode("\n", $lines);
}

// ============================================================
// Parser universel — extrait titres/abstracts/URLs selon source
// ============================================================
function parse_response(string $source, string $body, string $url = ''): array
{
    $items = [];
    $j = @json_decode($body, true);

    switch ($source) {

        case 'PubMed':
        case 'ClinVar':
        case 'GEO':
        case 'NCBI_Gene':
        case 'NCBI_Protein':
            $ids = $j['esearchresult']['idlist'] ?? [];
            $db  = match($source) {
                'PubMed'       => 'pubmed',
                'ClinVar'      => 'clinvar',
                'GEO'          => 'gds',
                'NCBI_Gene'    => 'gene',
                'NCBI_Protein' => 'protein',
                default        => 'pubmed',
            };
            foreach ($ids as $id) {
                $link = match($source) {
                    'PubMed'    => "https://pubmed.ncbi.nlm.nih.gov/{$id}/",
                    'ClinVar'   => "https://www.ncbi.nlm.nih.gov/clinvar/variation/{$id}/",
                    'NCBI_Gene' => "https://www.ncbi.nlm.nih.gov/gene/{$id}",
                    default     => "https://www.ncbi.nlm.nih.gov/{$db}/{$id}",
                };
                $items[] = ['title' => "{$source} ID:{$id}", 'url' => $link, 'source_url' => $link];
            }
            break;

        case 'EuropePMC':
            foreach ($j['resultList']['result'] ?? [] as $r) {
                $link = $r['fullTextUrlList']['fullTextUrl'][0]['url']
                     ?? "https://europepmc.org/article/{$r['source']}/{$r['id']}";
                $items[] = [
                    'title'      => $r['title']     ?? '',
                    'abstract'   => substr($r['abstractText'] ?? '', 0, 600),
                    'year'       => (string)($r['pubYear'] ?? ''),
                    'url'        => $link,
                    'source_url' => $link,
                ];
            }
            break;

        case 'SemanticScholar':
            foreach ($j['data'] ?? [] as $r) {
                $link = "https://api.semanticscholar.org/graph/v1/paper/{$r['paperId']}";
                $items[] = [
                    'title'      => $r['title']    ?? '',
                    'abstract'   => substr($r['abstract'] ?? '', 0, 600),
                    'year'       => (string)($r['year'] ?? ''),
                    'source_url' => $link,
                ];
            }
            break;

        case 'OpenAlex':
            foreach ($j['results'] ?? [] as $r) {
                $link = $r['doi'] ? "https://doi.org/{$r['doi']}" : ($r['id'] ?? '');
                $items[] = [
                    'title'      => $r['display_name'] ?? '',
                    'year'       => (string)($r['publication_year'] ?? ''),
                    'url'        => $link,
                    'source_url' => $r['id'] ?? '',
                ];
            }
            break;

        case 'CrossRef':
            foreach ($j['message']['items'] ?? [] as $r) {
                $doi  = $r['DOI'] ?? '';
                $link = $doi ? "https://doi.org/{$doi}" : ($r['URL'] ?? '');
                $items[] = [
                    'title'      => implode(' ', $r['title'] ?? ['']),
                    'year'       => (string)($r['published']['date-parts'][0][0] ?? ''),
                    'url'        => $link,
                    'source_url' => $link,
                ];
            }
            break;

        case 'arXiv':
            preg_match_all('/<entry>(.*?)<\/entry>/s', $body, $m);
            foreach ($m[1] as $entry) {
                preg_match('/<title>(.*?)<\/title>/s',     $entry, $t);
                preg_match('/<summary>(.*?)<\/summary>/s', $entry, $s);
                preg_match('/<id>(.*?)<\/id>/s',           $entry, $id);
                $link = trim($id[1] ?? '');
                $items[] = [
                    'title'      => trim(preg_replace('/\s+/', ' ', $t[1] ?? '')),
                    'abstract'   => substr(trim(preg_replace('/\s+/', ' ', $s[1] ?? '')), 0, 600),
                    'url'        => $link,
                    'source_url' => $link,
                ];
            }
            break;

        case 'Zenodo':
            foreach ($j['hits']['hits'] ?? [] as $r) {
                $link = $r['links']['html'] ?? "https://zenodo.org/record/{$r['id']}";
                $items[] = [
                    'title'      => $r['metadata']['title'] ?? '',
                    'year'       => substr($r['metadata']['publication_date'] ?? '', 0, 4),
                    'url'        => $link,
                    'source_url' => $link,
                ];
            }
            break;

        case 'INSPIRE-HEP':
            foreach ($j['hits']['hits'] ?? [] as $r) {
                $arxiv = $r['metadata']['arxiv_eprints'][0]['value'] ?? '';
                $link  = $arxiv ? "https://arxiv.org/abs/{$arxiv}" : '';
                $items[] = [
                    'title'      => $r['metadata']['titles'][0]['title'] ?? '',
                    'source_url' => $link,
                ];
            }
            break;

        case 'DataCite':
            foreach ($j['data'] ?? [] as $r) {
                $doi  = $r['attributes']['doi'] ?? '';
                $link = $doi ? "https://doi.org/{$doi}" : '';
                $items[] = [
                    'title'      => $r['attributes']['titles'][0]['title'] ?? '',
                    'url'        => $link,
                    'source_url' => $link,
                ];
            }
            break;

        case 'UniProt':
            foreach ($j['results'] ?? [] as $r) {
                $acc  = $r['primaryAccession'] ?? '';
                $link = $acc ? "https://www.uniprot.org/uniprotkb/{$acc}" : '';
                $items[] = [
                    'title'      => ($r['genes'][0]['geneName']['value'] ?? '') . ' — ' . ($r['organism']['scientificName'] ?? ''),
                    'abstract'   => $r['proteinDescription']['recommendedName']['fullName']['value'] ?? '',
                    'source_url' => $link,
                ];
            }
            break;

        case 'Ensembl':
            if (!empty($j['id'])) {
                $link = "https://www.ensembl.org/Homo_sapiens/Gene/Summary?g={$j['id']}";
                $items[] = [
                    'title'      => ($j['display_name'] ?? $j['id']) . ' — Chr' . ($j['seq_region_name'] ?? '?'),
                    'abstract'   => "Biotype: {$j['biotype']}, Strand: {$j['strand']}, Assembly: {$j['assembly_name']}",
                    'source_url' => $link,
                ];
            }
            break;

        case 'ChEMBL':
            foreach ($j['molecules'] ?? [] as $r) {
                $cid  = $r['molecule_chembl_id'] ?? '';
                $link = $cid ? "https://www.ebi.ac.uk/chembl/compound_report_card/{$cid}/" : '';
                $items[] = [
                    'title'      => $r['pref_name'] ?? $cid,
                    'source_url' => $link,
                ];
            }
            break;

        case 'PubChem':
            $cids = $j['IdentifierList']['CID'] ?? [];
            foreach (array_slice($cids, 0, 5) as $cid) {
                $link = "https://pubchem.ncbi.nlm.nih.gov/compound/{$cid}";
                $items[] = ['title' => "PubChem CID:{$cid}", 'url' => $link, 'source_url' => $link];
            }
            break;

        case 'KEGG':
            $lines = array_filter(explode("\n", trim($body)));
            foreach (array_slice($lines, 0, 5) as $line) {
                $parts = explode("\t", $line, 2);
                $kid   = trim($parts[0] ?? '');
                $desc  = trim($parts[1] ?? $kid);
                $link  = $kid ? "https://www.genome.jp/entry/{$kid}" : '';
                $items[] = ['title' => $desc, 'source_url' => $link];
            }
            break;

        case 'StringDB':
            if (is_array($j)) {
                $partners = array_unique(array_map(fn($i) => $i['preferredName_B'] ?? '', array_slice($j, 0, 5)));
                foreach (array_filter($partners) as $p) {
                    $link = "https://string-db.org/network/9606/{$p}";
                    $items[] = ['title' => "Interaction partner: {$p}", 'source_url' => $link];
                }
            }
            break;

        case 'Reactome':
            if (is_array($j)) {
                foreach (array_slice($j, 0, 5) as $pw) {
                    $stid = $pw['stId'] ?? '';
                    $link = $stid ? "https://reactome.org/content/detail/{$stid}" : '';
                    $items[] = [
                        'title'      => $pw['displayName'] ?? '',
                        'source_url' => $link,
                    ];
                }
            }
            break;

        case 'GeneOntology':
            foreach ($j['docs'] ?? [] as $r) {
                $goid = $r['id'] ?? '';
                $link = $goid ? "https://amigo.geneontology.org/amigo/term/{$goid}" : '';
                $items[] = [
                    'title'      => $r['label'] ?? '',
                    'abstract'   => $r['description'][0] ?? '',
                    'source_url' => $link,
                ];
            }
            break;

        case 'DisGeNET':
            foreach ($j ?? [] as $r) {
                $gene = $r['geneName'] ?? '';
                $link = $gene ? "https://www.disgenet.org/gene/{$r['geneId']}" : '';
                $items[] = [
                    'title'      => $gene . ' — score: ' . ($r['score'] ?? '?'),
                    'source_url' => $link,
                ];
            }
            break;

        case 'ClinicalTrials':
            $studies = $j['studies'] ?? [];
            foreach ($studies as $s) {
                $mod  = $s['protocolSection']['identificationModule'] ?? [];
                $nct  = $mod['nctId'] ?? '';
                $link = $nct ? "https://clinicaltrials.gov/study/{$nct}" : '';
                $items[] = [
                    'title'      => $mod['briefTitle'] ?? '',
                    'source_url' => $link,
                ];
            }
            break;

        case 'OpenFDA':
            foreach ($j['results'] ?? [] as $r) {
                $link = "https://api.fda.gov/drug/label.json";
                $items[] = [
                    'title'      => implode(', ', array_slice($r['openfda']['brand_name'] ?? ['Médicament FDA'], 0, 2)),
                    'abstract'   => substr(implode(' ', array_slice($r['indications_and_usage'] ?? [], 0, 1)), 0, 400),
                    'source_url' => $link,
                ];
            }
            break;

        case 'Wikidata':
            foreach ($j['search'] ?? [] as $r) {
                $qid  = $r['id'] ?? '';
                $link = $qid ? "https://www.wikidata.org/wiki/{$qid}" : '';
                $items[] = [
                    'title'      => $r['label'] ?? '',
                    'abstract'   => $r['description'] ?? '',
                    'source_url' => $link,
                ];
            }
            break;

        case 'Wikipedia':
            foreach ($j['query']['search'] ?? [] as $r) {
                $title = $r['title'] ?? '';
                $link  = $title ? "https://en.wikipedia.org/wiki/" . urlencode(str_replace(' ', '_', $title)) : '';
                $items[] = [
                    'title'      => $title,
                    'abstract'   => strip_tags($r['snippet'] ?? ''),
                    'url'        => $link,
                    'source_url' => $link,
                ];
            }
            break;

        case 'HuggingFace':
            foreach (array_slice(is_array($j) ? $j : [], 0, 5) as $r) {
                $mid  = $r['modelId'] ?? $r['id'] ?? '';
                $link = $mid ? "https://huggingface.co/{$mid}" : '';
                $items[] = ['title' => $mid, 'source_url' => $link];
            }
            break;

        case 'PapersWithCode':
            foreach ($j['results'] ?? [] as $r) {
                $link = $r['url_pdf'] ?? $r['url_abs'] ?? '';
                $items[] = [
                    'title'      => $r['title'] ?? '',
                    'year'       => substr($r['published'] ?? '', 0, 4),
                    'source_url' => $link,
                ];
            }
            break;

        case 'PDB':
            $ids = array_map(fn($r) => $r['identifier'] ?? '', $j['result_set'] ?? []);
            foreach (array_filter($ids) as $pid) {
                $link = "https://www.rcsb.org/structure/{$pid}";
                $items[] = ['title' => "PDB Structure: {$pid}", 'source_url' => $link];
            }
            break;

        case 'Unpaywall':
            if (!empty($j['title'])) {
                $link = $j['best_oa_location']['url'] ?? $j['doi_url'] ?? '';
                $items[] = [
                    'title'      => $j['title'] ?? '',
                    'abstract'   => ($j['is_oa'] ? '✅ Open Access' : '🔒 Paywall') . ' — ' . ($j['journal_name'] ?? ''),
                    'source_url' => $link,
                ];
            }
            break;

        case 'GBIF':
            foreach ($j['results'] ?? [] as $r) {
                $key  = $r['usageKey'] ?? '';
                $link = $key ? "https://www.gbif.org/species/{$key}" : '';
                $items[] = ['title' => $r['scientificName'] ?? '', 'source_url' => $link];
            }
            break;

        case 'WHOGHO':
            foreach ($j['value'] ?? [] as $r) {
                $link = "https://www.who.int/data/gho";
                $items[] = [
                    'title'      => $r['IndicatorName'] ?? '',
                    'source_url' => $link,
                ];
            }
            break;

        case 'RxNorm':
            $ids = $j['idGroup']['rxnormId'] ?? [];
            foreach (array_slice($ids, 0, 3) as $rid) {
                $link = "https://mor.nlm.nih.gov/RxNav/search?searchBy=RXCUI&searchTerm={$rid}";
                $items[] = ['title' => "RxNorm CUI: {$rid}", 'source_url' => $link];
            }
            break;

        case 'BioGRID':
            // Source statique — parse changelog
            $lines = array_filter(array_slice(explode("\n", trim($body)), 0, 3));
            foreach ($lines as $line) {
                $items[] = ['title' => trim($line), 'source_url' => 'https://thebiogrid.org'];
            }
            break;

        case 'ArrayExpress':
            foreach ($j['hits']['hits'] ?? [] as $r) {
                $acc  = $r['_source']['accession'] ?? '';
                $link = $acc ? "https://www.ebi.ac.uk/biostudies/studies/{$acc}" : '';
                $items[] = ['title' => $r['_source']['title'] ?? $acc, 'source_url' => $link];
            }
            break;

        case 'WorldBank':
            foreach (array_filter($j[1] ?? [], fn($v) => $v['value'] !== null) as $r) {
                $items[] = [
                    'title' => ($r['indicator']['value'] ?? '') . ' — ' . ($r['country']['value'] ?? '') . ' (' . ($r['date'] ?? '') . '): ' . round((float)($r['value'] ?? 0), 2),
                    'source_url' => 'https://data.worldbank.org',
                ];
            }
            break;

        case 'NASA_ADS':
            foreach ($j['response']['docs'] ?? [] as $r) {
                $items[] = ['title' => $r['title'][0] ?? '', 'source_url' => 'https://ui.adsabs.harvard.edu'];
            }
            break;

        default:
            // Fallback générique
            if ($j && is_array($j)) {
                $flat = array_values($j);
                if (isset($flat[0]) && is_array($flat[0])) {
                    foreach (array_slice($flat, 0, 5) as $r) {
                        $title = $r['title'] ?? $r['name'] ?? $r['label'] ?? '';
                        if ($title) $items[] = ['title' => (string)$title, 'source_url' => $url];
                    }
                }
            } elseif (strlen($body) > 10) {
                $lines = array_filter(explode("\n", trim($body)));
                foreach (array_slice($lines, 0, 3) as $line) {
                    $items[] = ['title' => substr(trim($line), 0, 200), 'source_url' => $url];
                }
            }
    }

    return array_filter($items, fn($i) => !empty($i['title']));
}
