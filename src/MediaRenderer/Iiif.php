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
        $frontMatter['js'][] = 'js/node_modules/openseadragon/build/openseadragon/openseadragon.min.js';
        return sprintf('{{< omeka-iiif-image page="/media/%s" >}}', $media->id());
    }
}
