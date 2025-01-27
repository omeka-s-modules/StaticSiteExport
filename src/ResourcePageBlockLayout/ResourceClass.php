<?php
namespace StaticSiteExport\ResourcePageBlockLayout;

use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Job\JobInterface;

class ResourceClass implements ResourcePageBlockLayoutInterface
{
    public function getMarkup(AbstractResourceEntityRepresentation $resource, JobInterface $job) : string
    {
        $resourceClass = $resource->resourceClass();
        if (!$resourceClass) {
            return '';
        }
        return sprintf('Resource class%s: %s', "\n", $resourceClass->label());
    }
}
