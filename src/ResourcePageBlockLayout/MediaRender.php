<?php
namespace StaticSiteExport\ResourcePageBlockLayout;

use ArrayObject;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Job\JobInterface;

class MediaRender implements ResourcePageBlockLayoutInterface
{
    public function getMarkdown(
        AbstractResourceEntityRepresentation $resource,
        JobInterface $job,
        ArrayObject $frontMatter
    ): string {
        $media = $resource->primaryMedia();
        if (!$media) {
            // Account for resources with asset thumbnails.
            return $job->getThumbnailShortcode($resource, 'large');
        }
        $mediaRenderer = $job->get('StaticSiteExport\MediaRendererManager')->get($media->renderer());
        return $mediaRenderer->getMarkdown($media, $job, $frontMatter);
    }
}
