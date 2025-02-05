<?php
namespace StaticSiteExport\MediaRenderer;

use ArrayObject;
use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Job\JobInterface;

class Iiif implements MediaRendererInterface
{
    public function getMarkdown(
        MediaRepresentation $media,
        JobInterface $job,
        ArrayObject $frontMatter
    ): string {
        $frontMatter['js'][] = 'js/vendor/openseadragon/openseadragon.min.js';
        $frontMatter['js'][] = 'js/omeka-iiif-image.js';
        return sprintf('{{< omeka-iiif-image page="/media/%s" >}}', $media->id());
    }
}
