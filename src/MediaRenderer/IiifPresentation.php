<?php
namespace StaticSiteExport\MediaRenderer;

use ArrayObject;
use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Job\JobInterface;

class IiifPresentation implements MediaRendererInterface
{
    public function getMarkdown(
        JobInterface $job,
        MediaRepresentation $media,
        ArrayObject $frontMatterPage,
        ArrayObject $frontMatterBlock
    ): string {
        $frontMatterPage['js'][] = 'vendor/mirador/mirador.min.js';
        $frontMatterPage['js'][] = 'js/iiif-viewer.js';
        return sprintf('{{< omeka-iiif-viewer manifestId="%s" >}}', $media->source());
    }
}
