<?php
namespace StaticSiteExport\ResourcePageBlockLayout;

use ArrayObject;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Job\JobInterface;

class MediaEmbeds implements ResourcePageBlockLayoutInterface
{
    public function getMarkdown(
        AbstractResourceEntityRepresentation $resource,
        JobInterface $job,
        ArrayObject $frontMatter
    ): string {
        $mediaRenderBlock = $job->get('StaticSiteExport\ResourcePageBlockLayoutManager')->get('mediaRender');
        $block = [];
        foreach ($resource->media() as $media) {
            $block[] = $mediaRenderBlock->getMarkdown($media, $job, $frontMatter);
        }
        return implode("\n", $block);
    }
}
