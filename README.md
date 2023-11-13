# p7_OC-API Bilemo
Créez un web service exposant une API

## Environnement de développement
* Symfony 6.3
* Composer 2.6
* Bootstrap 4.0.0
* jQuery 3.2.1
* PHPUnit 9.5
* WampServer 3.2.6
    * Apache 2.4.51
    * PHP 8.1.4
    * MySQL 5.7.36
 
## Respect des bonnes pratique
Utilisation de [PHP-CS-Fixer](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer)

Codacy [![Codacy Badge](https://app.codacy.com/project/badge/Grade/2d1b99d36e7a4cc089434cfe576b6c75)](https://app.codacy.com/gh/MaximeHoup/P6_OC-SnowTricks/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)

## Installation du projet
1.Télécharger ou cloner le repository suivant:
```
https://github.com/MaximeHoup/p7api.git
```

2.Configurez vos variables d'environnement (connexion à la base de données, serveur SMTP...) à l'aide du fichier
```.env```

3.Téléchargez et installez les dépendances du projet avec [Composer](https://getcomposer.org/download/) :
```
    composer install
```

4.Créez la base de données grace à la commande:
```
    php bin/console doctrine:database:create
```

5.Créez les différentes tables de la base de données avec la commande :
```
    php bin/console doctrine:migrations:migrate
```

6.(Optionnel) Installer les fixtures pour avoir une démo avec des données fictives :
```
    php bin/console doctrine:fixtures:load
```
