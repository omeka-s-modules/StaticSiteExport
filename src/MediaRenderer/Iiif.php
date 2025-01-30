<?php
namespace StaticSiteExport\MediaRenderer;

use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Job\JobInterface;

class Iiif implements MediaRendererInterface
{
    public function getMarkup(MediaRepresentation $media, JobInterface $job) : string
    {
        return sprintf('{{< omeka-iiif-image page="/media/%s" >}}', $media->id());
    }
}
