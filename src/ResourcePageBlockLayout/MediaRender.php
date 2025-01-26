<?php
namespace StaticSiteExport\ResourcePageBlockLayout;

use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Job\JobInterface;

class MediaRender implements ResourcePageBlockLayoutInterface
{
    public function getMarkup(AbstractResourceEntityRepresentation $resource, JobInterface $job) : string
    {
        $mediaRenderer = $job->get('StaticSiteExport\MediaRendererManager')->get($resource->renderer());
        return $mediaRenderer->getMarkup($resource, $job);
    }
}
