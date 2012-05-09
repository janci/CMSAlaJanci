<?php

/**
 * My Application bootstrap file.
 */



// Load Nette Framework
require LIBS_DIR . '/Nette/loader.php';
require LIBS_DIR . '/Spyc/spyc.php';


// Configure application
$configurator = new NConfigurator;

// Enable Nette Debugger for error visualisation & logging
//$configurator->setDebugMode(NConfigurator::AUTO);
$configurator->enableDebugger(dirname(__FILE__) . '/../log');

// Enable RobotLoader - this will load all classes automatically
$configurator->setTempDirectory(dirname(__FILE__) . '/../temp');

$robotLoader = $configurator->createRobotLoader()
	->addDirectory(APP_DIR)
	->addDirectory(LIBS_DIR);

$robotLoader->ignoreDirs = $robotLoader->ignoreDirs.",plugins/*/www/*";
$robotLoader->register();

// Create Dependency Injection container from config.neon file
$configurator->addConfig(dirname(__FILE__) . '/config/config.neon');
$container = $configurator->createContainer();
$container->addService('robotloader', $robotLoader);
        
// Setup router
$container->router[] = new NRoute('index.php', 'Homepage:default', NRoute::ONE_WAY);
$container->router[] = new NRoute('<presenter>/<action>[/<id>]', 'Page:default');


// Configure and run the application!
$container->application->run();
