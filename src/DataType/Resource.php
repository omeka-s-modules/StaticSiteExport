<?php
namespace StaticSiteExport\DataType;

use ArrayObject;
use Omeka\Api\Representation\ValueRepresentation;
use Omeka\Job\JobInterface;

class Resource implements DataTypeInterface
{
    public function getMarkdown(
        JobInterface $job,
        ValueRepresentation $value,
        ArrayObject $frontMatterPage,
        ArrayObject $frontMatterBlock
    ): string {
        $valueResource = $value->valueResource();
        if (in_array($valueResource->id(), $job->getItemIds())) {
            $contentDirectory = 'items';
        } elseif (in_array($valueResource->id(), $job->getItemSetIds())) {
            $contentDirectory = 'item-sets';
        } elseif (in_array($valueResource->id(), $job->getMediaIds())) {
            $contentDirectory = 'media';
        } else {
            return ''; // Resource not in site.
        }
        return sprintf(
            '[%s]({{< ref "/%s/%s" >}} "%s")',
            $job->escape(['[', ']'], $valueResource->displayTitle()),
            $contentDirectory,
            $valueResource->id(),
            $job->escape(['"'], $valueResource->displayTitle()),
        );
    }
}
