
# How to install

```
# download the source code
git clone https://github.com/scroach/rezeptdb

# install dependencies
composer install

# copy the env file and update database settings
cp .env.dist .env && vim .env

# run database migrations
php bin/console doctrine:migrations:migrate
```

# Docs

* https://symfony.com/doc/current/index.html
* https://symfony.com/doc/master/bundles/DoctrineMigrationsBundle/index.html
* https://www.doctrine-project.org/projects/migrations.html
* https://semantic-ui.com/