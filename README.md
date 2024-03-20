Console Component
=================

# Descrição do projeto

Este projeto trata-se de uma pequena aplicação para ler um ficheiro XML de um site de notícias. Com a aplicação, será extraido deste ficheiro xml, os seus links e os seus título que passaram por uma série de "validações".

# Instalação

* cd `project_path`
* Run `composer install`

# REPO GIT HUB

* https://github.com/diogopnt/PHPAppTerranova

# Comandos

Dentro do ddev ssh correr

* ./console.php app:app:report-URLs `xml_file input`
* ./console.php app:titleValidator `xml_file input`

# Descrição dos comandos

* ./console.php app:app:report-URLs `xml_file input`
    Comando que fornece um report de URL´s inativos/activos. Aqui é feita a leitura do ficheiro XML onde será retirado o href de cada node (notícia). Este href que foi extráido do ficheiro XML será adicionado a uma base que formará um URL. Depois desta primeira fase concluída, o URL criado será verificado, caso seja válido/exista será guardado no array $activeURLS, caso seja inválido/não exista seja aguardado no array $inactiveURLS. No fim o utilizador poderá escolher qual dos reports é que pretende visualizar.


* ./console.php app:titleValidator `xml_file input`
    Comando que fornece um report de titulos que sofreram alteração. Aqui é feita a leitura do ficheiro XML onde será retirado o título de cada node (notícia). Depois será feita a comparação entre o título presente no ficheiro XML e o título presente no URL dessa mesma notícia. No fim, semelhante ao comando acima especificado, será apresentado um report de títulos que não sofreram alteração guardados no array $spt e de títulos que sofream alteração guardados no array $dpt.


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
