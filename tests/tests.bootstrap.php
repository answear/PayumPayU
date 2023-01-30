<?php

use Composer\Autoload\ClassLoader;

/** @var ClassLoader $loader */
if (!$loader = @include dirname(__DIR__) . '/vendor/autoload.php') {
    echo <<<EOM
You must set up the project dependencies by running the following commands:
    curl -s http://getcomposer.org/installer | php
    php composer.phar install --dev
EOM;
    exit(1);
}

// override OpenPayU_HttpCurl class with static methods
$classMap = [
    'OpenPayU_HttpCurl' => dirname(__DIR__) . '/testsMock/OpenPayU_HttpCurl.php',
];
$loader->addClassMap($classMap);
