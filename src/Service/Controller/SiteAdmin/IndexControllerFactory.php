<?php
namespace StaticSiteExport\Service\Controller\SiteAdmin;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use StaticSiteExport\Controller\SiteAdmin\IndexController;

class IndexControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new IndexController(
            $services->get('Omeka\EntityManager')
        );
    }
}
