<?php
namespace StaticSiteExport\MediaRenderer;

use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Job\JobInterface;

class Youtube implements MediaRendererInterface
{
    public function getMarkup(MediaRepresentation $media, JobInterface $job) : string
    {
        $data = $media->mediaData();
        return sprintf('{{< youtube id="%s" >}}', $data['id']);
    }
}
