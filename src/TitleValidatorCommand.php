<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;


class ReportCommand extends Command
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
        
    }


}