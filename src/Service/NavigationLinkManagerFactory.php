<?php
namespace StaticSiteExport\Service;

use StaticSiteExport\NavigationLink\Manager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class NavigationLinkManagerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $config = $services->get('Config');
        return new Manager($services, $config['static_site_export']['navigation_links']);
    }
}
