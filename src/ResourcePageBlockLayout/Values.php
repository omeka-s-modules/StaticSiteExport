<?php
namespace StaticSiteExport\ResourcePageBlockLayout;

use ArrayObject;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Job\JobInterface;

class Values implements ResourcePageBlockLayoutInterface
{
    public function getMarkdown(
        JobInterface $job,
        AbstractResourceEntityRepresentation $resource,
        ArrayObject $frontMatterPage,
        ArrayObject $frontMatterBlock
    ): string {
        $allValues = $resource->values();
        if (!$allValues) {
            return '';
        }
        $markdown = [sprintf("## %s\n", $job->translate('Values'))];
        foreach ($allValues as $term => $valueData) {
            $property = $valueData['property'];
            $altLabel = $valueData['alternate_label'];
            $altComment = $valueData['alternate_comment'];
            $markdown[] = sprintf("%s", $altLabel ?? $job->translate($property->label()));
            foreach ($valueData['values'] as $value) {
                $dataType = $job->get('StaticSiteExport\DataTypeManager')->get($value->type());
                $markdown[] = sprintf(': %s', $dataType->getMarkdown($job, $value, $frontMatterPage, $frontMatterBlock));
            }
            $markdown[] = '';
        }
        return implode("\n", $markdown);
    }
}
