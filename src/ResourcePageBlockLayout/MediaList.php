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
        return sprintf(
            "#### %s\n%s",
            $job->translate('Media'),
            $job->getMediaListMarkdown($resource)
        );
    }
}
