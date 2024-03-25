<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
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
            ->addArgument('baseUrl', InputArgument::REQUIRED, 'URL base a verificar')
            ->addArgument('file', InputArgument::REQUIRED, 'Ficheiro XML')
            ->addArgument('int', InputArgument::REQUIRED, '1. Títulos iguais 2. Títulos diferentes')
            ->setHelp("Escolha entre 1 ou 2 para obter o report que pretende");
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $baseUrl = $input->getArgument('baseUrl');

        $baseUrlCheck = self::checkUrlStatus($baseUrl);

        if ($baseUrlCheck == "URL encontrada") {
            //$baseUrl = "https://terranova-d10.pictonio.pt";

            //URL´S with the same page title
            $spt = [];
            //URL´S with a diferent page title
            $dpt = [];

            // Read the XML File
            $xmlFile = simplexml_load_file($input->getArgument('file'));
            $records = $xmlFile->count();

            if ($xmlFile === false) {
                $output->writeln("<error>Não foi possível ler o arquivo XML</error>");
                return Command::FAILURE;
            }

            $opcaoInput = $input->getArgument('int');

            if ($opcaoInput != 1 && $opcaoInput != 2) {
                $output->writeln("<error>Opção inválida. Por favor escolha 1 ou 2</error>");
                return Command::FAILURE;
            } else {
                echo "A carregar resultados... \n";

                $progressBar = new ProgressBar($output, $records);

                $pgb_25 = $records * 0.25;
                $pgb_50 = $records * 0.50;
                $pgb_75 = $records * 0.75;

                $progressBarMessages = [
                    intval($pgb_25) => 'O seu processo ainda está no início',
                    intval($pgb_50) => 'O seu processo encontra-se 50% concluído',
                    intval($pgb_75) => 'O seu processo está quase completo',
                    $records => 'Processo completo',
                ];

                //$progressBar->setBarCharacter('<comment>....</comment>');
                $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:16s%/%estimated:-16s% %memory:6s%');
                $progressBar->start();

                foreach ($xmlFile->node as $node) {
                    $NID = (int) $node->Nid;
                    $titleXML = (string) $node->title->a;
                    $href = (string) $node->title->a['href'];

                    $hrefNovo = self::urlPath($href);

                    $url = $baseUrl . $hrefNovo;

                    $title = "Title: " . $titleXML . " | Terranova";

                    /*
                    $results = $url . PHP_EOL . "\n" . $title;
                    $output->writeln($results); */

                    $pageTitle = self::checkURLPageTitle($url);

                    //echo "\n" . $pageTitle . PHP_EOL . "\n" . $url . "\n";
                    //echo $title . "\n";

                    $result = self::comparePageTitle($title, $pageTitle);

                    if ($result == "Titulos iguais") {
                        $spt[] = $pageTitle . " -> " . $url . " | NID -> " . $NID;
                        //echo "Títulos iguais -> " . $url . "\n";
                    } elseif ($result == "Titulos diferentes") {
                        $dpt[] = $pageTitle . "-> " . $url . " | NID -> " . $NID;
                        //echo "Títulos diferentes -> " . $url . "\n";
                    }

                    $progressBar->advance();

                    foreach ($progressBarMessages as $threshold => $message) {
                        if ($progressBar->getProgress() == $threshold) {
                            $progressBar->setMessage($message);
                            $output->writeln('');
                            $output->writeln("<info>$message</info>");
                            break;
                        }
                    }
                }

                if ($opcaoInput == 1) {
                    $output->writeln(" \n Report de Títulos iguais.\n");
                    $numberSPT = count($spt);
                    $numberSPTP = count($spt);

                    foreach ($spt as $sptV) {
                        $output->writeln($sptV);
                    }

                    $output->writeln($numberSPT . " títulos iguais de " . $records . " | " . $numberSPTP . "%");
                } elseif ($opcaoInput == 2) {
                    $output->writeln("\n Report de Títulos diferentes.\n");
                    $numberDPT = count($dpt);
                    $numberDPTP = $numberDPT / $records * 100;

                    foreach ($dpt as $dptI) {
                        $output->writeln($dptI);
                    }

                    $output->writeln($numberDPT . " títulos diferentes de " . $records . " | " . $numberDPTP . "%");

                }
            }

            /*
        do {
            echo "Escolha qual dos reports pretende visualizar: \n 1. Títulos iguais \n 2. Títulos diferentes \n 3. Sair \n";

            $opcao = trim(fgets(STDIN));

            switch ($opcao) {
                case '1':
                    $output->writeln("Report de Títulos iguais.\n");

                    foreach ($spt as $sptV) {
                        $output->writeln($sptV);
                    }
                    break;
                case '2':
                    $output->writeln("Report de Títulos diferentes.\n");

                    foreach ($dpt as $dptI) {
                        $output->writeln($dptI);
                    }
                    break;
                case '3':
                    $output->writeln("A sair do programa.... \n");
                    break;
                    
                default:
                    echo "Opção inválida. Por favor, escolha 1, 2 ou 3\n";
                    break;
            }
        } while ($opcao != 3);*/
        } elseif ($baseUrlCheck == "URL não encontrada (status 404)") {
            $output->writeln("<error>URL não encontrada (status 404)</error>");
            return Command::FAILURE;
        }

        $progressBar->finish();

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
        if ($title == $urlPageTitle) {
            return "Titulos iguais";
        } elseif ($title != $urlPageTitle) {
            return "Titulos diferentes";
        }
    }

    protected static function urlPath($href){
        if (is_array($href)) { 
            foreach ($href as $key => $hrefnode){
                if (strpos($hrefnode, "/noticia") === 0){
                    $href[$key] = str_replace("/noticia", "/noticias", $hrefnode);
    
                    return $href;
                }else{
                    return $href;
                }
            }
        } else {
            if (strpos($href, "/noticia") === 0) { 
                return str_replace("/noticia", "/noticias", $href);
            }else{
                return $href;
            }
        }
    
        return $href;
    }
}
