<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;


class TitleValidatorCommand extends Command
{
    protected static $defaultName = 'app:titleValidator';


    protected function configure()
    {
        $this
            ->setDescription('Validador de page titles')
            ->addArgument('file', InputArgument::REQUIRED, 'Ficheiro XML');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $baseUrl = "https://terranova-d10.pictonio.pt";

        // Read the XML File
        $xmlFile = simplexml_load_file($input->getArgument('file'));

        if ($xmlFile === false) {
            $output->writeln("<error>Não foi possível ler o arquivo XML</error>");
            return Command::FAILURE;
        }

        foreach ($xmlFile->node as $node) {
            $title = (string) $node->title->a;
            $href = (string) $node->title->a['href'];

            $url = $baseUrl . $href;

            /*
            $results = $url . PHP_EOL . "\n" . $title;
            $output->writeln($results); */

            $pageTitle = self::checkURLPageTitle($url);

            echo "\n" . $pageTitle . PHP_EOL . "\n" . $url . "\n";
            //echo $title . "\n";
        }

        return Command::SUCCESS;
    }


    protected static function checkURLPageTitle($url)
    {

        $status = self::checkUrlStatus($url);

        if ($status == "URL não encontrada (status 404)") {
            return "Página não encontrada";
        } elseif ($status == "URL encontrada") {
            $html = file_get_contents($url);
            preg_match("/<title>(.+)<\/title>/i", $html, $title);
            preg_match_all('/<meta .*?name=["\']?([^"\']+)["\']? .*?content=["\']([^"\']+)["\'].*?>/i', $html, $meta);

            return "Title: " . $title[1];
            /*
            for ($i = 0; $i < count($meta[1]); $i++) {
                return "Meta " . $meta[1][$i] . ": " . $meta[2][$i] . "<br>";
            }*/
        }
    }

    protected static function checkUrlStatus($url)
    {
        $headers = get_headers($url);

        if ($headers === false) {
            return "Erro ao obter os cabeçalhos HTTP";
        }

        $statusCode = explode(' ', $headers[0])[1];

        if ($statusCode == '200' || $statusCode == '302') {
            return "URL encontrada";
        } elseif ($statusCode == '404') {
            return "URL não encontrada (status 404)";
        } else {
            return "Código de status HTTP desconhecido: $statusCode";
        }
    }

    protected static function comparePageTitle($title, $urlPageTitle)
    {
    }
}
