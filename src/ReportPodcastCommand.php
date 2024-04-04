<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class ReportPodcastCommand extends Command
{

    protected static $defaultName = 'app:report-Podcast';

    protected function configure()
    {
        $this
            ->setDescription('Report de Podcast´s')
            ->addArgument('file', InputArgument::REQUIRED, 'Ficheiro XML')
            ->addArgument('int', InputArgument::REQUIRED, '1. Podcast´s Válidos 2. Podcast´s Inválidos 3. Outro tipo de Podcast´s')
            ->setHelp("Escolha entre 1,2 ou 3 para obter o report que pretende");
    }

    protected function execute(InputInterface $inputInterface, OutputInterface $outputInterface)
    {
        $xmlFile = simplexml_load_file($inputInterface->getArgument('file'));
        $records = $xmlFile->count();

        if ($xmlFile === false) {
            $outputInterface->writeln("<error>Não foi possível ler o arquivo XML</error>");
            return Command::FAILURE;
        }

        $inactivePod = [];
        $activePod = [];
        $otherPod = [];

        $opcaoInput = $inputInterface->getArgument('int');

        if ($opcaoInput != 1 && $opcaoInput != 2 && $opcaoInput != 3) {
            $outputInterface->writeln("<error>Opção inválida. Por favor escolha 1,2 ou 3</error>");
            return Command::FAILURE;
        } else {

            echo "A carregar resultados... \n";

            $progressBar = new ProgressBar($outputInterface, $records);

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

            foreach ($xmlFile->node as $node) {
                $NID = (int) $node->Nid;

                $url_podcast = (string) $node->Podcast;

                $status = self::checkUrlStatus($url_podcast);
                $statusCodeInt = self::statusCode($url_podcast);

                $NIDPorURL[$url_podcast] = $NID;
                $StatusPorUrl[$url_podcast] = $statusCodeInt;

                if ($status == "URL não encontrada (status 404)") {
                    $inactivePod[] = $url_podcast;
                } elseif ($status == "URL encontrada") {
                    $activePod[] = $url_podcast;
                } elseif ($status == "Código de status HTTP desconhecido") {
                    $otherPod[] = $url_podcast;
                }

                $progressBar->advance();

                foreach ($progressBarMessages as $threshold => $message) {
                    if ($progressBar->getProgress() == $threshold) {
                        $progressBar->setMessage($message);
                        $outputInterface->writeln('');
                        $outputInterface->writeln("<info>$message</info>");
                        break;
                    }
                }
            }

            if ($opcaoInput == 1) {
                //$output->writeln("\n Report de URL´s válidos.\n");
                $numberACTUrl = count($activePod);
                $numberACTUrlP = $numberACTUrl / $records * 100;

                $outputData = [
                    'report' => 'Report de Podcasts validos.',
                    'podcasts' => []
                ];

                foreach ($activePod as $urlV) {
                    $outputData['podcasts'][] = [
                        'Podcast' => $urlV,
                        'NID' => $NIDPorURL[$urlV]
                    ];
                }

                $outputInterface->writeln($numberACTUrl . " Podcast´s válidos de " . $records . " | " . $numberACTUrlP . "%");
            } elseif ($opcaoInput == 2) {
                //$output->writeln("\n Report de URL´s inválidos.\n");
                $numberIACTUrl = count($inactivePod);
                $numberIACTUrlP = $numberIACTUrl / $records * 100;

                $outputData = [
                    'report' => 'Report de Podcasts invalidos.',
                    'podcasts' => []
                ];

                foreach ($inactivePod as $urlI) {
                    $outputData['podcasts'][] = [
                        'Podcast' => $urlI,
                        'NID' => $NIDPorURL[$urlI]
                    ];
                }

                $outputInterface->writeln($numberIACTUrl . " Podcast´s inválidos de " . $records . " | " . $numberIACTUrlP . "%");
            } elseif ($opcaoInput == 3) {
                $numberOUrl = count($otherPod);
                $numberOUrlP = $numberOUrl / $records * 100;

                $outputData = [
                    'report' => 'Report de outro tipo de Podcasts.',
                    'podcasts' => []
                ];

                foreach ($otherPod as $urlO) {
                    $outputData['podcasts'][] = [
                        'Podcast' => $urlO,
                        'NID' => $NIDPorURL[$urlO],
                        'Status code' => $StatusPorUrl[$urlO]
                    ];
                }

                $outputInterface->writeln($numberOUrl . " Podcast´s com o código de status HTTP desconhecido de " . $records . " | " . $numberOUrlP . "%");
            }

            $jsonOutput = json_encode($outputData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

            $outputInterface->writeln($jsonOutput);

            $progressBar->finish();

            return Command::SUCCESS;
        }
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
