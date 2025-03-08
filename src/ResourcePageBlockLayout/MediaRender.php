<?php
namespace StaticSiteExport\ResourcePageBlockLayout;

use ArrayObject;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Job\JobInterface;

class MediaRender implements ResourcePageBlockLayoutInterface
{
    public function getMarkdown(
        JobInterface $job,
        AbstractResourceEntityRepresentation $resource,
        ArrayObject $frontMatterPage,
        ArrayObject $frontMatterBlock
    ): string {
        $media = $resource->primaryMedia();
        if (!$media) {
            // Account for resources with asset thumbnails.
            return $job->getThumbnailShortcode($resource, ['thumbnailType' => 'large']);
        }
        $mediaRenderer = $job->get('StaticSiteExport\MediaRendererManager')->get($media->renderer());
        return $mediaRenderer->getMarkdown($job, $media, $frontMatterPage, $frontMatterBlock);
    }
}
