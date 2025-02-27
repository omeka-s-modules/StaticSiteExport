<?php
namespace StaticSiteExport\BlockLayout;

use ArrayObject;
use Omeka\Api\Representation\SitePageBlockRepresentation;
use Omeka\Job\JobInterface;

class Asset implements BlockLayoutInterface
{
    public function getMarkdown(
        JobInterface $job,
        SitePageBlockRepresentation $block,
        ArrayObject $frontMatterPage,
        ArrayObject $frontMatterBlock
    ): string {
        $api = $job->get('Omeka\ApiManager');
        $markdown = [];
        foreach ($block->data() as $attachmentData) {
            $asset = $api->read('assets', $attachmentData['id'])->getContent();
            $caption = $attachmentData['caption'] ? $job->escape(['"'], $attachmentData['caption']) : '';
            $markdown[] = sprintf(
                '{{< omeka-figure
                    type="image"
                    filePage="/assets/%s"
                    fileResource="file"
                    imgPage="/assets/%s"
                    imgResource="file"
                    linkPage="/pages/welcome"
                    caption="%s"
                >}}',
                $asset->id(),
                $asset->id(),
                $caption
            );
        }
        return implode("\n", $markdown);
    }
}
