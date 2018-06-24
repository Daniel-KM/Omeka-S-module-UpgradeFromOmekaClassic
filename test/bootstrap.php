<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use OmekaTestHelper\Bootstrap;

Bootstrap::bootstrap(__DIR__);
Bootstrap::loginAsAdmin();
Bootstrap::enableModule('UpgradeFromOmekaClassic');

require_once dirname(__DIR__) . '/src/View/Helper/Upgrade.php';
require_once dirname(__DIR__) . '/src/View/Helper/Inflector.php';
