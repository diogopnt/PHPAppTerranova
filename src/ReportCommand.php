<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class ReportCommand extends Command
{
    // the name of the command that users type after "php bin/console"
    protected static $defaultName = 'app:report-URLs';

    protected function configure()
    {
        $this
            ->setDescription('Report de URL´s falhados')
            ->addArgument('file', InputArgument::REQUIRED, 'Ficheiro XML');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //URL D10
        $baseUrl = "https://terranova-d10.pictonio.pt"; //erro a concatenar

        //Read the XML File
        //$xmlFile = $input->getArgument(simplexml_load_file('file'));
        $xmlFile = simplexml_load_file($input->getArgument('file'));

        if ($xmlFile === false) {

            $output->writeln("<error>Não foi possível ler o arquivo XML</error>");

            return Command::FAILURE;
        }

        $inactiveURLS = [];
        $activeURLS = [];

        //Loop do get all the href in teh XML File
        foreach ($xmlFile->node as $node) {
            $href = (string) $node->title->a['href'];

            $url = $baseUrl . $href;

            //echo $url . PHP_EOL . checkUrlStatus($url). "\n";

            $status = checkUrlStatus($url);

            if ($status == "URL não encontrada (status 404)") {
                //echo "Páginas não encontradas (status 404):\n" . $url;

                $inactiveURLS[] = $url;
            }elseif($status == "URL encontrada"){
                $activeURLS[] = $url;
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




        function checkUrlStatus($url)
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
}
