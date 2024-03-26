#!/usr/bin/env php
<?php
// app.php

require __DIR__.'/vendor/autoload.php';

use App\Command\ReportCommand;
use App\Command\TitleValidatorCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\HelpCommand;
use Symfony\Component\Console\Command\LazyCommand;

$app = new Application();


$app->add(new ReportCommand());
$app->add(new TitleValidatorCommand());

$app->run();