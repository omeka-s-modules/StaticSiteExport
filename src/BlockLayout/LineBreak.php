<?php
namespace StaticSiteExport\BlockLayout;

use ArrayObject;
use Omeka\Api\Representation\SitePageBlockRepresentation;
use Omeka\Job\JobInterface;

class LineBreak implements BlockLayoutInterface
{
    public function getMarkdown(
        JobInterface $job,
        SitePageBlockRepresentation $block,
        ArrayObject $frontMatterPage,
        ArrayObject $frontMatterBlock
    ): string {
        return sprintf('{{< omeka-html >}}<div class="break %s"></div>{{< /omeka-html >}}', $block->dataValue('break_type', ''));
    }
}
