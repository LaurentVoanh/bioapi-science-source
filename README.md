# BioAPI Research Portal
to install add you api key mistral in config.php , you can use 3 differents api key.
Try V2 for better result

## Installation
1. Uploadez install.php sur votre serveur PHP 8.3+
2. Visitez https://votre-domaine.fr/install.php
3. Le script genere automatiquement les fichiers

## Utilisation
1. Cliquez sur "Lancer une recherche"
2. Le systeme execute en AJAX le workflow complet
3. L article apparait dans la colonne droite
4. Cliquez pour voir l article complet en popup

## Architecture
- config.php : Configuration (DB, APIs, Mistral)
- database.sqlite : Base de donnees SQLite
- api.php : Backend AJAX
- index.php : Frontend
- SOURCE/ : Export JSON
- logs/ : Debug logs

## APIs Supportees (36)
PubMed, ClinVar, NCBI Gene, UniProt, EuroPMC, OpenAlex, ChEMBL, ArXiv, CrossRef, DataCite, Unpaywall, RCSB PDB, Ensembl, STRING DB, Reactome, GBIF, RxNorm, OpenFDA, ClinicalTrials, WHO GHO, World Bank, KEGG, Wikipedia, Zenodo, NASA ADS, BioGRID, PubChem, Wikidata, Hugging Face, INSPIRE-HEP, Papers with Code, Semantic Scholar.

## Mistral AI
- Endpoint: https://api.mistral.ai/v1/chat/completions
- Modele: mistral-large-latest
- 3 cles configurées dans config.php

## Securite
- Base SQLite protegee par .htaccess
- Logs non accessibles publiquement
- Cles API cote serveur uniquement

## Requirements
- PHP 8.3+
- Extensions: pdo_sqlite, curl, json, mbstring
- Aucune dependance Composer

Projet educatif - Usage responsable recommande
