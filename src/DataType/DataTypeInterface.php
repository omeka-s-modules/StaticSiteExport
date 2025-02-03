<?php
namespace StaticSiteExport\DataType;

use ArrayObject;
use Omeka\Api\Representation\ValueRepresentation;
use Omeka\Job\JobInterface;

    /**
     * Get the Markdown for a data type.
     *
     * @param ValueRepresentation $value The Omeka value
     * @param JobInterface $job The export job, use for convenience methods
     * @param ArrayObject $frontMatter The page's front matter
     */
    interface DataTypeInterface
{
    public function getMarkdown(
        ValueRepresentation $value,
        JobInterface $job,
        ArrayObject $frontMatter
    ): string;
}
