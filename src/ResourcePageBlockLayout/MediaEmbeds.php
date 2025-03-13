<?php
namespace StaticSiteExport\ResourcePageBlockLayout;

use ArrayObject;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Job\JobInterface;

class MediaEmbeds implements ResourcePageBlockLayoutInterface
{
    public function getMarkdown(
        JobInterface $job,
        AbstractResourceEntityRepresentation $resource,
        ArrayObject $frontMatterPage,
        ArrayObject $frontMatterBlock
    ): string {
        $medias = $resource->media();
        if (!$medias) {
            return '';
        }
        $mediaRenderBlock = $job->get('StaticSiteExport\ResourcePageBlockLayoutManager')->get('mediaRender');
        $markdown = [];
        foreach ($medias as $media) {
            $markdown[] = $mediaRenderBlock->getMarkdown($job, $media, $frontMatterPage, $frontMatterBlock);
        }
        return implode("\n", $markdown);
    }
}
