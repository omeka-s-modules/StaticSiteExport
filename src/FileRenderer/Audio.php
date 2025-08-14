<?php
namespace StaticSiteExport\FileRenderer;

use ArrayObject;
use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Job\JobInterface;

class Audio implements FileRendererInterface
{
    public function getMarkdown(
        JobInterface $job,
        MediaRepresentation $media,
        ArrayObject $frontMatterPage,
        ArrayObject $frontMatterBlock
    ): string {
        return $job->getFigureShortcode([
            'class' => 'media-render file',
            'type' => 'audio',
            'filePage' => sprintf('/media/%s', $media->id()),
            'fileResource' => 'file',
            'imgResource' => '/thumbnails/audio.png',
            'linkPage' => sprintf('/media/%s', $media->id()),
            'linkResource' => 'file',
        ]);
    }
}
