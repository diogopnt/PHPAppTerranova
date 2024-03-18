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

        // Loop to get all the href in the XML File
        foreach ($xmlFile->node as $node) {
            $href = (string) $node->title->a['href'];

            $url = $baseUrl . $href;

            $status = self::checkUrlStatus($url);

            if ($status == "URL não encontrada (status 404)") {
                $inactiveURLS[] = $url;
            } elseif ($status == "URL encontrada") {
                $activeURLS[] = $url;
            }
        }

        if (!empty($inactiveURLS)) {
            $output->writeln("<comment>Páginas não encontradas (status 404):</comment>");
            foreach ($inactiveURLS as $url) {
                $output->writeln($url);
            }
        } else {
            $output->writeln("<info>Todas as páginas foram encontradas</info>");
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
