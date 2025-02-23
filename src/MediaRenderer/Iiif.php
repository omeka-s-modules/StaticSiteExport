<?php
namespace StaticSiteExport\MediaRenderer;

use ArrayObject;
use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Job\JobInterface;

class Iiif implements MediaRendererInterface
{
    public function getMarkdown(
        JobInterface $job,
        MediaRepresentation $media,
        ArrayObject $frontMatterPage,
        ArrayObject $frontMatterBlock
    ): string {
        $frontMatterPage['js'][] = 'vendor/openseadragon/openseadragon.min.js';
        $frontMatterPage['js'][] = 'js/iiif-image.js';
        return sprintf('{{< omeka-iiif-image page="/media/%s" >}}', $media->id());
    }
}
