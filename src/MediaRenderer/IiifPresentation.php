<?php
namespace StaticSiteExport\MediaRenderer;

use ArrayObject;
use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Job\JobInterface;

class IiifPresentation implements MediaRendererInterface
{
    public function getMarkdown(
        MediaRepresentation $media,
        JobInterface $job,
        ArrayObject $frontMatter
    ): string {
        $frontMatter['js'][] = 'js/node_modules/mirador/dist/mirador.min.js';
        $frontMatter['js'][] = 'js/omeka-iiif-viewer.js';
        return sprintf('{{< omeka-iiif-viewer manifestId="%s" >}}', $media->source());
    }
}
