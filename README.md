# Projet E-Commerce de Burgers

## Description
Site e-commerce de vente de burgers avec système de personnalisation des produits et recommandations.

## Fonctionnalités
- Catalogue de produits avec catégories
- Système de personnalisation des burgers
  - Choix des sauces
  - Sélection des fromages
  - Ajout de légumes
- Panier d'achat dynamique
- Système de recommandations "Vous pourriez aussi aimer"
- Interface responsive
- Système d'authentification utilisateur

## Technologies Utilisées
- PHP 7.4+
- MySQL
- HTML5/CSS3
- JavaScript/jQuery
- Bootstrap 5.3.0
- PDO pour la connexion base de données

## Installation
1. Cloner le repository
```bash
git clone https://github.com/[votre-username]/projet-burger.git
```

2. Configurer la base de données
- Importer le fichier `sql/burger.sql` dans votre base de données MySQL
- Configurer les informations de connexion dans `db.php`

3. Configuration XAMPP
- Placer le projet dans le dossier `htdocs` de XAMPP
- Démarrer Apache et MySQL
- Accéder au site via `http://localhost/projet-burger`

## Structure du Projet
```
projet-burger/
├── css/
├── images/
├── js/
├── sql/
│   ├── burger.sql
│   └── personnalisation.sql
├── index.php
├── panier.php
├── personnalisation.php
├── recommandations.php
├── db.php
└── README.md
```

## Base de Données
- Tables principales :
  - `items` : Produits
  - `categories` : Catégories de produits
  - `users` : Utilisateurs
  - `orders` : Commandes
  - `sauces` : Options de sauces
  - `fromages` : Options de fromages
  - `legumes` : Options de légumes
  - `personnalisations_panier` : Personnalisations

## Auteur
[Votre Nom]

## License
Ce projet est sous license MIT.
