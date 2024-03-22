Console Component
=================

# Descrição do projeto

Este projeto trata-se de uma pequena aplicação para ler um ficheiro XML de um site de notícias. Com a aplicação, será extraido deste ficheiro xml, os seus links e os seus título que passaram por uma série de "validações".

# Instalação

* cd `project_path`
* Run `composer install`

## Requesitos para instalação com  o DDEV 

```
name: PHPAppTerranova
type: php
docroot: ""
php_version: "8.1"
webserver_type: nginx-fpm
xdebug_enabled: false
additional_hostnames: []
additional_fqdns: []
use_dns_when_possible: true
composer_version: "2"
web_environment: []

```

# REPO GIT HUB

* https://github.com/diogopnt/PHPAppTerranova

# Comandos

Dentro do ddev ssh correr

* ./console.php app:app:report-URLs `base_url input` `xml_file input` `int input` 
* ./console.php app:titleValidator `base_url input` `xml_file input` `int input`

# Descrição dos comandos

* ./console.php app:app:report-URLs `base_url input` `xml_file input` `int input`
    Comando que fornece um report de URL´s inativos/activos. Aqui é feita a leitura do ficheiro XML onde será retirado o href de cada node (notícia). Este href que foi extráido do ficheiro XML será adicionado a uma base que formará um URL. Depois desta primeira fase concluída, o URL criado será verificado, caso seja válido/exista será guardado no array $activeURLS, caso seja inválido/não exista seja aguardado no array $inactiveURLS. Ao correr o comando é necessário especificar, que base URL quer verificar, que ficheiro xml prentende ler, bem como o report que deseja obter no final (Caso pretenda o report de URL´s válidos coloque o número 1, caso seja o report de URL´s diferentes coloque o número 2).


* ./console.php app:titleValidator `base_url input` `xml_file input` `int input`
    Comando que fornece um report de titulos que sofreram alteração. Aqui é feita a leitura do ficheiro XML onde será retirado o título de cada node (notícia). Depois será feita a comparação entre o título presente no ficheiro XML e o título presente no URL dessa mesma notícia. Ao correr o comando é necessário especificar, que base URL quer verificar, ficheiro xml prentende ler, bem como o report que deseja obter no final (Caso pretenda o report de títulos iguais coloque o número 1, caso seja o report de títulos diferentes coloque o número 2).


The Console component eases the creation of beautiful and testable command line
interfaces.

Sponsor
-------

The Console component for Symfony 6.4 is [backed][1] by [Les-Tilleuls.coop][2].

Les-Tilleuls.coop is a team of 70+ Symfony experts who can help you design, develop and
fix your projects. They provide a wide range of professional services including development,
consulting, coaching, training and audits. They also are highly skilled in JS, Go and DevOps.
They are a worker cooperative!

Help Symfony by [sponsoring][3] its development!

Resources
---------

 * [Documentation](https://symfony.com/doc/current/components/console.html)
 * [Contributing](https://symfony.com/doc/current/contributing/index.html)
 * [Report issues](https://github.com/symfony/symfony/issues) and
   [send Pull Requests](https://github.com/symfony/symfony/pulls)
   in the [main Symfony repository](https://github.com/symfony/symfony)

Credits
-------

`Resources/bin/hiddeninput.exe` is a third party binary provided within this
component. Find sources and license at https://github.com/Seldaek/hidden-input.

[1]: https://symfony.com/backers
[2]: https://les-tilleuls.coop
[3]: https://symfony.com/sponsor
