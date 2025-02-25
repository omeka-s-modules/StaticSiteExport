<?php
namespace StaticSiteExport\ResourcePageBlockLayout;

use ArrayObject;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Job\JobInterface;

interface ResourcePageBlockLayoutInterface
{
    /**
     * Get the Markdown for a resource page block layout.
     *
     * @param JobInterface $job The export job, use for convenience methods
     * @param ValueRepresentation $value The Omeka value
     * @param ArrayObject $frontMatterPage The page's front matter
     * @param ArrayObject $frontMatterBlock The block's front matter
     */
    public function getMarkdown(
        JobInterface $job,
        AbstractResourceEntityRepresentation $resource,
        ArrayObject $frontMatterPage,
        ArrayObject $frontMatterBlock
    ): string;
}
