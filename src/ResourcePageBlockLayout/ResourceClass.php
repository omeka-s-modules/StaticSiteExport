<?php
namespace StaticSiteExport\ResourcePageBlockLayout;

use ArrayObject;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Job\JobInterface;

class ResourceClass implements ResourcePageBlockLayoutInterface
{
    public function getMarkdown(
        JobInterface $job,
        ArrayObject $frontMatter,
        AbstractResourceEntityRepresentation $resource
    ): string {
        $resourceClass = $resource->resourceClass();
        if (!$resourceClass) {
            return '';
        }
        return sprintf('Resource class%s: %s', "\n", $resourceClass->label());
    }
}
