
# 🔬 BioAPI Test Suite

Suite de tests exhaustive pour valider la disponibilité et l'interopérabilité de **31 APIs biomédicales & scientifiques publiques**. Aucun token requis.

[![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=flat&logo=php)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![APIs](https://img.shields.io/badge/APIs-31+-green.svg)](#sources-couvertes)

## 🎯 Objectif

Permettre aux chercheurs, développeurs et data scientists de :
1. **Valider** rapidement l'état de santé des endpoints publics.
2. **Comprendre** la valeur ajoutée du croisement entre sources (gène → protéine → maladie → publication).
3. **Identifier** des endpoints fiables pour des pipelines de recherche automatisés.

## ✨ Fonctionnalités

- **78+ endpoints** testés automatiquement.
- **Rapport HTML interactif** (dark mode, responsive, collapsible).
- **Analyse de croisement** : explication de la valeur scientifique par source.
- **Zéro authentification** : 100% des requêtes sont publiques.
- **Filtrage intelligent** : mise en avant des requêtes fonctionnelles (HTTP 200 + données parsées).

## 🚀 Usage

### Prérequis
- PHP 8.0+
- Extension `curl` activée
- Accès internet sortant

### Exécution
```bash
# En ligne de commande
php index.php

# Ou via serveur web
# Ouvrir http://localhost/index.php dans votre navigateur si vous êtes en local et https://votresite/votrerepertoire/index.php
```

## 📊 Sources Couvertes

| Catégorie | APIs |
|-----------|------|
| **Génomique** | PubMed, ClinVar, NCBI Gene, UniProt, Ensembl, NCBI Nucleotide/Protein |
| **Chimie** | ChEMBL, PubChem, ChEBI, KEGG, RxNorm |
| **Publications** | EuroPMC, OpenAlex, CrossRef, Semantic Scholar, ArXiv, INSPIRE-HEP |
| **Données** | Zenodo, DataCite, BioGRID, STRING, Reactome, PDB |
| **Santé Globale** | WHO GHO, World Bank, OpenFDA, ClinicalTrials, GBIF |
| **Généraliste** | Wikipedia, Wikidata, Hugging Face, NASA ADS/Exoplanet |

## 📈 Exemple de Rapport

Le script génère un tableau de bord HTML contenant :
- **Stats bar** : Taux de succès, durée totale, sources actives.
- **Progress bar** : Visualisation du taux de disponibilité global.
- **Groupes collapsibles** : Détails par source avec valeur de croisement.
- **Tableau de tests** : URL, statut HTTP, temps de réponse, résultat parsé.

## ⚠️ Limites & Disclaimer

- **Rate Limiting** : Certaines APIs (NCBI, Semantic Scholar) peuvent retourner des erreurs 429 lors d'exécutions fréquentes.
- **Stabilité** : Les endpoints publics peuvent changer sans préavis. Ce tool sert à *monitorer* ces changements.
- **Usage** : Éducatif et recherche. Non conçu pour un usage production critique.

- 🔬 Test de Cas Réel : "Projet Antiviral Universel"
- 
Voici comment le système utilise obligatoirement tous les sites pour un seul projet :

Aspiration (NCBI/Nucleotide) : L'IA détecte une nouvelle séquence de virus émergent via GBIF (zones de biodiversité à risque).

Calcul Protéique (UniProt/PDB) : Elle traduit la séquence en protéine et cherche sa structure 3D.

Criblage (ChEMBL/PubChem) : Elle cherche des molécules existantes capables de bloquer ce virus.

Vérification Littéraire (PubMed/ArXiv) : Elle vérifie si des chercheurs ont déjà échoué sur cette piste pour ne pas perdre de temps.

Corrélation de Voies (KEGG/Reactome) : Elle analyse l'impact du virus sur le métabolisme humain.

Sécurité (OpenFDA/RxNorm) : Elle élimine les molécules trop toxiques.

Plan Clinique (ClinicalTrials) : Elle rédige le protocole d'essai clinique idéal.

Économie (World Bank) : Elle calcule le prix de revient pour une distribution mondiale.

🚀 Amélioration par les Tokens IA (Multi-couches)
Pour que ce travail soit de "très haute qualité", l'IA utilise les tokens de trois manières :

Tokens de Raisonnement : Pour vérifier la logique interne (ex: "Si la protéine est membranaire, le ligand doit être lipophile").

Tokens Créatifs : Pour rédiger une littérature scientifique fluide, pédagogique mais rigoureuse (niveau "Nature" ou "Science").

Tokens de Code : Pour générer les scripts de calcul (BioPython, R) qui traitent les fichiers ADN.

Conclusion : En forçant chaque site à être un paramètre d'une seule équation, nous créons une IA Spécialiste qui ne se contente pas de répondre à des questions, mais qui génère de la science de manière industrielle. C'est le passage de la recherche artisanale à l'usine à découvertes.


## 📄 Licence

MIT License — Libre utilisation pour la recherche et l'éducation.

---
*Développé pour faciliter la découverte scientifique interopérable.*
```
