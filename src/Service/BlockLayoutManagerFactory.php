<?php
namespace StaticSiteExport\Service;

use StaticSiteExport\BlockLayout\Manager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class BlockLayoutManagerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $config = $services->get('Config');
        return new Manager($services, $config['static_site_export']['block_layouts']);
    }
}
