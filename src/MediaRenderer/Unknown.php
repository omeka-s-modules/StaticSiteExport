<?php
namespace StaticSiteExport\MediaRenderer;

use ArrayObject;
use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Job\JobInterface;

class Unknown implements MediaRendererInterface
{
    public function getMarkdown(
        JobInterface $job,
        ArrayObject $frontMatter,
        MediaRepresentation $media
    ): string {
        return '';
    }
}
