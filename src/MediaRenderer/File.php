<?php
namespace StaticSiteExport\MediaRenderer;

use ArrayObject;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Job\JobInterface;

class File implements MediaRendererInterface
{
    public function getMarkdown(
        JobInterface $job,
        ArrayObject $frontMatter,
        MediaRepresentation $media
    ): string {
        $fileRendererManager = $fileRenderer = $job->get('StaticSiteExport\FileRendererManager');
        try {
            $fileRenderer = $fileRendererManager->get($media->mediaType());
        } catch (ServiceNotFoundException $e) {
            try {
                $fileRenderer = $fileRendererManager->get($media->extension());
            } catch (ServiceNotFoundException $e) {
                $fileRenderer = $fileRendererManager->get('thumbnail');
            }
        }
        return $fileRenderer->getMarkdown($job, $frontMatter, $media);
    }
}
