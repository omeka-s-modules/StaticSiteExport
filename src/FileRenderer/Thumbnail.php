<?php
namespace StaticSiteExport\FileRenderer;

use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Job\JobInterface;

class Thumbnail implements FileRendererInterface
{
    public function getMarkup(MediaRepresentation $media, JobInterface $job) : string
    {
        if ($media->hasThumbnails()) {
            return sprintf(
                '{{< omeka-figure
                    type="image"
                    linkPage="/media/%s"
                    linkResource="file"
                    imgPage="/media/%s"
                    imgResource="thumbnail_large"
                >}}',
                $media->id(),
                $media->id()
            );
        }
        $thumbnailPaths = [
            'audio' => 'thumbnails/audio.png',
            'image' => 'thumbnails/image.png',
            'video' => 'thumbnails/video.png',
        ];
        $topLevelType = strstr((string) $media->mediaType(), '/', true);
        $fileThumbnailPath = $thumbnailPaths[$topLevelType] ?? 'thumbnails/default.png';
        return sprintf(
            '{{< omeka-figure
                type="image"
                linkPage="/media/%s"
                linkResource="file"
                imgResource="%s"
            >}}',
            $media->id(),
            $fileThumbnailPath,
        );
    }
}
