<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class ReportCommand extends Command
{
    protected static $defaultName = 'app:report-URLs';

    protected function configure()
    {
        $this
            ->setDescription('Report de URL´s falhados')
            ->addArgument('baseUrl', InputArgument::REQUIRED, 'URL base a verificar')
            ->addArgument('file', InputArgument::REQUIRED, 'Ficheiro XML')
            ->addArgument('int', InputArgument::REQUIRED, '1. URL´s Válidos 2. URL´s Inválidos')
            ->setHelp("Escolha entre 1 ou 2 para obter o report que pretende");
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
            //$output->writeln("<warning>".$xmlFile->count()."</warning>");

            if ($xmlFile === false) {
                $output->writeln("<error>Não foi possível ler o arquivo XML</error>");
                return Command::FAILURE;
            }

            $inactiveURLS = [];
            $activeURLS = [];

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

                // Loop to get all the href in the XML File
                foreach ($xmlFile->node as $node) {
                    $href = (string) $node->title->a['href'];

                    $url = $baseUrl . $href;

                    //$results = $url . PHP_EOL . self::checkUrlStatus($url). "\n";

                    //$output->writeln($results);

                    $status = self::checkUrlStatus($url);

                    if ($status == "URL não encontrada (status 404)") {
                        $inactiveURLS[] = $url;
                        //echo "URL não encontrada (status 404): " . $url . PHP_EOL . "\n";
                    } elseif ($status == "URL encontrada") {
                        $activeURLS[] = $url;
                        //echo "URL encontrada: " . $url . PHP_EOL . "\n";
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
                    $output->writeln("\n Report de URL´s válidos.\n");

                    foreach ($activeURLS as $urlV) {
                        $output->writeln($urlV);
                    }
                } elseif ($opcaoInput == 2) {
                    $output->writeln("\n Report de URL´s inválidos.\n");

                    foreach ($inactiveURLS as $urlI) {
                        $output->writeln($urlI);
                    }
                }
            }
            /*
        do {
            echo "Escolha qual dos reports pretende visualizar: \n 1. URL´s Válidos \n 2. URL´s inválidos \n 3. Sair \n";

            $opcao = trim(fgets(STDIN));

            switch ($opcao) {
                case '1':
                    $output->writeln("Report de URL´s válidos.\n");

                    foreach ($activeURLS as $urlV) {
                        $output->writeln($urlV);
                    }
                    break;
                case '2':
                    $output->writeln("Report de URL´s inválidos.\n");

                    foreach ($inactiveURLS as $urlI) {
                        $output->writeln($urlI);
                    }
                    break;
                case '3':
                    $output->writeln("A sair do programa.... \n");
                    break;
                    
                default:
                    echo "Opção inválida. Por favor, escolha 1, 2 ou 3\n";
                    break;
            }
        } while ($opcao != 3); */
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

        if ($statusCode == '200' || $statusCode == '302') {
            return "URL encontrada";
        } elseif ($statusCode == '404') {
            return "URL não encontrada (status 404)";
        } else {
            return "Código de status HTTP desconhecido: $statusCode";
        }
    }
}
