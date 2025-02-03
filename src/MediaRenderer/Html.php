<?php
namespace StaticSiteExport\MediaRenderer;

use ArrayObject;
use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Job\JobInterface;

class Html implements MediaRendererInterface
{
    public function getMarkdown(
        MediaRepresentation $media,
        JobInterface $job,
        ArrayObject $frontMatter
    ): string {
        $data = $media->mediaData();
        return sprintf('{{< omeka-html >}}%s{{< /omeka-html >}}', $data['html']);
    }
}
