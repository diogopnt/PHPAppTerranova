#!/usr/bin/env php
<?php
// app.php

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\HelpCommand;
use Symfony\Component\Console\Command\LazyCommand;

$app = new Application();


//$app->add(new HelpCommand());

$app->run();