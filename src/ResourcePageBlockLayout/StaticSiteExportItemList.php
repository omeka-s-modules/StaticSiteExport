<?php
namespace StaticSiteExport\ResourcePageBlockLayout;

use ArrayObject;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Job\JobInterface;

class StaticSiteExportItemList implements ResourcePageBlockLayoutInterface
{
    public function getMarkdown(
        JobInterface $job,
        AbstractResourceEntityRepresentation $resource,
        ArrayObject $frontMatterPage,
        ArrayObject $frontMatterBlock
    ): string {
        $items = $job->get('Omeka\ApiManager')->search('items', [
            'item_set_id' => $resource->id(),
            'site_id' => $job->getStaticSite()->site()->id(),
        ])->getContent();
        if (!$items) {
            return '';
        }
        $markdown .= "## Items\n";
        foreach ($items as $item) {
            $markdown .= sprintf(
                "- %s\n",
                $job->getLinkMarkdown($item, [
                    'thumbnailType' => 'square',
                    'thumbnailHeight' => 40,
                ])
            );
        }
        return $markdown;
    }
}
