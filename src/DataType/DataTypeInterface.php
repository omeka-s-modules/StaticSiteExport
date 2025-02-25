<?php
namespace StaticSiteExport\DataType;

use ArrayObject;
use Omeka\Api\Representation\ValueRepresentation;
use Omeka\Job\JobInterface;

interface DataTypeInterface
{
    /**
     * Get the Markdown for a data type.
     *
     * @param JobInterface $job The export job, use for convenience methods
     * @param ValueRepresentation $value The Omeka value
     * @param ArrayObject $frontMatterPage The page's front matter
     * @param ArrayObject $frontMatterBlock The block's front matter
     */
    public function getMarkdown(
        JobInterface $job,
        ValueRepresentation $value,
        ArrayObject $frontMatterPage,
        ArrayObject $frontMatterBlock
    ): string;
}
