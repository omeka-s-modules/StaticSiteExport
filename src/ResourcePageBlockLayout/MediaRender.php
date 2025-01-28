<?php
namespace StaticSiteExport\ResourcePageBlockLayout;

use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Job\JobInterface;

class MediaRender implements ResourcePageBlockLayoutInterface
{
    public function getMarkup(AbstractResourceEntityRepresentation $resource, JobInterface $job) : string
    {
        $media = $resource->primaryMedia();
        if (!$media) {
            // Account for resources with asset thumbnails.
            return $job->getThumbnailShortcode($resource, 'large');
        }
        $mediaRenderer = $job->get('StaticSiteExport\MediaRendererManager')->get($media->renderer());
        return $mediaRenderer->getMarkup($media, $job);
    }
}
