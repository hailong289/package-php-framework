<?php

require '../vendor/autoload.php';

use Symfony\Component\Console\Application;

$application = new Application();

// ... register commands

$application->register('generate-admin');
$application->register('generate-admin1');
$application->register('generate-admin2');
//    ->addArgument('username', \Symfony\Component\Console\Input\InputArgument::REQUIRED)
//    ->setCode(function (\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output): int {
//        // ...
//
//        return \Symfony\Component\Console\Command\Command::SUCCESS;
// });
$application->run();