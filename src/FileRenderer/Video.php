<?php
namespace StaticSiteExport\FileRenderer;

use ArrayObject;
use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Job\JobInterface;

class Video implements FileRendererInterface
{
    public function getMarkdown(
        JobInterface $job,
        MediaRepresentation $media,
        ArrayObject $frontMatterPage,
        ArrayObject $frontMatterBlock
    ): string {
        return $job->getFigureShortcode([
            'class' => 'media-render file',
            'type' => "video",
            'filePage' => sprintf("/media/%s", $media->id()),
            'fileResource' => "file",
            'imgResource' => "/thumbnails/video.png",
            'linkPage' => sprintf("/media/%s", $media->id()),
            'linkResource' => "file",
        ]);
    }
}
