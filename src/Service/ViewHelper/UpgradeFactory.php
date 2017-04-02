<?php

namespace UpgradeFromOmekaClassic\Service\ViewHelper;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use UpgradeFromOmekaClassic\View\Helper\Upgrade;

/**
 * Service factory for the api view helper.
 */
class UpgradeFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new Upgrade($services);
    }
}
