<?php
namespace StaticSiteExport\MediaRenderer;

use ArrayObject;
use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Job\JobInterface;

class IiifPresentation implements MediaRendererInterface
{
    public function getMarkdown(
        JobInterface $job,
        ArrayObject $frontMatter,
        MediaRepresentation $media
    ): string {
        $frontMatter['js'][] = 'vendor/mirador/mirador.min.js';
        $frontMatter['js'][] = 'js/iiif-viewer.js';
        return sprintf('{{< omeka-iiif-viewer manifestId="%s" >}}', $media->source());
    }
}
