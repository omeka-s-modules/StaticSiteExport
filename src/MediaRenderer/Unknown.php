<?php
namespace StaticSiteExport\MediaRenderer;

use ArrayObject;
use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Job\JobInterface;

class Unknown implements MediaRendererInterface
{
    public function getMarkdown(
        MediaRepresentation $media,
        JobInterface $job,
        ArrayObject $frontMatter
    ): string {
        return '';
    }
}
