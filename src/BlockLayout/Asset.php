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
            $markdown[] = sprintf(
                '{{< omeka-figure
                    type="image"
                    linkPage="/assets/%s"
                    linkResource="file"
                    imgPage="/assets/%s"
                    imgResource="file"
                    caption="%s"
                >}}',
                $asset->id(),
                $asset->id(),
                $attachmentData['caption'] ? $attachmentData['caption'] : ''
            );
        }
        return implode("\n", $markdown);
    }
}
