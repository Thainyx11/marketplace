# Marketplace Pop Culture

Plateforme de vente entre particuliers d'objets pop culture (cartes à collectionner, jeux vidéo rétro, figurines, manga, goodies), réalisée dans le cadre d'un Travail de Fin d'Études — voir le [cahier des charges](#cahier-des-charges) d'origine pour le périmètre complet.

## Stack

| Domaine | Techno |
|---|---|
| Framework | Laravel 13 (PHP 8.5), Eloquent ORM |
| Frontend | Blade + Livewire 3 / Volt, Tailwind CSS 3 |
| Rôles & permissions | Spatie Laravel Permission |
| Paiement | Stripe Checkout Sessions + webhook |
| Temps réel | Laravel Reverb (auto-hébergé, remplace Pusher) |
| PDF | barryvdh/laravel-dompdf |
| Base de données | SQLite en local (zéro configuration) / MySQL en production |

## Installation

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
```

`DB_CONNECTION=sqlite` fonctionne directement sans rien configurer (le fichier `database/database.sqlite` est créé automatiquement). Pour passer en MySQL/MariaDB (recommandé en production, comme demandé au cahier des charges), voir les lignes commentées dans `.env.example`.

## Lancer le projet en local

Trois processus tournent en parallèle :

```bash
php artisan serve          # http://localhost:8000
npm run dev                # Vite (hot reload CSS/JS)
php artisan reverb:start   # WebSocket pour la messagerie temps réel
```

> Si `ws://localhost:8080` échoue dans votre navigateur, essayez `REVERB_HOST=127.0.0.1` dans `.env` (certains environnements résolvent mal `localhost` en WebSocket).

### Paiement Stripe

Le paiement fonctionne en mode dégradé sans clé configurée : le checkout affiche un message clair plutôt que de planter. Pour l'activer :

1. Créez un compte Stripe gratuit et récupérez vos clés de test sur https://dashboard.stripe.com/test/apikeys
2. Renseignez `STRIPE_KEY` et `STRIPE_SECRET` dans `.env`
3. Pour recevoir le webhook en local, installez le [Stripe CLI](https://stripe.com/docs/stripe-cli) puis :
   ```bash
   stripe listen --forward-to localhost:8000/webhook/stripe
   ```
   Copiez le `whsec_...` affiché dans `STRIPE_WEBHOOK_SECRET`.
4. Testez avec la carte `4242 4242 4242 4242`, une date future, un CVC quelconque.

## Comptes de test (créés par le seeder)

| Rôle | Email | Mot de passe |
|---|---|---|
| Administrateur | `admin@marketplace.test` | `password` |
| Vendeur approuvé | `vendeur@marketplace.test` | `password` |
| Vendeur en attente | `vendeur.pending@marketplace.test` | `password` |
| Acheteur | `acheteur@marketplace.test` | `password` |

`php artisan migrate:fresh --seed` régénère une base propre avec ces comptes, 5 catégories, 12 produits, et une commande livrée avec avis pour avoir tout de suite des données à l'écran.

## Tests

```bash
php artisan test           # 33 tests (auth, autorisation par rôle, profil...)
./vendor/bin/pint --test   # style de code
npm run build               # build production, doit sortir sans erreur ni warning
```

## Périmètre fonctionnel réalisé

- **Comptes** : inscription acheteur (accès immédiat) / vendeur (validation admin requise), connexion, mot de passe oublié, profil (avatar, bio, adresse, infos boutique)
- **Catalogue** : accueil, liste avec filtres (catégorie, marque, état, rareté, prix) + tri + recherche, fiche produit, annuaire et profils vendeurs
- **Panier & commande** : panier session (invité) ou base de données (connecté, fusionné à la connexion), checkout Stripe, codes promo, facture PDF, suivi de commande
- **Messagerie** : discussion par produit entre acheteur et vendeur, temps réel (Reverb), indicateur lu/non lu, signalement
- **Avis** : notation 1-5 après livraison, affichée sur la fiche produit et le profil vendeur
- **Espace vendeur** : CRUD produits avec upload d'images, commandes reçues avec pipeline de statut, statistiques mensuelles (graphique)
- **Back office admin** : statistiques globales, gestion utilisateurs/vendeurs (validation, suspension), catégories, modération produits/avis, litiges de commande, messages signalés, codes promo, paramètres (taux de commission, mentions légales)

### Décisions techniques notables

- **SQLite en local** : le MySQL fourni par l'environnement de développement initial s'est révélé être une installation incomplète (dossier `lib/plugin` absent, `mysqld` ne démarrait pas). SQLite évite cette dépendance en local ; les migrations n'utilisent aucune syntaxe spécifique à un moteur, donc le passage à MySQL en production ne demande qu'un changement de `.env`.
- **Reverb plutôt que Pusher** : le cahier des charges autorise explicitement cette alternative auto-hébergée (section 7.1), ce qui évite de dépendre d'un compte tiers.
- **Pas de coordonnées bancaires réelles stockées** : le champ « coordonnées de versement » du profil vendeur est un simple mémo texte, avec un avertissement à l'écran. Un vrai système de versement nécessiterait Stripe Connect, hors périmètre de cette itération.
- **Statut de commande à deux niveaux** : `orders.status` (vu par l'acheteur) et `order_items.status` (piloté par chaque vendeur). Un même panier pouvant contenir des produits de plusieurs vendeurs indépendants, le statut global ne peut avancer que lorsque tous les articles ont atteint l'étape correspondante (`Order::recomputeStatus()`).

## Non couvert dans cette itération

- Déploiement réel (hébergement o2switch/Forge) — nécessite les accès du commanditaire
- Clés Stripe/Reverb de production — à créer par le commanditaire
- Suite de tests exhaustive et audit de sécurité formel (phase 7 du planning du cahier des charges) — la base d'autorisation par rôle est testée, mais la couverture n'est pas complète
- Schéma MCD/UML graphique pour le dossier de TFE — voir l'ERD texte ci-dessous, à transposer dans un outil de modélisation pour le rendu final

## Modèle de données (ERD simplifié)

```
users ──┬──< products >──── categories (auto-référencée via parent_id)
        │       │
        │       ├──< product_images
        │       ├──< order_items >── orders ──< payments
        │       ├──< reviews (via order_id + product_id)
        │       └──< messages >── message_reports
        │
        ├──< carts ──< cart_items >── products
        └──< orders (buyer_id)

orders ──< order_items >── users (seller_id, denormalisé)
orders ── promo_codes (nullable)
```

Champs clés par table : voir `database/migrations/`. Statuts pipeline (`orders.status`, `order_items.status`) : `en_attente → acceptee → expediee → livree`.

## Manuel utilisateur rapide

**Acheteur** : s'inscrire via « Je suis acheteur » → parcourir `/produits` → ajouter au panier → `/panier` → « Passer commande » → payer via Stripe → suivre dans « Mes commandes », laisser un avis une fois « Livrée ».

**Vendeur** : s'inscrire via « Je suis vendeur » → attendre la validation admin (bannière visible sur le tableau de bord tant que non approuvé) → une fois approuvé, `/vendeur/produits/creer` pour publier, `/vendeur/commandes` pour faire avancer le statut de chaque article vendu.

**Administrateur** : `/admin` centralise tout — valider les vendeurs en attente (`/admin/vendeurs`), modérer produits/avis/messages signalés, gérer les codes promo et le taux de commission (`/admin/parametres`).

## Cahier des charges

Document d'origine fourni par le commanditaire, résumant les sections 1 à 14 (contexte, périmètre fonctionnel, spécifications techniques, sécurité, planning, critères de validation). Non inclus dans ce dépôt.
