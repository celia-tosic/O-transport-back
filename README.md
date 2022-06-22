# Le projet O'Transport 

Ce repository contient la partie back-end du projet O'transport réalisé lors du projet de fin de formation chez O'clock. 
O'transport est une application permettant de créer des livraisons et de les attribuer à des chauffeurs dans le cadre d'une société de transport poids lourds. 

Les administrateurs de la société peuvent :
- créer/modifier/supprimer des livraisons et les attribuer aux chauffeurs.
- créer/modifier/supprimer des profils chauffeur.

Les chauffeurs peuvent : 
- voir toutes les livraisons qui leur ont été attribuées, 
- commencer une livraison,
- terminer une livraison. 

Le back a été réalisé par une autre personne et moi-même avec Symfony. 
Le front a été réalisé par 2 autres personnes avec React.
Le front et le back étant séparés, notre but, en back, a été de créer tous les endpoints d'API permettant ansi le transfert d'informations entre le front et le back du projet.  

# Utiliser le repository

 - le cloner
 - ouvrir un terminal à la racine du projet
 - taper dans la terminal la commande ``composer install`` (= installe tous les composants)
 - configurer la base de données : dans Adminer, créer une base de données otransport puis créer un user nommé otransport avec mot de passe: otransport
 - dans le terminal, executer la commande ``bin/console migrations:migrate`` (=créé les tables dans la base de données)
 - puis executer la commande ``bin/console doctrine:fixtures:load`` (= Ajoute des données dans la base de données)
 - Ne pas oublier d'allumer le serveur: dans le terminal, executer la commande ``php -S 0.0.0.0:8080 -t public`` (attention, le port peut être différent si vous utilisez déjà le port 8080)
 
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
- serializer-pack
