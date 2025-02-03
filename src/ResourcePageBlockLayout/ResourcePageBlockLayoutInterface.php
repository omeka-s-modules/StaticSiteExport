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
     * @param AbstractResourceEntityRepresentation $resource The Omeka resource
     * @param JobInterface $job The export job, use for convenience methods
     * @param ArrayObject $frontMatter The page's front matter
     */
    public function getMarkdown(
        AbstractResourceEntityRepresentation $resource,
        JobInterface $job,
        ArrayObject $frontMatter
    ): string;
}
