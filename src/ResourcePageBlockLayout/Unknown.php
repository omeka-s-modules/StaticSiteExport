<?php
namespace StaticSiteExport\ResourcePageBlockLayout;

use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Job\JobInterface;

class Unknown implements ResourcePageBlockLayoutInterface
{
    public function getMarkup(AbstractResourceEntityRepresentation $resource, JobInterface $job) : string
    {
        return '';
    }
}
