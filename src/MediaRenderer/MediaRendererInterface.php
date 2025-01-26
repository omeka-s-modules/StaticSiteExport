<?php
namespace StaticSiteExport\MediaRenderer;

use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Job\JobInterface;

interface MediaRendererInterface
{
    public function getMarkup(MediaRepresentation $media, JobInterface $job) : string;
}
