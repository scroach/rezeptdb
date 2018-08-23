
![Travis (.org)](https://img.shields.io/travis/scroach/rezeptdb.svg)

[![Coverage Status](https://coveralls.io/repos/github/scroach/rezeptdb/badge.svg)](https://coveralls.io/github/scroach/rezeptdb)

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

# import doctrine test fixtures into database 
php bin/console doctrine:fixtures:load

```

# Docs

* https://symfony.com/doc/current/index.html
* https://symfony.com/doc/master/bundles/DoctrineMigrationsBundle/index.html
* https://www.doctrine-project.org/projects/migrations.html
* https://semantic-ui.com/
