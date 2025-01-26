<?php
namespace StaticSiteExport\MediaRenderer;

use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Job\JobInterface;

class Html implements MediaRendererInterface
{
    public function getMarkup(MediaRepresentation $media, JobInterface $job) : string
    {
        $data = $media->mediaData();
        return sprintf('{{< omeka-html >}}%s{{< /omeka-html >}}', $data['html']);
    }
}
