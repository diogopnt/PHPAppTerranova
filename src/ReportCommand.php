<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
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
            ->addArgument('file', InputArgument::REQUIRED, 'Ficheiro XML');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // URL D10
        $baseUrl = "https://terranova-d10.pictonio.pt";

        // Read the XML File
        $xmlFile = simplexml_load_file($input->getArgument('file'));

        if ($xmlFile === false) {
            $output->writeln("<error>Não foi possível ler o arquivo XML</error>");
            return Command::FAILURE;
        }

        $inactiveURLS = [];
        $activeURLS = []; 

        echo "A carregar resultados...";

        // Loop to get all the href in the XML File
        foreach ($xmlFile->node as $node) {
            $href = (string) $node->title->a['href'];

            $url = $baseUrl . $href;

            //$results = $url . PHP_EOL . self::checkUrlStatus($url). "\n";

            //$output->writeln($results);

            $status = self::checkUrlStatus($url);

            if ($status == "URL não encontrada (status 404)") {
                $inactiveURLS[] = $url;
                echo "URL não encontrada (status 404): " . $url . PHP_EOL . "\n";
            } elseif ($status == "URL encontrada") {
                $activeURLS[] = $url;
                echo "URL encontrada: " . $url . PHP_EOL . "\n";
            }
        }

        echo "Escolha qual dos reports pretende visualizar: \n 1. URL´s Válidos \n 2. URL´s inválidos";

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
            default:
                echo "Opção inválida. Por favor, escolha 1 ou 2.\n";
                break;
        }

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
