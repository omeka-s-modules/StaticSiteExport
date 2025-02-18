<?php
namespace StaticSiteExport\ResourcePageBlockLayout;

use ArrayObject;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Job\JobInterface;

class MediaList implements ResourcePageBlockLayoutInterface
{
    public function getMarkdown(
        JobInterface $job,
        ArrayObject $frontMatter,
        AbstractResourceEntityRepresentation $resource
    ): string {
        $media = $resource->media();
        if (!$media) {
            return '';
        }
        $block = [sprintf("## Media\n")];
        foreach ($media as $media) {
            $block[] = sprintf(
                '- %s',
                $job->getLinkMarkdown($media, [
                    'thumbnailType' => 'square',
                    'thumbnailHeight' => 40,
                ])
            );
        }
        return implode("\n", $block);
    }
}
