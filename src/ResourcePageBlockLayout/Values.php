<?php
namespace StaticSiteExport\ResourcePageBlockLayout;

use ArrayObject;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Job\JobInterface;

class Values implements ResourcePageBlockLayoutInterface
{
    public function getMarkdown(
        JobInterface $job,
        AbstractResourceEntityRepresentation $resource,
        ArrayObject $frontMatterPage,
        ArrayObject $frontMatterBlock
    ): string {
        $allValues = $resource->values();
        if (!$allValues) {
            return '';
        }
        return sprintf(
            "## %s\n%s",
            $job->translate('Values'),
            $job->getValuesMarkdown($resource, $frontMatterPage, $frontMatterBlock)
        );
    }
}
