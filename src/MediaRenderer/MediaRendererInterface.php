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
     * @param MediaRepresentation $resource The Omeka media
     * @param JobInterface $job The export job, use for convenience methods
     * @param ArrayObject $frontMatter The page's front matter
     */
    public function getMarkdown(
        MediaRepresentation $media,
        JobInterface $job,
        ArrayObject $frontMatter
    ): string;
}
