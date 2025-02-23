<?php
namespace StaticSiteExport\MediaRenderer;

use ArrayObject;
use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Job\JobInterface;

class Oembed implements MediaRendererInterface
{
    public function getMarkdown(
        JobInterface $job,
        MediaRepresentation $media,
        ArrayObject $frontMatterPage,
        ArrayObject $frontMatterBlock
    ): string {
        $data = $media->mediaData();
        if (isset($data['html'])) {
            return sprintf('{{< omeka-html >}}%s{{< /omeka-html >}}', $data['html']);
        }
        $type = $data['type'] ?? null;
        if ('photo' === $type && isset($data['url'])) {
            return sprintf(
                '{{< figure src="%s" width="%s" height="%s" alt="%s" >}}',
                $data['url'],
                $data['width'] ?? '',
                $data['height'] ?? '',
                $data['title'] ?? ''
            );
        }
    }
}
