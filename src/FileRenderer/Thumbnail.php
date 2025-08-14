<?php
namespace StaticSiteExport\FileRenderer;

use ArrayObject;
use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Job\JobInterface;

class Thumbnail implements FileRendererInterface
{
    public function getMarkdown(
        JobInterface $job,
        MediaRepresentation $media,
        ArrayObject $frontMatterPage,
        ArrayObject $frontMatterBlock
    ): string {
        if ($media->thumbnail()) {
            return $job->getFigureShortcode([
                'class' => 'media-render file',
                'type' => "image",
                'filePage' => sprintf("/media/%s", $media->id()),
                'fileResource' => "file",
                'imgPage' => sprintf("/assets/%s", $media->thumbnail()->id()),
                'imgResource' => "file",
                'linkPage' => sprintf("/media/%s", $media->id()),
                'linkResource' => "file",
            ]);
        }
        if ($media->hasThumbnails()) {
            return $job->getFigureShortcode([
                'class' => 'media-render file',
                'type' => "image",
                'filePage' => sprintf("/media/%s", $media->id()),
                'fileResource' => "file",
                'imgPage' => sprintf("/media/%s", $media->id()),
                'imgResource' => "thumbnail_large",
                'linkPage' => sprintf("/media/%s", $media->id()),
                'linkResource' => "file",
            ]);
        }
        $thumbnailPaths = [
            'audio' => '/thumbnails/audio.png',
            'image' => '/thumbnails/image.png',
            'video' => '/thumbnails/video.png',
        ];
        $topLevelType = strstr((string) $media->mediaType(), '/', true);
        $fileThumbnailPath = $thumbnailPaths[$topLevelType] ?? '/thumbnails/default.png';
        return $job->getFigureShortcode([
            'class' => 'media-render file',
            'type' => "image",
            'filePage' => sprintf("/media/%s", $media->id()),
            'fileResource' => "file",
            'imgResource' => $fileThumbnailPath,
            'linkPage' => sprintf("/media/%s", $media->id()),
            'linkResource' => "file",
        ]);
    }
}
