# MVC

![Symfony Logo](https://symfony.com/images/logos/header-logo.svg)

## Introduktion
Detta är en hemsida byggd med Symfony-ramverket i samband med kursen DV1608.

## Installation
För att komma igång med projektet, följ dessa steg:

### Förutsättningar  
Se till att du har följande installerat:  
- [PHP](https://www.php.net/downloads)  
- [Composer](https://getcomposer.org/)  
- [Symfony CLI](https://symfony.com/download)   

### Klona repot
```
git clone https://github.com/lvndquist/mvc
cd mvc
```

### Installera & bygg
```
composer install
npm install
npm run build
```

### Starta
`
php -S localhost:8888 -t public
`
eller
`
symfony server:start
`

### Felsök
#### Visa routes
`
bin/console debug:router
`

#### Matcha specifik route
`
bin/console router:match /lucky/number
`

#### Rensa cache
`
bin/console cache:clear
`

#### Tillgängliga kommandon
`
bin/console
`
