# Projet-o-transport-back

Pour utiliser le repo :
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
