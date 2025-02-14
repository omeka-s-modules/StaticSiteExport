<?php
namespace StaticSiteExport\MediaRenderer;

use ArrayObject;
use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Job\JobInterface;

class Iiif implements MediaRendererInterface
{
    public function getMarkdown(
        JobInterface $job,
        ArrayObject $frontMatter,
        MediaRepresentation $media
    ): string {
        $frontMatter['js'][] = 'vendor/openseadragon/openseadragon.min.js';
        $frontMatter['js'][] = 'js/iiif-image.js';
        return sprintf('{{< omeka-iiif-image page="/media/%s" >}}', $media->id());
    }
}
