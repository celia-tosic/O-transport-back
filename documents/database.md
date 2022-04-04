# ORGANISATION DE LA BDD

## Connexion à la bdd

Dans un fichier .env.local (à créer) ajouter cette ligne pour donner l'accès à la bdd
```
DATABASE_URL="mysql://otransport:otransport@127.0.0.1:3306/otransport?serverVersion=10.3.32-MariaDB&charset=utf8mb4"
```

CREATE USER 'otransport'@'localhost' IDENTIFIED BY PASSWORD '*720E1FE7BDA1925412CF807F4ADAE0E9B7992811';
GRANT ALL PRIVILEGES ON `otransport`.* TO 'otransport'@'localhost' WITH GRANT OPTION;