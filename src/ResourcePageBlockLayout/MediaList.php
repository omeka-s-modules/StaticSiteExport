<?php
namespace StaticSiteExport\ResourcePageBlockLayout;

use ArrayObject;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Job\JobInterface;

class MediaList implements ResourcePageBlockLayoutInterface
{
    public function getMarkdown(
        JobInterface $job,
        AbstractResourceEntityRepresentation $resource,
        ArrayObject $frontMatterPage,
        ArrayObject $frontMatterBlock
    ): string {
        $media = $resource->media();
        if (!$media) {
            return '';
        }
        $block = [sprintf("## %s\n", $job->translate('Media'))];
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
