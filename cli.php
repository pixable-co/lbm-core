<?php

// Ensure this script runs only in CLI mode
if (PHP_SAPI !== 'cli') {
    exit('This script can only be run in CLI mode.');
}

require __DIR__ . '/vendor/autoload.php'; // Load Composer autoloader

use Symfony\Component\Console\Application;
use Pixable\LBM\MakeShortcodeCommand;
use Pixable\LBM\MakeShortcodeReactCommand;
use Pixable\LBM\MakeAjaxCommand;
use Pixable\LBM\MakeApiCommand;


use Pixable\LBM\DeleteShortcodeCommand;
use Pixable\LBM\DeleteShortcodeReactCommand;
use Pixable\LBM\DeleteAjaxCommand;
use Pixable\LBM\DeleteApiCommand;

// Create a new Symfony Console Application
$application = new Application();

// Register your custom commands
$application->add(new MakeShortcodeCommand());
$application->add(new MakeShortcodeReactCommand());
$application->add(new MakeAjaxCommand());
$application->add(new MakeApiCommand());

// Delete Commands
$application->add(new DeleteShortcodeCommand());
$application->add(new DeleteShortcodeReactCommand());
$application->add(new DeleteAjaxCommand());
$application->add(new DeleteApiCommand());

// Run the application
$application->run();
