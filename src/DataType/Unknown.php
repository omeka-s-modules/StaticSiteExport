<?php
namespace StaticSiteExport\DataType;

use ArrayObject;
use Omeka\Api\Representation\ValueRepresentation;
use Omeka\Job\JobInterface;

class Unknown implements DataTypeInterface
{
    public function getMarkdown(
        JobInterface $job,
        ValueRepresentation $value,
        ArrayObject $frontMatterPage,
        ArrayObject $frontMatterBlock
    ): string {
        return '';
    }
}
