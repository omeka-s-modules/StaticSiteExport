<?php
namespace StaticSiteExport\MediaRenderer;

use ArrayObject;
use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Job\JobInterface;

interface MediaRendererInterface
{
    /**
     * Get the Markdown for a media renderer.
     *
     * @param JobInterface $job The export job, use for convenience methods
     * @param ValueRepresentation $value The Omeka value
     * @param ArrayObject $frontMatterPage The page's front matter
     * @param ArrayObject $frontMatterBlock The block's front matter
     */
    public function getMarkdown(
        JobInterface $job,
        MediaRepresentation $media,
        ArrayObject $frontMatterPage,
        ArrayObject $frontMatterBlock
    ): string;
}
