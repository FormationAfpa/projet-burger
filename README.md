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
