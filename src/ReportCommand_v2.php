<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class ReportCommand_v2 extends Command
{
    protected static $defaultName = 'app:report_url';

    protected function configure()
    {
        $this
            ->setDescription('Report de URL´s')
            ->addArgument('baseUrl', InputArgument::REQUIRED, 'URL base a verificar')
            ->addArgument('file', InputArgument::REQUIRED, 'Ficheiro XML')
            ->addArgument('int', InputArgument::REQUIRED, '1. URL´s Válidos 2. URL´s Inválidos 3. Outro tipo de URL´s')
            ->setHelp("Escolha entre 1,2 ou 3 para obter o report que pretende");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $baseUrl = $input->getArgument('baseUrl');

        $baseUrlCheck = self::checkUrlStatus($baseUrl);

        if ($baseUrlCheck == "URL encontrada") {

            //$baseUrl = "https://terranova-d10.pictonio.pt";

            // Read the XML File
            $xmlFile = simplexml_load_file($input->getArgument('file'));
            $records = $xmlFile->count();

            if ($xmlFile === false) {
                $output->writeln("<error>Não foi possível ler o arquivo XML</error>");
                return Command::FAILURE;
            }

            $inactiveURLS = [];
            $activeURLS = [];
            $othersURLS = [];

            $opcaoInput = $input->getArgument('int');

            if ($opcaoInput != 1 && $opcaoInput != 2 && $opcaoInput != 3) {
                $output->writeln("<error>Opção inválida. Por favor escolha 1,2 ou 3</error>");
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

                $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:16s%/%estimated:-16s% %memory:6s%');
                $progressBar->start();

                // Loop to get all the href in the XML File
                foreach ($xmlFile->node as $node) {
                    $NID = (int) $node->Nid;

                    $href = (string) $node->title->a['href'];

                    //$hrefNovo = self::urlPath($href);

                    $url = $baseUrl . $href;

                    $status = self::checkUrlStatus($url);
                    $statusCodeInt = self::statusCode($url);

                    $NIDPorURL[$url] = $NID;
                    $StatusPorUrl[$url] = $statusCodeInt;

                    if ($status == "URL não encontrada (status 404)") {
                        $inactiveURLS[] = $url;
                    } elseif ($status == "URL encontrada") {
                        $activeURLS[] = $url;
                    } elseif ($status == "Código de status HTTP desconhecido") {
                        $othersURLS[] = $url;
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
                    $numberACTUrl = count($activeURLS);
                    $numberACTUrlP = $numberACTUrl / $records * 100;

                    $outputData = [
                        'report' => 'Report de URL validos.',
                        'urls' => []
                    ];

                    foreach ($activeURLS as $urlV) {
                        $outputData['urls'][] = [
                            'URL' => $urlV,
                            'NID' => $NIDPorURL[$urlV]
                        ];
                    }

                    $output->writeln($numberACTUrl . " URL´S válidos de " . $records . " | " . $numberACTUrlP . "%");
                } elseif ($opcaoInput == 2) {
                    $numberIACTUrl = count($inactiveURLS);
                    $numberIACTUrlP = $numberIACTUrl / $records * 100;

                    $outputData = [
                        'report' => 'Report de URLs invalidos.',
                        'urls' => []
                    ];

                    foreach ($inactiveURLS as $urlI) {
                        $outputData['urls'][] = [
                            'URL' => $urlI,
                            'NID' => $NIDPorURL[$urlI]
                        ];
                    }

                    $output->writeln($numberIACTUrl . " URL´s inválidos de " . $records . " | " . $numberIACTUrlP . "%");
                } elseif ($opcaoInput == 3) {
                    $numberOUrl = count($othersURLS);
                    $numberOUrlP = $numberOUrl / $records * 100;

                    $outputData = [
                        'report' => 'Report de outro tipo de URLs.',
                        'urls' => []
                    ];

                    foreach ($othersURLS as $urlO) {
                        $outputData['urls'][] = [
                            'URL' => $urlO,
                            'NID' => $NIDPorURL[$urlO],
                            'Status code' => $StatusPorUrl[$urlO]
                        ];
                    }

                    $output->writeln($numberOUrl . " URL´s com o código de status HTTP desconhecido de " . $records . " | " . $numberOUrlP . "%");
                }

                $jsonOutput = json_encode($outputData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

                $output->writeln($jsonOutput);
            }
        } elseif ($baseUrlCheck == "URL não encontrada (status 404)") {
            $output->writeln("<error>URL não encontrada (status 404)</error>");
            return Command::FAILURE;
        }
        $progressBar->finish();

        return Command::SUCCESS;
    }

    protected static function checkUrlStatus($url)
    {
        $headers = get_headers($url);

        if ($headers === false) {
            return "Erro ao obter os cabeçalhos HTTP";
        }

        $statusCode = explode(' ', $headers[0])[1];

        if ($statusCode == '200') {
            return "URL encontrada";
        } elseif ($statusCode == '404') {
            return "URL não encontrada (status 404)";
        } else {
            return "Código de status HTTP desconhecido";
        }
    }

    protected static function urlPath($href)
    {
        if (is_array($href)) {
            foreach ($href as $key => $hrefnode) {
                if (strpos($hrefnode, "/noticia") === 0) {
                    $href[$key] = str_replace("/noticia", "/noticias", $hrefnode);

                    return $href;
                } else {
                    return $href;
                }
            }
        } else {
            if (strpos($href, "/noticia") === 0) {
                return str_replace("/noticia", "/noticias", $href);
            } else {
                return $href;
            }
        }

        return $href;
    }

    protected function statusCode($url)
    {
        $headers = get_headers($url);

        if ($headers === false) {
            return "Erro ao obter os cabeçalhos HTTP";
        }

        $statusCode = explode(' ', $headers[0])[1];

        return $statusCode;
    }
}
