<?php
namespace StaticSiteExport\ResourcePageBlockLayout;

use ArrayObject;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Job\JobInterface;

class MediaEmbeds implements ResourcePageBlockLayoutInterface
{
    public function getMarkdown(
        JobInterface $job,
        ArrayObject $frontMatter,
        AbstractResourceEntityRepresentation $resource
    ): string {
        $mediaRenderBlock = $job->get('StaticSiteExport\ResourcePageBlockLayoutManager')->get('mediaRender');
        $block = [];
        foreach ($resource->media() as $media) {
            $block[] = $mediaRenderBlock->getMarkdown($job, $frontMatter, $media);
        }
        return implode("\n", $block);
    }
}
