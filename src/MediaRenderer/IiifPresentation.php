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
        $frontMatter['js'][] = 'js/mirador/mirador.min.js';
        $frontMatter['js'][] = 'js/omeka/iiif-viewer.js';
        return sprintf('{{< omeka-iiif-viewer manifestId="%s" >}}', $media->source());
    }
}
