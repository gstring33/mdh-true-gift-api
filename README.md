## Project

The aim of the api is to provide the payload for the true-gift client-side application.

### Purpose
- Create a secured stateless API implementing JWT (JSON Web Token)

### Challenge faced
- Algorithm: a user cannot at any time be in a position to select himself
- Serialize data based on one-to-many entities relations using serializing context
- Secure exchange between the client-side application and the api with correct response status and CORS headers

### Next features...
- Implement a refresh token logic
- Use Docker with Symfony
- Test the api

### Technologies
- [Symfony 6](https://symfony.com/)
- [Doctrine](https://www.doctrine-project.org/projects/doctrine-orm/en/2.13/tutorials/getting-started.html)
- [Lexik JWT Authentication Bundle](https://github.com/lexik/LexikJWTAuthenticationBundle)
- [JMS Serializer Bundle](https://github.com/schmittjoh/JMSSerializerBundle)
- [Nelmio Cors Bundle](https://github.com/nelmio/NelmioCorsBundle)
- [FOSRest Bundle](https://github.com/FriendsOfSymfony/FOSRestBundle)
- [Phpunit](https://phpunit.readthedocs.io/en/9.5/)

## Requirements 

* PHP 8.0.2 or higher;
* composer
* and the [usual Symfony application requirements](https://symfony.com/doc/6.0/setup.html#technical-requirements).

## Installation

Install dependencies
``` bash
composer install
```
Create Database 
``` bash
php bin/console doctrine:database:create
```
Run Migrations
``` bash
php bin/console doctrine:migrations:migrate
```
Generate JWT SSL Keys
``` bash
php bin/console lexik:jwt:generate-keypair
```
Start the server
``` bash
symfony server:start
```
