<?php
namespace StaticSiteExport\ResourcePageBlockLayout;

use ArrayObject;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Job\JobInterface;

class ResourceClass implements ResourcePageBlockLayoutInterface
{
    public function getMarkdown(
        JobInterface $job,
        AbstractResourceEntityRepresentation $resource,
        ArrayObject $frontMatterPage,
        ArrayObject $frontMatterBlock
    ): string {
        $resourceClass = $resource->resourceClass();
        if (!$resourceClass) {
            return '';
        }
        return sprintf(
            "%s\n: %s",
            $job->translate('Resource class'),
            $resourceClass->label()
        );
    }
}
