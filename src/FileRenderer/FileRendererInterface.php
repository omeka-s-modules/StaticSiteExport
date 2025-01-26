<?php
namespace StaticSiteExport\FileRenderer;

use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Job\JobInterface;

interface FileRendererInterface
{
    public function getMarkup(MediaRepresentation $media, JobInterface $job) : string;
}
