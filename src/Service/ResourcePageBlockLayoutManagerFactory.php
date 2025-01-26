<?php
namespace StaticSiteExport\Service;

use StaticSiteExport\ResourcePageBlockLayout\Manager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class ResourcePageBlockLayoutManagerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $config = $services->get('Config');
        return new Manager($services, $config['static_site_export']['resource_page_block_layouts']);
    }
}
