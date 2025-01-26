<?php
namespace StaticSiteExport\MediaRenderer;

use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Job\JobInterface;

class Unknown implements MediaRendererInterface
{
    public function getMarkup(MediaRepresentation $media, JobInterface $job) : string
    {
        return '';
    }
}
