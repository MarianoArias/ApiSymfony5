# ApiSymfony5


## Requirements 
* PHP >= 7.3
* MySQL >= 8.0
* Composer >= 1.9
* Elasticsearch >= 7.5


## Standards 
* Use PHP-CS-Fixer
* Use 4 spaces tab length


## Installation 
* Clone repository:
```
git clone git clone git@github.com:MarianoArias/ApiSymfony5.git
```

* Install dependencies. Run following command in the project's root folder:
```
composer install
```

* Create **.env.local** and **.env.test.local** and set them up with environment variables. 
Use **.dist** files as examples.

* Create database and schema. Run following commands in the project's root folder:
```
php bin/console doctrine:database:create
php bin/console doctrine:schema:update --force
```

* Setting up or fixing file permissions. Run following command in the project's root folder:
```
sudo chmod -R 777 var/cache/ var/log/
```


## Clear cache 
* Run following command in the project's root folder:
```
php bin/console cache:clear
```


## Test 
* Run following command in the project's root folder:
```
./bin/phpunit
```