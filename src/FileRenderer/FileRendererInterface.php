<?php
namespace StaticSiteExport\FileRenderer;

use ArrayObject;
use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Job\JobInterface;

interface FileRendererInterface
{
    /**
     * Get the Markdown for a file renderer.
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
