
# How to install

```
# download the source code
git clone https://github.com/scroach/rezeptdb

# install dependencies
composer install

# update database settings
vim .env

# create database if it does not exist
php bin/console doctrine:database:create

# run database migrations
php bin/console doctrine:migrations:migrate
```

# Docs

* https://symfony.com/doc/current/index.html
* https://symfony.com/doc/master/bundles/DoctrineMigrationsBundle/index.html
* https://www.doctrine-project.org/projects/migrations.html
* https://semantic-ui.com/
