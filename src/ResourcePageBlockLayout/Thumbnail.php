<?php
namespace StaticSiteExport\ResourcePageBlockLayout;

use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Job\JobInterface;

class Thumbnail implements ResourcePageBlockLayoutInterface
{
    public function getMarkup(AbstractResourceEntityRepresentation $resource, JobInterface $job) : string
    {
        return $job->getThumbnailShortcode($resource, 'large');
    }
}
