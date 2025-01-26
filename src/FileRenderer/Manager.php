<?php
namespace StaticSiteExport\FileRenderer;

use Omeka\ServiceManager\AbstractPluginManager;

class Manager extends AbstractPluginManager
{
    protected $autoAddInvokableClass = false;

    protected $instanceOf = FileRendererInterface::class;
}
