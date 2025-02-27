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
            return sprintf(
                '{{< omeka-figure
                    type="image"
                    filePage="/media/%s"
                    fileResource="file"
                    imgPage="/assets/%s"
                    imgResource="file"
                    linkPage="/media/%s"
                    linkResource="file"
                >}}',
                $media->id(),
                $media->thumbnail()->id(),
                $media->id(),
            );
        }
        if ($media->hasThumbnails()) {
            return sprintf(
                '{{< omeka-figure
                    type="image"
                    filePage="/media/%s"
                    fileResource="file"
                    imgPage="/media/%s"
                    imgResource="thumbnail_large"
                    linkPage="/media/%s"
                    linkResource="file"
                >}}',
                $media->id(),
                $media->id(),
                $media->id()
            );
        }
        $thumbnailPaths = [
            'audio' => '/thumbnails/audio.png',
            'image' => '/thumbnails/image.png',
            'video' => '/thumbnails/video.png',
        ];
        $topLevelType = strstr((string) $media->mediaType(), '/', true);
        $fileThumbnailPath = $thumbnailPaths[$topLevelType] ?? 'thumbnails/default.png';
        return sprintf(
            '{{< omeka-figure
                type="image"
                filePage="/media/%s"
                fileResource="file"
                imgResource="%s"
                linkPage="/media/%s"
                linkResource="file"
            >}}',
            $media->id(),
            $fileThumbnailPath,
            $media->id(),
        );
    }
}
