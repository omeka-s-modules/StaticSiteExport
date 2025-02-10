<?php
namespace StaticSiteExport\FileRenderer;

use ArrayObject;
use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Job\JobInterface;

class Thumbnail implements FileRendererInterface
{
    public function getMarkdown(
        JobInterface $job,
        ArrayObject $frontMatter,
        MediaRepresentation $media
    ): string {
        if ($media->thumbnail()) {
            return sprintf(
                '{{< omeka-figure
                    type="image"
                    linkPage="/media/%s"
                    linkResource="file"
                    imgPage="/assets/%s"
                    imgResource="file"
                >}}',
                $media->id(),
                $media->thumbnail()->id()
            );
        }
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
