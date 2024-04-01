# Chinpoko-API
Projet Symfony de cours de FullStack en M1, cette partie est le back, le front lui est dans le repository Chimpokomon-front

1. Installer les dépendances en utilisant Composer (=> ```composer install``` et ```composer update```)
2. Mettre à jour le .env (surtout le DATABASE_URL pour qu'il soit un lien à votre base de donnée)
3. Initialisation de la base de donnée
     - Création de la database (=> ```php bin/console doctrine:database:create```)
     - Création du schéma de la base de donnée (=> ```php bin/console d:s:u --force```)
     - Remplissage de la base de donnée avec des fixtures (=> ```php bin/console d:f:l``` et faire ```yes```) 
5. Lancement du serveur avec (=> ```symfony server:start```)
