# GENESIS-ULTRA v4.0

Plateforme de recherche scientifique automatisée — 36 sources interrogées en parallèle.

## Fichiers

- `index.php` — Interface principale
- `api.php` — Backend (toutes les actions)
- `config.php` — Configuration, sources, fonctions HTTP/Mistral

## Fonctionnement

1. **Saisir une question** (facultatif) ou laisser l'IA choisir
2. **Mistral** sélectionne le sujet et génère des termes adaptés à chaque source
3. **36 sources** sont interrogées avec les bons formats de requête
4. **Mistral Large** synthétise en article ≥ 3000 mots avec liens vers les sources
5. Bouton **🔬 APPROFONDIR** pour une analyse ciblée sur les sources les plus pertinentes

## Sources

Literature: PubMed, EuropePMC, OpenAlex, CrossRef, arXiv, Zenodo, INSPIRE-HEP, DataCite, SemanticScholar
Génomique: UniProt, Ensembl, ClinVar, GEO, ArrayExpress, NCBI_Gene, NCBI_Protein
Chimie: ChEMBL, PubChem, KEGG
Réseaux: StringDB, Reactome, GeneOntology, DisGeNET
Clinique: ClinicalTrials, OpenFDA, RxNorm
Encyclopédies: Wikidata, Wikipedia
IA: HuggingFace, PapersWithCode
Structures: PDB, Unpaywall
Écologie: GBIF
Santé pub: WHOGHO, WorldBank
Physique: NASA_ADS
Interactions: BioGRID

## Hébergement Hostinger

- PHP `file_get_contents` uniquement (pas de cURL)
- SQLite — pas de MySQL requis
- Clés Mistral en rotation automatique
