<?php
namespace StaticSiteExport\ResourcePageBlockLayout;

use Omeka\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;

class Manager extends AbstractPluginManager
{
    protected $autoAddInvokableClass = false;

    protected $instanceOf = ResourcePageBlockLayoutInterface::class;

    public function get($name, $options = [], $usePeeringServiceManagers = true)
    {
        try {
            $instance = parent::get($name, $options, $usePeeringServiceManagers);
        } catch (ServiceNotFoundException $e) {
            $instance = new Unknown($name);
        }
        return $instance;
    }
}
