# Projet-o-transport-back

Pour utiliser le repo :
 - le cloner
 - ouvrir un terminal à la racine du projet
 - tapper dans la terminal la commande ``composer install``
 - configurer la base de données : dans Adminer, créer une base de données otransport puis créer un user nommé otransport avec mdp: otransport. 
**On ne touche pas au dossier vendor et au dossier var !**



Dépendances installées:
- profiler
- annotations
- asset
- --dev maker
- ORM pack
- debug bundle (pour des dump() plus lisibles)
- security-bundle
- validator (pour valider les données de formulaires)
- orm fixtures
- faker/php
- nelmio/cors-bundle
- lexik/jwt-authentication-bundle


## Connexion de la base de données (Mise en prod)

Dans un fichier .env.local (à créer) ajouter cette ligne pour donner l'accès à la bdd

```
DATABASE_URL="mysql://otransport:otransport@127.0.0.1:3306/otransport?serverVersion=10.3.32-MariaDB&charset=utf8mb4"
```

Créer la table et le user à utiliser
```
CREATE DATABASE `otransport`;
CREATE USER 'otransport'@'localhost' IDENTIFIED BY PASSWORD '*720E1FE7BDA1925412CF807F4ADAE0E9B7992811';
GRANT ALL PRIVILEGES ON `otransport`.* TO 'otransport'@'localhost' WITH GRANT OPTION;
```
