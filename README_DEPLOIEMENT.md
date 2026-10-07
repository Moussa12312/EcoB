# 🚀 Guide de Déploiement et Publication - ECOB University

Ce projet est prêt pour une mise en production immédiate.

---

## 📦 Fichiers de Déploiement Inclus

1. **`ecob_university_database.sql`** :
   - Sauvegarde complète et réelle de la base de données (utilisateurs réels, rôles admin/marketeur, filières, sliders, articles/actualités, paramètres du site).
2. **`Siteweb_production.zip`** *(dans `c:\wamp64\www\`)* :
   - Archive complète prête à être envoyée sur un serveur web ou cPanel.
3. **Dépôt Git initialisé** :
   - Historique versionné avec tous les fichiers de l'application.

---

## 🌐 Option 1 : Déploiement sur Hébergement Web (cPanel, Hostinger, OVH, LWS...)

1. **Uploader les fichiers** :
   - Décompressez l'archive `Siteweb_production.zip` dans le dossier racine de votre hébergement (`public_html` ou `www`).
2. **Créer la Base de Données** :
   - Dans votre panneau d'hébergement (cPanel / phpMyAdmin), créez une base de données MySQL (ex: `u123456789_ecob`).
   - Créez un utilisateur MySQL et donnez-lui tous les privilèges sur cette base.
   - Cliquez sur **Importer** et sélectionnez le fichier **`ecob_university_database.sql`**.
3. **Configurer les accès dans `config/db.php`** :
   - Modifiez les valeurs ou configurez les variables d'environnement :
     ```php
     define('DB_HOST', 'localhost'); // ou adresse fournie par l'hébergeur
     define('DB_NAME', 'nom_de_votre_base');
     define('DB_USER', 'nom_utilisateur_mysql');
     define('DB_PASS', 'mot_de_passe_mysql');
     ```
4. **Configuration SMTP pour les e-mails** :
   - Connectez-vous à l'espace administration (`/admin/login.php`).
   - Rendez-vous dans **Paramètres** pour ajuster l'e-mail de réception et le serveur SMTP (Gmail, Hostinger, OVH, etc.).

---

## 🐙 Option 2 : Publication sur GitHub / GitLab

Si vous souhaitez publier ce projet sur votre compte GitHub (`moucoulibaby111111@gmail.com`) :

1. Créez un nouveau dépôt vide sur votre compte GitHub (ex: `ecob-university`).
2. Dans le terminal, exécutez simplement :
   ```bash
   git remote add origin https://github.com/VOTRE_PSEUDO/ecob-university.git
   git branch -M main
   git push -u origin main
   ```

---

## 🔒 Comptes d'accès inclus

- **Super Administrateur** :
  - Identifiant : `admin@ecob.edu`
  - Rôle : Accès complet (Filières, Équipe, Partenaires, Paramètres, Sécurité, Gestion des utilisateurs et marketeurs).
- **Espace Marketeur** :
  - Créable directement depuis l'espace Admin avec restrictions professionnelles sur les données sensibles.
