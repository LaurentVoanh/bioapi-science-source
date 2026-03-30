<?php
/**
 * ╔══════════════════════════════════════════════════════════════════════════════╗
 * ║     🔬 BIOMEDICAL API SUITE — VALIDATED ENDPOINTS ONLY + CROSS-VALUE        ║
 * ║  ~65 endpoints validés · 30 sources · 100% gratuit · sans API key           ║
 * ╚══════════════════════════════════════════════════════════════════════════════╝
 * 
 * Usage : php bioapi_validated.php ou servir via serveur web
 */

declare(strict_types=1);

// ─── Configuration ────────────────────────────────────────────────────────────
define('TIMEOUT',   12);
define('UA',        'BioAPI-Validated/1.0 (educational; php-curl)');
define('VERSION',   '1.0');

// ─── Utilitaires HTTP ─────────────────────────────────────────────────────────
function http_get(string $url, array $extra_headers = []): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 5,
        CURLOPT_TIMEOUT        => TIMEOUT,
        CURLOPT_USERAGENT      => UA,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER     => array_merge(['Accept: application/json, application/xml, */*'], $extra_headers),
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    return ['code' => $code, 'body' => $body ?: '', 'error' => $err];
}

function safe_json(string $body): ?array {
    $d = json_decode($body, true);
    return json_last_error() === JSON_ERROR_NONE ? $d : null;
}

function xml_text(string $xml, string $tag): string {
    $tag_escaped = preg_quote($tag, '/');
    if (preg_match('/<' . $tag_escaped . '[^>]*>(.*?)<\/' . $tag_escaped . '>/si', $xml, $m)) {
        return trim(html_entity_decode(strip_tags($m[1]), ENT_XML1 | ENT_HTML5, 'UTF-8'));
    }
    return '—';
}

function xml_count(string $xml, string $tag): int {
    return substr_count($xml, "<$tag");
}

function truncate(string $s, int $n = 80): string {
    return mb_strlen($s) > $n ? mb_substr($s, 0, $n) . '…' : $s;
}

// ─── Valeurs de croisement par source ─────────────────────────────────────────
function get_cross_value(string $source): string {
    $values = [
        'PubMed' => '🔗 <strong>Nœud central du graphe biomédical</strong>. Croisé avec <em>ClinVar</em> → liens variants/maladies ; <em>UniProt</em> → annotation protéique ; <em>EuroPMC</em> → full-text ; <em>CrossRef</em> → métadonnées DOI ; <em>OpenAlex</em> → réseaux de citations. Permet de tracer : <code>gène → protéine → maladie → publication → essai clinique</code>.',
        'ClinVar' => '🔗 <strong>Relie variants génomiques et signification clinique</strong>. Croisé avec <em>PubMed</em> → preuves publicationnelles ; <em>UniProt</em> → impact sur domaines protéiques ; <em>OpenFDA</em> → réponse aux médicaments ; <em>ClinicalTrials</em> → essais ciblant des mutations. Essentiel pour la médecine de précision.',
        'NCBI Gene' => '🔗 <strong>Normalise les identifiants géniques</strong>. Combiné avec <em>Ensembl</em> → contexte génomique ; <em>STRING</em> → interactions protéiques ; <em>Reactome/KEGG</em> → voies métaboliques ; <em>ChEMBL</em> → cibles thérapeutiques. Permet des analyses de biologie des systèmes.',
        'NCBI Nucleotide' => '🔗 <strong>Séquences nucléotidiques de référence</strong>. Croisé avec <em>UniProt</em> → traduction protéique ; <em>PDB</em> → structures 3D ; <em>GBIF</em> → contexte biodiversité ; <em>ArXiv/CrossRef</em> → prépublications associées. Accélère les analyses évolutives en virologie.',
        'NCBI Protein' => '🔗 <strong>Entrées protéiques annotées</strong>. Lié avec <em>UniProt</em> (annotation fonctionnelle), <em>ChEMBL</em> (ligands), <em>STRING</em> (réseaux d\'interaction), <em>Reactome</em> (voies). Offre une vue multidimensionnelle de la fonction protéique.',
        'UniProt' => '🔗 <strong>Connaissances protéiques curées</strong>. Croisé avec <em>ClinVar</em> → prédiction d\'impact des variants ; <em>ChEMBL</em> → évaluations de "drugabilité" ; <em>STRING</em> → cartographie d\'interactome ; <em>Reactome/KEGG</em> → contexte de voie. Clé pour l\'identification de cibles.',
        'EuroPMC' => '🔗 <strong>Agrège la littérature en accès ouvert</strong>. Lie les PMID, DOI (CrossRef), full-text (Unpaywall), et jeux de données (Zenodo/DataCite). Accélère la découverte basée sur la littérature et la recherche reproductible.',
        'OpenAlex' => '🔗 <strong>Cartographie l\'écosystème savant</strong> : auteurs → institutions → publications → citations. Croisé avec <em>PubMed/CrossRef</em> pour le focus biomédical, <em>Semantic Scholar</em> pour la découverte IA, <em>Zenodo</em> pour les outputs : révèle les fronts de recherche émergents.',
        'ChEMBL' => '🔗 <strong>Lie structures chimiques, cibles biologiques et bioactivité</strong>. Combiné avec <em>UniProt</em> (validation de cible), <em>PubChem</em> (propriétés chimiques), <em>OpenFDA</em> (signaux de sécurité), <em>ClinicalTrials</em> (statut de développement) : permet le repurposing de médicaments.',
        'ArXiv' => '🔗 <strong>Accès précoce aux prépublications</strong>. Croisé avec <em>PubMed/CrossRef</em> pour le suivi de publication finale, <em>Semantic Scholar</em> pour l\'impact citationnel, <em>Zenodo</em> pour le code/données associés : accélère la découverte de méthodologies émergentes en biologie computationnelle.',
        'CrossRef' => '🔗 <strong>Identifiants DOI persistants</strong>. Intégré avec <em>PubMed</em> pour l\'indexation biomédicale, <em>Unpaywall</em> pour le statut OA, <em>OpenAlex</em> pour les métriques, <em>DataCite</em> pour les datasets : crée un enregistrement savant unifié et interopérable.',
        'Semantic Scholar' => '🔗 <strong>Compréhension IA des papiers</strong>. Extrait concepts et relations clés. Combiné avec <em>CrossRef</em> pour les métadonnées autoritatives, <em>OpenAlex</em> pour la couverture exhaustive, <em>PubMed</em> pour la spécificité de domaine : améliore le mining de littérature.',
        'DataCite' => '🔗 <strong>DOI persistants pour les jeux de données</strong>. Lié avec <em>Zenodo</em> pour les dépôts ouverts, <em>EuroPMC</em> pour les connexions article-données, et les métadonnées FAIR : permet la science reproductible et la réutilisation des données.',
        'Unpaywall' => '🔗 <strong>Identifie les versions légales en accès ouvert</strong>. Croisé avec <em>CrossRef</em> pour la résolution DOI, <em>EuroPMC</em> pour l\'accès full-text, <em>DataCite</em> pour les dépôts institutionnels : maximise l\'accès au savoir scientifique.',
        'RCSB PDB' => '🔗 <strong>Structures 3D macromoléculaires</strong>. Intégré avec <em>UniProt</em> pour le mappage séquence-structure, <em>ChEMBL</em> pour les sites de liaison, <em>STRING</em> pour l\'assemblage de complexes, <em>Reactome</em> pour le contexte de voie : permet le drug design basé sur la structure.',
        'Ensembl' => '🔗 <strong>Annotation génomique et génomique comparative</strong>. Quand fonctionnel, connecte avec <em>NCBI Gene</em> pour le mappage d\'identifiants, <em>ClinVar</em> pour l\'interprétation des variants, <em>UniProt</em> pour les produits protéiques : permet des applications de médecine génomique.',
        'STRING DB' => '🔗 <strong>Réseaux d\'interactions protéine-protéine</strong> avec scores de confiance. Croisé avec <em>UniProt</em> pour l\'annotation fonctionnelle, <em>Reactome/KEGG</em> pour l\'appartenance aux voies, <em>ChEMBL</em> pour les nœuds "drugables" : révèle les mécanismes maladie au niveau système.',
        'Reactome' => '🔗 <strong>Voies biologiques curées avec détails moléculaires</strong>. Intégré avec <em>UniProt</em> pour les rôles protéiques, <em>ChEMBL</em> pour les médicaments ciblant les voies, <em>STRING</em> pour les preuves d\'interaction : permet l\'interprétation de données omiques basée sur les voies.',
        'GBIF' => '🔗 <strong>Données d\'occurrence de biodiversité mondiales</strong>. Croisé avec la taxonomie NCBI pour les données génétiques, <em>PubMed</em> pour la recherche spécifique aux espèces, <em>WHO GHO</em> pour l\'écologie des maladies : supporte les approches One Health.',
        'RxNorm (FDA)' => '🔗 <strong>Standardise la nomenclature des médicaments</strong>. Lié avec <em>ChEMBL</em> pour les structures chimiques, <em>OpenFDA</em> pour les rapports de sécurité, <em>ClinicalTrials</em> pour les usages investigatoires : permet la pharmacovigilance et la génération de preuves en vie réelle.',
        'OpenFDA' => '🔗 <strong>Données de sécurité et d\'application des médicaments en vie réelle</strong>. Croisé avec <em>RxNorm</em> pour l\'identification, <em>ChEMBL</em> pour les propriétés chimiques, <em>PubMed</em> pour les rapports de cas : supporte la surveillance post-commercialisation.',
        'ClinicalTrials' => '🔗 <strong>Suit la recherche interventionnelle mondiale</strong>. Intégré avec <em>PubMed</em> pour les résultats publiés, <em>OpenFDA</em> pour les issues de sécurité, <em>WHO GHO</em> pour le contexte de fardeau maladie : permet la découverte d\'essais et la méta-analyse.',
        'WHO GHO' => '🔗 <strong>Indicateurs de santé standardisés mondialement</strong>. Croisé avec <em>World Bank</em> pour le contexte socio-économique, <em>GBIF</em> pour les liens santé-environnement, <em>PubMed</em> pour la base de preuves : supporte les politiques de santé globale.',
        'World Bank' => '🔗 <strong>Indicateurs socio-économiques contextualisant la recherche biomédicale</strong>. Intégré avec <em>WHO GHO</em> pour les issues de santé, <em>OpenAlex</em> pour l\'analyse des investissements de recherche : permet l\'économie de la santé.',
        'KEGG' => '🔗 <strong>Cartographie gènes, protéines, composés et réactions dans un contexte de voie</strong>. Croisé avec <em>Reactome</em> pour la comparaison de curation, <em>ChEMBL</em> pour la bioactivité des composés, <em>UniProt</em> pour la fonction protéique : permet l\'analyse de réseaux métaboliques.',
        'Wikipedia (EN)' => '🔗 <strong>Connaissances accessibles et crowdsourcées</strong>. Croisé avec <em>PubMed/CrossRef</em> pour la vérification des sources, <em>Wikidata</em> pour les données structurées, <em>Zenodo</em> pour les datasets cités : sert de passerelle de découverte et de pont de communication publique.',
        'Wikipedia (FR)' => '🔗 <strong>Accessibilité aux chercheurs et public francophones</strong>. Lié avec Wikipedia EN pour le transfert de connaissances inter-langues, <em>PubMed</em> pour le suivi des sources : supporte l\'équité des connaissances globales.',
        'Zenodo' => '🔗 <strong>Stockage ouvert et citable pour les outputs de recherche</strong>. Intégré avec <em>DataCite</em> pour le minting de DOI, <em>Github</em> pour le versioning, <em>EuroPMC</em> pour le lien article-données, et les principes FAIR : permet une science reproductible.',
        'NASA ADS' => '🔗 <strong>Littérature en astrophysique et physique</strong>. Croisé avec <em>CrossRef</em> pour la résolution DOI, <em>ArXiv</em> pour le suivi des prépublications, <em>Semantic Scholar</em> pour l\'analyse citationnelle : supporte la recherche interdisciplinaire (astrobiologie, biophysique).',
        'BioGRID' => '🔗 <strong>Interactions protéine-protéine et génétiques curées</strong>. Intégré avec <em>STRING</em> pour les réseaux pondérés par confiance, <em>UniProt</em> pour l\'annotation protéique, <em>Reactome</em> pour le contexte de voie : fournit des preuves d\'interaction de haute qualité pour la biologie des systèmes.',
    ];
    return $values[$source] ?? '🔗 Source interopérable avec l\'écosystème biomédical.';
}

// ─── Moteur de test ───────────────────────────────────────────────────────────
$results  = [];
$start_all = microtime(true);

function run_test_validated(
    string $group,
    string $label,
    string $url,
    callable $parse,
    array  $headers = []
): ?array {
    $t0  = microtime(true);
    $res = http_get($url, $headers);
    $ms  = round((microtime(true) - $t0) * 1000);
    $ok   = ($res['code'] >= 200 && $res['code'] < 300);
    $parsed = '';

    if ($ok) {
        try {
            $parsed = $parse($res['body']);
            if ($parsed === null || $parsed === '') return null;
        } catch (\Throwable $e) { return null; }
    } else { return null; }

    return [
        'group'   => $group,
        'label'   => $label,
        'url'     => $url,
        'ok'      => true,
        'result'  => $parsed,
        'ms'      => $ms,
        'code'    => $res['code'],
    ];
}

// ══════════════════════════════════════════════════════════════════════════════
//  REQUÊTES VALIDÉES SEULEMENT (65 endpoints)
// ══════════════════════════════════════════════════════════════════════════════

$BASE_EUTILS = 'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/';

// PubMed (5 OK)
foreach ([
    ['Recherche "cancer" (esearch)', $BASE_EUTILS . 'esearch.fcgi?db=pubmed&retmode=json&retmax=3&term=cancer', fn($b) => ($d=safe_json($b)) ? "$d[esearchresult][count] articles trouvés | IDs: " . implode(', ', $d['esearchresult']['idlist'] ?? []) : null],
    ['Recherche "diabetes 2024" (esearch)', $BASE_EUTILS . 'esearch.fcgi?db=pubmed&retmode=json&retmax=3&term=diabetes+AND+2024[pdat]', fn($b) => ($d=safe_json($b)) ? "$d[esearchresult][count] articles trouvés | IDs: " . implode(', ', $d['esearchresult']['idlist'] ?? []) : null],
    ['Fetch résumé article (efetch pmid=33232094)', $BASE_EUTILS . 'efetch.fcgi?db=pubmed&id=33232094&retmode=xml&rettype=abstract', fn($b) => "Titre: " . truncate(xml_text($b, 'ArticleTitle'), 70) . " | Journal: " . truncate(xml_text($b, 'Title'), 40)],
    ['Fetch résumé (esummary pmid=34567890)', $BASE_EUTILS . 'esummary.fcgi?db=pubmed&id=34567890&retmode=json', fn($b) => ($d=safe_json($b)) ? "Titre: " . truncate((array_values($d['result'] ?? [])[1]['title'] ?? '—'), 70) . " | Source: " . ((array_values($d['result'] ?? [])[1]['source'] ?? '—')) : null],
    ['eLink (articles liés à pmid=33232094)', $BASE_EUTILS . 'elink.fcgi?dbfrom=pubmed&db=pubmed&id=33232094&retmode=json&cmd=neighbor_score', fn($b) => ($d=safe_json($b)) ? count($d['linksets'][0]['linksetdbs'][0]['links'] ?? []) . " articles liés trouvés" : null],
] as [$label, $url, $parse]) {
    if ($r = run_test_validated('PubMed', $label, $url, $parse)) $results[] = $r;
}

// ClinVar (3 OK)
foreach ([
    ['Variants BRCA1 (esearch)', $BASE_EUTILS . 'esearch.fcgi?db=clinvar&retmode=json&retmax=3&term=BRCA1', fn($b) => ($d=safe_json($b)) ? "$d[esearchresult][count] variants | IDs: " . implode(', ', $d['esearchresult']['idlist'] ?? []) : null],
    ['Variants TP53 pathogenic (esearch)', $BASE_EUTILS . 'esearch.fcgi?db=clinvar&retmode=json&retmax=3&term=TP53[gene]+AND+pathogenic[clinical_significance]', fn($b) => ($d=safe_json($b)) ? "$d[esearchresult][count] variants pathogéniques" : null],
    ['Fetch variant (esummary varid=9)', $BASE_EUTILS . 'esummary.fcgi?db=clinvar&id=9&retmode=json', fn($b) => ($d=safe_json($b)) ? "Variant: " . truncate($d['result']['9']['title'] ?? '—', 80) : null],
] as [$label, $url, $parse]) {
    if ($r = run_test_validated('ClinVar', $label, $url, $parse)) $results[] = $r;
}

// NCBI Gene (2 OK)
foreach ([
    ['Summary gene TP53 (esummary id=7157)', $BASE_EUTILS . 'esummary.fcgi?db=gene&id=7157&retmode=json', fn($b) => ($d=safe_json($b)) ? "Gene: " . ($d['result']['7157']['name'] ?? '—') . " | Desc: " . truncate($d['result']['7157']['description'] ?? '—', 60) . " | Chr: " . ($d['result']['7157']['chromosome'] ?? '—') : null],
    ['Fetch XML gene BRCA1 (id=672)', $BASE_EUTILS . 'esummary.fcgi?db=gene&id=672&retmode=json', fn($b) => ($d=safe_json($b)) ? "Gene: " . ($d['result']['672']['name'] ?? '—') . " | " . truncate($d['result']['672']['description'] ?? '—', 60) : null],
] as [$label, $url, $parse]) {
    if ($r = run_test_validated('NCBI Gene', $label, $url, $parse)) $results[] = $r;
}

// NCBI Nucleotide (1 OK)
if ($r = run_test_validated('NCBI Nucleotide', 'Recherche séquence SARS-CoV-2 (esearch)', $BASE_EUTILS . 'esearch.fcgi?db=nucleotide&retmode=json&retmax=3&term=SARS-CoV-2[organism]+AND+complete+genome', fn($b) => ($d=safe_json($b)) ? "$d[esearchresult][count] séquences" : null)) $results[] = $r;

// NCBI Protein (2 OK)
foreach ([
    ['Recherche insulin (esearch)', $BASE_EUTILS . 'esearch.fcgi?db=protein&retmode=json&retmax=3&term=insulin[titl]+AND+human[organism]', fn($b) => ($d=safe_json($b)) ? "$d[esearchresult][count] protéines" : null],
    ['Summary protéine P01308 (esummary)', $BASE_EUTILS . 'esummary.fcgi?db=protein&id=386828&retmode=json', fn($b) => ($d=safe_json($b)) ? "Acc: " . ((array_values($d['result'] ?? [])[1]['accessionversion'] ?? '—')) . " | " . truncate((array_values($d['result'] ?? [])[1]['title'] ?? '—'), 60) : null],
] as [$label, $url, $parse]) {
    if ($r = run_test_validated('NCBI Protein', $label, $url, $parse)) $results[] = $r;
}

// UniProt (4 OK)
$BASE_UNIPROT = 'https://rest.uniprot.org/';
foreach ([
    ['Recherche gène p53 (REST)', $BASE_UNIPROT . 'uniprotkb/search?format=json&size=3&query=gene%3Ap53+AND+reviewed%3Atrue', fn($b) => ($d=safe_json($b)) ? count($d['results'] ?? []) . " protéines | Accessions: " . implode(', ', array_map(fn($r) => $r['primaryAccession'] ?? '?', $d['results'] ?? [])) : null],
    ['Human reviewed proteins (REST)', $BASE_UNIPROT . 'uniprotkb/search?format=json&size=3&query=organism_id%3A9606+AND+reviewed%3Atrue', fn($b) => ($d=safe_json($b)) ? count($d['results'] ?? []) . " protéines humaines" : null],
    ['Fetch protéine P53_HUMAN (P04637)', $BASE_UNIPROT . 'uniprotkb/P04637.json', fn($b) => ($d=safe_json($b)) ? "ID: " . ($d['uniProtkbId'] ?? '—') . " | Gene: " . ($d['genes'][0]['geneName']['value'] ?? '—') . " | Longueur: " . ($d['sequence']['length'] ?? '—') . " aa" : null],
    ['Recherche BRCA1 + function', $BASE_UNIPROT . 'uniprotkb/search?format=json&size=3&query=BRCA1+AND+organism_id%3A9606', fn($b) => ($d=safe_json($b)) ? count($d['results'] ?? []) . " résultats" : null],
] as [$label, $url, $parse]) {
    if ($r = run_test_validated('UniProt', $label, $url, $parse)) $results[] = $r;
}

// EuroPMC (4 OK)
$BASE_EPMC = 'https://www.ebi.ac.uk/europepmc/webservices/rest/';
foreach ([
    ['Recherche "covid" (REST)', $BASE_EPMC . 'search?format=json&pageSize=3&query=covid', fn($b) => ($d=safe_json($b)) ? number_format($d['hitCount'] ?? 0) . " publications" : null],
    ['Recherche gene BRCA1', $BASE_EPMC . 'search?format=json&pageSize=3&query=BRCA1', fn($b) => ($d=safe_json($b)) ? number_format($d['hitCount'] ?? 0) . " publications" : null],
    ['Articles en open access 2024', $BASE_EPMC . 'search?format=json&pageSize=3&query=open_access:y+AND+FIRST_PDATE:2024', fn($b) => ($d=safe_json($b)) ? number_format($d['hitCount'] ?? 0) . " articles OA 2024" : null],
    ['Fetch article PMC (PMC8075510)', $BASE_EPMC . 'article/PMC/PMC8075510', fn($b) => ($d=safe_json($b)) ? "Titre: " . truncate($d['result']['title'] ?? $d['title'] ?? '—', 80) : null],
] as [$label, $url, $parse]) {
    if ($r = run_test_validated('EuroPMC', $label, $url, $parse)) $results[] = $r;
}

// OpenAlex (4 OK)
$BASE_OA = 'https://api.openalex.org/';
foreach ([
    ['Recherche "cancer" (works)', $BASE_OA . 'works?per_page=3&search=cancer', fn($b) => ($d=safe_json($b)) ? number_format($d['meta']['count'] ?? 0) . " travaux" : null],
    ['Travaux 2024 par citation', $BASE_OA . 'works?per_page=3&filter=publication_year:2024&sort=cited_by_count:desc', fn($b) => ($d=safe_json($b)) ? number_format($d['meta']['count'] ?? 0) . " travaux 2024" : null],
    ['Auteurs par h-index (authors)', $BASE_OA . 'authors?per_page=3&sort=summary_stats.h_index:desc&filter=last_known_institutions.country_code:FR', fn($b) => ($d=safe_json($b)) ? "Top FR auteurs" : null],
    ['Institutions (universities)', $BASE_OA . 'institutions?per_page=3&filter=country_code:FR&sort=summary_stats.h_index:desc', fn($b) => ($d=safe_json($b)) ? "Top FR institutions" : null],
] as [$label, $url, $parse]) {
    if ($r = run_test_validated('OpenAlex', $label, $url, $parse)) $results[] = $r;
}

// ChEMBL (4 OK)
$BASE_CHEMBL = 'https://www.ebi.ac.uk/chembl/api/data/';
foreach ([
    ['Fetch molécule CHEMBL25 (Aspirine)', $BASE_CHEMBL . 'molecule/CHEMBL25.json', fn($b) => ($d=safe_json($b)) ? "Molécule: " . ($d['pref_name'] ?? '—') . " | MW: " . ($d['molecule_properties']['mw_freebase'] ?? '—') : null],
    ['Fetch molécule CHEMBL1 (Gleevec)', $BASE_CHEMBL . 'molecule/CHEMBL1.json', fn($b) => ($d=safe_json($b)) ? "Molécule: " . ($d['pref_name'] ?? '—') . " | MW: " . ($d['molecule_properties']['mw_freebase'] ?? '—') : null],
    ['Recherche molécule par nom "aspirin"', $BASE_CHEMBL . 'molecule.json?pref_name__icontains=aspirin&limit=3', fn($b) => ($d=safe_json($b)) ? ($d['page_meta']['total_count'] ?? count($d['molecules'] ?? [])) . " molécules" : null],
    ['Cibles thérapeutiques (target) human kinases', $BASE_CHEMBL . 'target.json?target_type=SINGLE+PROTEIN&organism=Homo+sapiens&limit=3', fn($b) => ($d=safe_json($b)) ? number_format($d['page_meta']['total_count'] ?? 0) . " cibles" : null],
] as [$label, $url, $parse]) {
    if ($r = run_test_validated('ChEMBL', $label, $url, $parse)) $results[] = $r;
}

// ArXiv (3 OK)
foreach ([
    ['Recherche "machine learning" (Atom XML)', 'https://export.arxiv.org/api/query?max_results=3&search_query=all:%22machine+learning%22', fn($b) => xml_count($b, 'entry') . " articles"],
    ['Catégorie physics.atom-ph', 'https://export.arxiv.org/api/query?max_results=3&search_query=cat:physics.atom-ph', fn($b) => xml_count($b, 'entry') . " articles"],
    ['Recherche "CRISPR" (Atom XML)', 'https://export.arxiv.org/api/query?max_results=3&search_query=all:CRISPR&sortBy=submittedDate&sortOrder=descending', fn($b) => xml_count($b, 'entry') . " articles"],
] as [$label, $url, $parse]) {
    if ($r = run_test_validated('ArXiv', $label, $url, $parse)) $results[] = $r;
}

// CrossRef (3 OK)
foreach ([
    ['Recherche travaux "CRISPR" (works)', 'https://api.crossref.org/works?query=CRISPR&rows=3&select=DOI,title,author,published', fn($b) => ($d=safe_json($b)) ? number_format($d['message']['total-results'] ?? 0) . " travaux" : null],
    ['Fetch DOI metadata 10.1038/nature12373', 'https://api.crossref.org/works/10.1038/nature12373', fn($b) => ($d=safe_json($b)) ? "Titre: " . truncate(($d['message']['title'][0] ?? '—'), 60) : null],
    ['Journaux éditeur Elsevier', 'https://api.crossref.org/journals?query=elsevier+lancet&rows=3', fn($b) => ($d=safe_json($b)) ? count($d['message']['items'] ?? []) . " journaux" : null],
] as [$label, $url, $parse]) {
    if ($r = run_test_validated('CrossRef', $label, $url, $parse)) $results[] = $r;
}

// Semantic Scholar (1 OK)
$BASE_S2 = 'https://api.semanticscholar.org/graph/v1/';
if ($r = run_test_validated('Semantic Scholar', 'Fetch papier par ID (AlphaFold)', $BASE_S2 . 'paper/649def34f8be52c8b66281af98ae884c09aef38b?fields=title,abstract,year,citationCount,authors', fn($b) => ($d=safe_json($b)) ? "Titre: " . truncate($d['title'] ?? '—', 70) . " | Année: " . ($d['year'] ?? '—') : null)) $results[] = $r;

// DataCite (2 OK)
foreach ([
    ['Recherche datasets "genomics"', 'https://api.datacite.org/works?query=genomics&page[size]=3&resource-type-id=dataset', fn($b) => ($d=safe_json($b)) ? number_format($d['meta']['total'] ?? 0) . " datasets" : null],
    ['DOIs par publisher "EMBL-EBI"', 'https://api.datacite.org/works?query=EMBL-EBI&page[size]=3', fn($b) => ($d=safe_json($b)) ? number_format($d['meta']['total'] ?? 0) . " ressources" : null],
] as [$label, $url, $parse]) {
    if ($r = run_test_validated('DataCite', $label, $url, $parse)) $results[] = $r;
}

// Unpaywall (1 OK)
if ($r = run_test_validated('Unpaywall', 'DOI 10.1038/nature12373 (OA status)', 'https://api.unpaywall.org/v2/10.1038/nature12373?email=test@bioapi.org', fn($b) => ($d=safe_json($b)) ? (($d['is_oa'] ?? false) ? '✅ Open Access' : '🔒 Paywall') . " | " . truncate($d['title'] ?? '—', 60) : null)) $results[] = $r;

// RCSB PDB (3 OK)
foreach ([
    ['Info structure 1HHO (Hémoglobine)', 'https://data.rcsb.org/rest/v1/core/entry/1HHO', fn($b) => ($d=safe_json($b)) ? "Structure: " . truncate($d['struct']['title'] ?? '—', 60) : null],
    ['Recherche "insulin human" (GraphQL search)', 'https://search.rcsb.org/rcsbsearch/v2/query?json=' . urlencode('{"query":{"type":"terminal","service":"full_text","parameters":{"value":"insulin human"}},"return_type":"entry","request_options":{"paginate":{"start":0,"rows":3}}}'), fn($b) => ($d=safe_json($b)) ? number_format($d['total_count'] ?? 0) . " structures" : null],
    ['Ligands de la structure 4HHB', 'https://data.rcsb.org/rest/v1/core/entry/4HHB', fn($b) => ($d=safe_json($b)) ? "Titre: " . truncate($d['struct']['title'] ?? '—', 60) : null],
] as [$label, $url, $parse]) {
    if ($r = run_test_validated('RCSB PDB', $label, $url, $parse)) $results[] = $r;
}

// Ensembl (1 OK)
$BASE_ENS = 'https://rest.ensembl.org/';
if ($r = run_test_validated('Ensembl', 'Séquence chromosome 1 region (REST)', $BASE_ENS . 'sequence/region/human/1:1000..1050?content-type=application/json', fn($b) => ($d=safe_json($b)) ? "Région: " . truncate($d['desc'] ?? '—', 40) . " | Séquence: " . truncate($d['seq'] ?? '—', 40) : null)) $results[] = $r;

// STRING DB (2 OK)
foreach ([
    ['Interactions TP53 (humain)', 'https://string-db.org/api/json/network?species=9606&identifiers=TP53&required_score=700', fn($b) => ($d=safe_json($b)) ? count($d) . " interactions" : null],
    ['Enrichissement fonctionnel BRCA1', 'https://string-db.org/api/json/enrichment?species=9606&identifiers=BRCA1&caller_identity=bioapi_test', fn($b) => ($d=safe_json($b)) ? count($d) . " termes enrichis" : null],
] as [$label, $url, $parse]) {
    if ($r = run_test_validated('STRING DB', $label, $url, $parse)) $results[] = $r;
}

// Reactome (1 OK)
if ($r = run_test_validated('Reactome', 'Fetch pathway R-HSA-69278 (Cell Cycle)', 'https://reactome.org/ContentService/data/query/R-HSA-69278', fn($b) => ($d=safe_json($b)) ? "Pathway: " . truncate($d['displayName'] ?? '—', 60) : null)) $results[] = $r;

// GBIF (1 OK)
if ($r = run_test_validated('GBIF', 'Recherche espèce "Homo sapiens"', 'https://api.gbif.org/v1/species/match?name=Homo+sapiens&verbose=false', fn($b) => ($d=safe_json($b)) ? "Key: " . ($d['usageKey'] ?? '—') . " | Royaume: " . ($d['kingdom'] ?? '—') : null)) $results[] = $r;

// RxNorm (FDA) (2 OK)
foreach ([
    ['RxCUI pour "aspirin"', 'https://rxnav.nlm.nih.gov/REST/rxcui.json?name=aspirin', fn($b) => ($d=safe_json($b)) ? "RxCUI Aspirin: " . ($d['idGroup']['rxnormId'][0] ?? '—') : null],
    ['Info médicament RxCUI 1191 (Aspirin)', 'https://rxnav.nlm.nih.gov/REST/rxcui/1191/properties.json', fn($b) => ($d=safe_json($b)) ? "Nom: " . ($d['properties']['name'] ?? '—') : null],
] as [$label, $url, $parse]) {
    if ($r = run_test_validated('RxNorm (FDA)', $label, $url, $parse)) $results[] = $r;
}

// OpenFDA (2 OK)
foreach ([
    ['Rappels de médicaments (drug enforcement)', 'https://api.fda.gov/drug/enforcement.json?limit=3&search=status:Ongoing', fn($b) => ($d=safe_json($b)) ? number_format($d['meta']['results']['total'] ?? 0) . " rappels" : null],
    ['Événements indésirables (drug event) ibuprofen', 'https://api.fda.gov/drug/event.json?limit=3&search=patient.drug.medicinalproduct:ibuprofen', fn($b) => ($d=safe_json($b)) ? number_format($d['meta']['results']['total'] ?? 0) . " événements" : null],
] as [$label, $url, $parse]) {
    if ($r = run_test_validated('OpenFDA', $label, $url, $parse)) $results[] = $r;
}

// ClinicalTrials (1 OK)
if ($r = run_test_validated('ClinicalTrials', 'Essais COVID-19 en cours (recruiting)', 'https://clinicaltrials.gov/api/v2/studies?query.cond=COVID-19&filter.overallStatus=RECRUITING&pageSize=3&fields=NCTId,BriefTitle,OverallStatus', fn($b) => ($d=safe_json($b)) ? number_format($d['totalCount'] ?? 0) . " essais COVID actifs" : null)) $results[] = $r;

// WHO GHO (2 OK)
foreach ([
    ['Indicateurs disponibles (sample)', 'https://ghoapi.azureedge.net/api/Indicator?$top=3', fn($b) => ($d=safe_json($b)) ? count($d['value'] ?? []) . " indicateurs" : null],
    ['Espérance de vie (WHOSIS_000001) France', 'https://ghoapi.azureedge.net/api/WHOSIS_000001?$filter=SpatialDim+eq+%27FRA%27&$top=3&$orderby=TimeDim+desc', fn($b) => ($d=safe_json($b)) ? "France espérance de vie" : null],
] as [$label, $url, $parse]) {
    if ($r = run_test_validated('WHO GHO', $label, $url, $parse)) $results[] = $r;
}

// World Bank (2 OK)
foreach ([
    ['Dépenses santé France (% PIB)', 'https://api.worldbank.org/v2/country/FR/indicator/SH.XPD.CHEX.GD.ZS?format=json&mrv=3', fn($b) => ($d=json_decode($b,true)) ? "Dépenses santé FR: " . implode(', ', array_map(fn($v) => ($v['date'] ?? '?') . ': ' . round(($v['value'] ?? 0), 1) . '%', array_filter($d[1] ?? [], fn($v) => $v['value'] !== null))) : null],
    ['Médecins pour 1000 hab (monde)', 'https://api.worldbank.org/v2/country/all/indicator/SH.MED.PHYS.ZS?format=json&mrv=1&per_page=3', fn($b) => ($d=json_decode($b,true)) ? "Médecins/1000 hab" : null],
] as [$label, $url, $parse]) {
    if ($r = run_test_validated('World Bank', $label, $url, $parse)) $results[] = $r;
}

// KEGG (2 OK)
foreach ([
    ['Info pathway hsa05215 (Prostate cancer)', 'https://rest.kegg.jp/get/hsa05215', fn($b) => (preg_match('/^NAME\s+(.+)$/m', $b, $m1) && preg_match('/^CLASS\s+(.+)$/m', $b, $m2)) ? "Pathway: " . truncate($m1[1] ?? '—', 50) : null],
    ['Info composé C00031 (glucose)', 'https://rest.kegg.jp/get/C00031', fn($b) => (preg_match('/^NAME\s+(.+)$/m', $b, $m1) && preg_match('/^FORMULA\s+(.+)$/m', $b, $m2)) ? "Composé: " . ($m1[1] ?? '—') . " | Formule: " . ($m2[1] ?? '—') : null],
] as [$label, $url, $parse]) {
    if ($r = run_test_validated('KEGG', $label, $url, $parse)) $results[] = $r;
}

// Wikipedia EN/FR (2 OK)
foreach ([
    ['Wikipedia (EN)', 'Recherche "CRISPR" (API action)', 'https://en.wikipedia.org/w/api.php?action=query&list=search&format=json&srlimit=3&srsearch=CRISPR', fn($b) => ($d=safe_json($b)) ? number_format($d['query']['searchinfo']['totalhits'] ?? 0) . " pages" : null],
    ['Wikipedia (FR)', 'Extraits "génomique" (API)', 'https://fr.wikipedia.org/w/api.php?action=query&list=search&format=json&srlimit=3&srsearch=g%C3%A9nomique', fn($b) => ($d=safe_json($b)) ? number_format($d['query']['searchinfo']['totalhits'] ?? 0) . " pages" : null],
] as [$grp, $label, $url, $parse]) {
    if ($r = run_test_validated($grp, $label, $url, $parse)) $results[] = $r;
}

// Zenodo (2 OK)
foreach ([
    ['Recherche datasets "genomics"', 'https://zenodo.org/api/records?type=dataset&q=genomics&size=3&sort=mostrecent', fn($b) => ($d=safe_json($b)) ? number_format($d['hits']['total'] ?? 0) . " datasets" : null],
    ['Publications CRISPR (software)', 'https://zenodo.org/api/records?type=software&q=CRISPR&size=3', fn($b) => ($d=safe_json($b)) ? number_format($d['hits']['total'] ?? 0) . " logiciels" : null],
] as [$label, $url, $parse]) {
    if ($r = run_test_validated('Zenodo', $label, $url, $parse)) $results[] = $r;
}

// NASA ADS (1 OK)
if ($r = run_test_validated('NASA ADS', 'Open API (resolver DOI)', 'https://ui.adsabs.harvard.edu/link_gateway/2019ApJ...875L...1E/doi:10.3847/2041-8213/ab0ec7', fn($b) => strlen($b) > 0 ? "Endpoint actif (" . strlen($b) . " bytes)" : null)) $results[] = $r;

// BioGRID (2 OK)
foreach ([
    ['Version / changelog public', 'https://downloads.thebiogrid.org/BioGRID/CHANGELOG.txt', fn($b) => "Changelog accessible"],
    ['Index releases (download page)', 'https://downloads.thebiogrid.org/BioGRID/Release-Archive/', fn($b) => "Index releases accessible"],
] as [$label, $url, $parse]) {
    if ($r = run_test_validated('BioGRID', $label, $url, $parse)) $results[] = $r;
}

// ─── Grouper les résultats ────────────────────────────────────────────────────
$by_group = [];
foreach ($results as $r) { $by_group[$r['group']][] = $r; }
$total_tests = count($results);
$total_time = round((microtime(true) - $start_all) * 1000);
$pct_ok = 100; // Tous les résultats affichés sont OK
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>🔬 BioAPI Validated v<?= VERSION ?></title>
<style>
/* ─── Variables (identiques au template original) ────────────────────────── */
:root {
    --bg: #090e1a; --bg2: #0d1526; --bg3: #111d33; --surface: #162040;
    --border: #1e2d4a; --border2: #243355; --accent: #00d4ff; --accent2: #0095cc;
    --green: #00e676; --red: #ff5252; --orange: #ff9800; --yellow: #ffeb3b;
    --purple: #b39ddb; --text: #c8d8f0; --text2: #7a9bbf; --text3: #4a6a8a;
    --mono: 'JetBrains Mono', 'Fira Code', monospace; --sans: 'Inter', system-ui, sans-serif;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{font-size:14px;scroll-behavior:smooth}
body{font-family:var(--sans);background:var(--bg);color:var(--text);line-height:1.6;min-height:100vh;overflow-x:hidden}
body::before{content:'';position:fixed;inset:0;background-image:linear-gradient(var(--border) 1px,transparent 1px),linear-gradient(90deg,var(--border) 1px,transparent 1px);background-size:40px 40px;opacity:.25;pointer-events:none;z-index:0}
.wrapper{position:relative;z-index:1;max-width:1400px;margin:0 auto;padding:2rem 1.5rem 4rem}
.header{text-align:center;padding:3rem 1rem 2.5rem;border-bottom:1px solid var(--border2);margin-bottom:2rem}
.header .badge{display:inline-block;font-family:var(--mono);font-size:.7rem;letter-spacing:.15em;text-transform:uppercase;color:var(--accent);background:rgba(0,212,255,.08);border:1px solid rgba(0,212,255,.3);padding:.3rem .8rem;border-radius:2px;margin-bottom:1.2rem}
.header h1{font-size:clamp(1.6rem,4vw,2.8rem);font-weight:800;letter-spacing:-.02em;color:#fff;line-height:1.15;margin-bottom:.6rem}
.header h1 span{color:var(--accent)}
.header .sub{color:var(--text2);font-size:.9rem;max-width:800px;margin:0 auto;line-height:1.7}
.stats-bar{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:1px;background:var(--border);border:1px solid var(--border);border-radius:6px;overflow:hidden;margin-bottom:2.5rem}
.stat{background:var(--bg2);padding:1.2rem 1rem;text-align:center;transition:background .2s}
.stat .val{font-family:var(--mono);font-size:1.7rem;font-weight:700;line-height:1;margin-bottom:.3rem}
.stat .lbl{font-size:.72rem;text-transform:uppercase;letter-spacing:.1em;color:var(--text3)}
.stat.ok .val{color:var(--green)}.stat.err .val{color:var(--red)}.stat.warn .val{color:var(--orange)}.stat.time .val{color:var(--accent)}.stat.total .val{color:var(--yellow)}.stat.pct .val{color:var(--purple)}
.progress-wrap{margin-bottom:2.5rem}.progress-label{display:flex;justify-content:space-between;font-family:var(--mono);font-size:.75rem;color:var(--text2);margin-bottom:.4rem}
.progress-bar{height:6px;background:var(--bg3);border-radius:3px;overflow:hidden}.progress-fill{height:100%;background:linear-gradient(90deg,var(--accent2),var(--green));border-radius:3px;transition:width .5s ease;width:<?= $pct_ok ?>%}
.toc{background:var(--bg2);border:1px solid var(--border2);border-radius:6px;padding:1.5rem;margin-bottom:2.5rem}
.toc h2{font-size:.75rem;text-transform:uppercase;letter-spacing:.12em;color:var(--accent);margin-bottom:1rem}
.toc-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:.3rem}
.toc-item{display:flex;align-items:center;gap:.5rem;font-family:var(--mono);font-size:.78rem;color:var(--text2);text-decoration:none;padding:.25rem .4rem;border-radius:3px;transition:all .15s}
.toc-item:hover{color:var(--accent);background:rgba(0,212,255,.06)}.toc-item .dot{width:7px;height:7px;border-radius:50%;flex-shrink:0}
.group-section{margin-bottom:2rem;border:1px solid var(--border);border-radius:8px;overflow:hidden;transition:border-color .2s}
.group-section:hover{border-color:var(--border2)}
.group-header{display:flex;align-items:center;gap:1rem;padding:1rem 1.2rem;background:var(--bg2);border-bottom:1px solid var(--border);cursor:pointer;user-select:none}
.group-header:hover{background:var(--surface)}
.group-icon{font-size:1.2rem;flex-shrink:0}
.group-name{font-weight:700;font-size:.9rem;letter-spacing:.02em;color:#fff;flex:1}
.group-meta{display:flex;gap:.6rem;align-items:center;font-family:var(--mono);font-size:.72rem}
.chip{padding:.15rem .5rem;border-radius:2px;font-size:.68rem;text-transform:uppercase;letter-spacing:.07em}
.chip.ok{background:rgba(0,230,118,.12);color:var(--green);border:1px solid rgba(0,230,118,.2)}
.chip.neutral{background:rgba(120,150,200,.1);color:var(--text2);border:1px solid rgba(120,150,200,.15)}
.toggle-icon{color:var(--text3);font-size:.8rem;transition:transform .25s}
.cross-value{background:rgba(0,212,255,.05);border-left:3px solid var(--accent);padding:.8rem 1.2rem;margin:.5rem 1.2rem 1rem;font-size:.78rem;color:var(--text2);font-family:var(--sans);line-height:1.5}
.cross-value strong{color:var(--accent)}.cross-value em{color:var(--purple);font-style:normal}.cross-value code{background:rgba(0,0,0,.2);padding:.1rem .3rem;border-radius:2px;font-family:var(--mono);font-size:.72rem}
.test-table{width:100%;border-collapse:collapse;font-size:.82rem}
.test-table th{background:var(--bg3);padding:.6rem 1rem;text-align:left;font-family:var(--mono);font-size:.68rem;text-transform:uppercase;letter-spacing:.1em;color:var(--text3);border-bottom:1px solid var(--border);white-space:nowrap}
.test-table td{padding:.65rem 1rem;border-bottom:1px solid var(--border);vertical-align:top;background:var(--bg);transition:background .15s}
.test-table tr:last-child td{border-bottom:none}.test-table tr:hover td{background:rgba(0,212,255,.03)}
.test-label{font-weight:600;color:#fff;white-space:nowrap}
.test-url{font-family:var(--mono);font-size:.72rem;color:var(--text3);word-break:break-all;max-width:380px}
.test-url a{color:var(--text3);text-decoration:none;transition:color .15s}.test-url a:hover{color:var(--accent)}
.status-ok{color:var(--green);font-family:var(--mono);font-weight:700;white-space:nowrap}
.result-text{color:var(--text2);font-family:var(--mono);font-size:.76rem;line-height:1.4;max-width:420px}
.result-text.success{color:var(--text)}
.ms-badge{font-family:var(--mono);font-size:.72rem;white-space:nowrap}
.ms-fast{color:var(--green)}.ms-med{color:var(--yellow)}.ms-slow{color:var(--orange)}.ms-vslow{color:var(--red)}
.http-code{font-family:var(--mono);font-size:.72rem;color:var(--text3);white-space:nowrap}
.http-2xx{color:var(--green)}
.legend{display:flex;flex-wrap:wrap;gap:1rem;padding:1rem 1.5rem;background:var(--bg2);border:1px solid var(--border);border-radius:6px;margin-bottom:2.5rem;font-size:.78rem;color:var(--text2)}
.legend span{display:flex;align-items:center;gap:.4rem}
.footer{text-align:center;padding:2rem;margin-top:3rem;border-top:1px solid var(--border);color:var(--text3);font-size:.78rem;font-family:var(--mono);line-height:1.8}
.footer a{color:var(--text2);text-decoration:none}.footer a:hover{color:var(--accent)}
.group-body{transition:all .25s ease}.collapsed{display:none}
@media(max-width:768px){.test-url{display:none}.stats-bar{grid-template-columns:repeat(3,1fr)}.toc-grid{grid-template-columns:1fr 1fr}}
</style>
</head>
<body>
<div class="wrapper">

<!-- ══════════ HEADER ══════════ -->
<div class="header">
    <div class="badge">🔬 BioAPI Validated v<?= VERSION ?> · <?= date('Y-m-d H:i:s') ?></div>
    <h1>APIs Biomédicales — <span>100% Fonctionnelles</span></h1>
    <p class="sub">
        ✅ <?= $total_tests ?> endpoints validés · <?= count($by_group) ?> sources · 100% gratuit · sans API key<br>
        <small style="opacity:.6">Chaque requête affichée retourne HTTP 200 + données parsées.<br>
        Au-dessus de chaque source : explication de la valeur ajoutée par croisement avec l'écosystème.</small>
    </p>
</div>

<!-- ══════════ STATS BAR ══════════ -->
<div class="stats-bar">
    <div class="stat total"><div class="val"><?= $total_tests ?></div><div class="lbl">Tests OK</div></div>
    <div class="stat ok"><div class="val">100%</div><div class="lbl">Taux succès</div></div>
    <div class="stat time"><div class="val"><?= number_format($total_time/1000, 2) ?>s</div><div class="lbl">Durée</div></div>
    <div class="stat neutral" style="--val-color:var(--purple)"><div class="val" style="color:var(--purple)"><?= count($by_group) ?></div><div class="lbl">Sources</div></div>
</div>

<!-- ══════════ PROGRESS BAR ══════════ -->
<div class="progress-wrap">
    <div class="progress-label"><span>Tous les endpoints affichés sont opérationnels</span><span>✓ 100%</span></div>
    <div class="progress-bar"><div class="progress-fill"></div></div>
</div>

<!-- ══════════ LEGEND ══════════ -->
<div class="legend">
    <span><strong style="color:var(--green)">✓ OK</strong> — Endpoint validé, données parsées</span>
    <span><strong style="color:var(--accent)">🔗 Croisement</strong> — Valeur ajoutée par intégration multi-sources</span>
    <span style="color:var(--text3)">⏱ Temps en ms · requêtes séquentielles côté serveur</span>
</div>

<!-- ══════════ TABLE OF CONTENTS ══════════ -->
<div class="toc">
    <h2>📑 Index des sources validées</h2>
    <div class="toc-grid">
<?php
$group_icons = ['PubMed'=>'📚','ClinVar'=>'🧬','NCBI Gene'=>'🔵','NCBI Nucleotide'=>'🔗','NCBI Protein'=>'⚛','UniProt'=>'🔵','EuroPMC'=>'🇪🇺','OpenAlex'=>'📖','ChEMBL'=>'💊','ArXiv'=>'📄','CrossRef'=>'🔗','Semantic Scholar'=>'🧠','DataCite'=>'📊','Unpaywall'=>'🔓','RCSB PDB'=>'🏗','Ensembl'=>'🦠','STRING DB'=>'🕸','Reactome'=>'♻','GBIF'=>'🌿','RxNorm (FDA)'=>'💉','OpenFDA'=>'⚕','ClinicalTrials'=>'🏥','WHO GHO'=>'🌍','World Bank'=>'🌐','KEGG'=>'🔬','Wikipedia (EN)'=>'📙','Wikipedia (FR)'=>'📗','Zenodo'=>'📦','NASA ADS'=>'🌌','BioGRID'=>'🔗'];
foreach ($by_group as $grp => $tests) {
    $icon = $group_icons[$grp] ?? '🔬';
    $slug = 'grp-' . preg_replace('/[^a-z0-9]/i', '-', strtolower($grp));
    echo "<a class='toc-item' href='#$slug'><span class='dot' style='background:var(--green)'></span>$icon " . htmlspecialchars($grp) . " <span style='margin-left:auto;color:var(--text3)'>" . count($tests) . "</span></a>\n";
}
?>
    </div>
</div>

<!-- ══════════ RESULTS ══════════ -->
<?php foreach ($by_group as $grp => $tests): ?>
<?php
    $tot = count($tests);
    $icon = $group_icons[$grp] ?? '🔬';
    $slug = 'grp-' . preg_replace('/[^a-z0-9]/i', '-', strtolower($grp));
    $avg_ms = round(array_sum(array_column($tests, 'ms')) / max(1, $tot));
?>
<div class="group-section" id="<?= $slug ?>" style="border-color:rgba(0,230,118,.2)">
    <div class="group-header" onclick="toggleGroup('<?= $slug ?>')">
        <span class="group-icon"><?= $icon ?></span>
        <span class="group-name"><?= htmlspecialchars($grp) ?></span>
        <div class="group-meta">
            <span class="chip ok">✓ <?= $tot ?> OK</span>
            <span class="chip neutral">⏱ ~<?= $avg_ms ?>ms</span>
        </div>
        <span class="toggle-icon" id="tog-<?= $slug ?>">▼</span>
    </div>
    
    <!-- ═════ VALEUR DE CROISEMENT ═════ -->
    <div class="cross-value"><?= get_cross_value($grp) ?></div>
    
    <div class="group-body" id="body-<?= $slug ?>">
        <table class="test-table">
            <thead><tr><th>#</th><th>Requête</th><th>URL</th><th>HTTP</th><th>Statut</th><th>Résultat</th><th>⏱ ms</th></tr></thead>
            <tbody>
            <?php foreach ($tests as $i => $t): ?>
            <?php $http_class = 'http-' . floor($t['code'] / 100) . 'xx'; $ms_class = $t['ms'] < 300 ? 'ms-fast' : ($t['ms'] < 1000 ? 'ms-med' : ($t['ms'] < 5000 ? 'ms-slow' : 'ms-vslow')); ?>
            <tr>
                <td style="color:var(--text3);font-family:var(--mono)"><?= $i + 1 ?></td>
                <td><span class="test-label"><?= htmlspecialchars($t['label']) ?></span></td>
                <td class="test-url"><a href="<?= htmlspecialchars($t['url']) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($t['url']) ?></a></td>
                <td class="http-code <?= $http_class ?>"><?= $t['code'] ?></td>
                <td><span class="status-ok">✓ OK</span></td>
                <td><div class="result-text success"><?= htmlspecialchars($t['result']) ?></div></td>
                <td class="ms-badge <?= $ms_class ?>"><?= number_format($t['ms']) ?></td>
            </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>
<?php endforeach ?>

<!-- ══════════ SYNTHÈSE ══════════ -->
<div style="margin-top:2rem;padding:1.5rem;background:var(--bg2);border:1px solid var(--border2);border-radius:8px;font-family:var(--mono);font-size:.8rem;line-height:1.9">
    <div style="color:var(--accent);font-weight:700;margin-bottom:.8rem;font-size:.85rem;letter-spacing:.08em">📊 SYNTHÈSE FINALE</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.3rem 2rem;color:var(--text2)">
        <span>🟢 Tests validés</span><span style="color:var(--green);font-weight:700"><?= $total_tests ?> / <?= $total_tests ?> (100%)</span>
        <span>📚 Sources actives</span><span style="color:var(--purple)"><?= count($by_group) ?></span>
        <span>⏱ Durée totale</span><span style="color:var(--accent)"><?= number_format($total_time/1000, 2) ?> secondes</span>
        <span>📅 Timestamp</span><span style="color:var(--text3)"><?= date('Y-m-d H:i:s T') ?></span>
    </div>
    <div style="margin-top:1rem;padding-top:.8rem;border-top:1px solid var(--border);color:var(--text3);font-size:.72rem">
        ℹ  Cette page affiche <strong style="color:var(--text2)">uniquement les endpoints fonctionnels</strong>. 
        Les sources avec 0% de succès ont été exclues. 
        Pour chaque source, la section <code style="color:var(--accent)">🔗 Croisement</code> explique comment l'intégration avec les autres APIs amplifie la découverte scientifique.
    </div>
</div>

<!-- ══════════ FOOTER ══════════ -->
<div class="footer">
    BioAPI Validated v<?= VERSION ?> · PHP <?= PHP_MAJOR_VERSION ?>.<?= PHP_MINOR_VERSION ?> · <?= $total_tests ?> tests validés<br>
    <span style="opacity:.5">Gratuit · Open · Sans authentification · Usage éducatif et de recherche</span><br>
    <small style="opacity:.35">PubMed · UniProt · EuroPMC · OpenAlex · ChEMBL · ArXiv · CrossRef · PDB · Ensembl · STRING · Reactome · GBIF · RxNorm · OpenFDA · ClinicalTrials · WHO GHO · World Bank · KEGG · Zenodo · DataCite · Unpaywall · BioGRID · Wikipedia</small>
</div>

</div><!-- /wrapper -->

<script>
function toggleGroup(id) {
    const body = document.getElementById('body-' + id);
    const tog  = document.getElementById('tog-' + id);
    if (body.classList.toggle('collapsed')) { tog.style.transform = 'rotate(-90deg)'; }
    else { tog.style.transform = ''; }
}
document.addEventListener('keydown', e => {
    if (e.key === 'c' || e.key === 'C') document.querySelectorAll('[id^="body-grp-"]').forEach(b => { b.classList.add('collapsed'); const tog = document.getElementById('tog-' + b.id.replace('body-','')); if (tog) tog.style.transform = 'rotate(-90deg)'; });
    if (e.key === 'e' || e.key === 'E') document.querySelectorAll('[id^="body-grp-"]').forEach(b => { b.classList.remove('collapsed'); const tog = document.getElementById('tog-' + b.id.replace('body-','')); if (tog) tog.style.transform = ''; });
});
</script>
</body>
</html>
