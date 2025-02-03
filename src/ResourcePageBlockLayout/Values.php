<?php
namespace StaticSiteExport\ResourcePageBlockLayout;

use ArrayObject;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Job\JobInterface;

class Values implements ResourcePageBlockLayoutInterface
{
    public function getMarkdown(
        AbstractResourceEntityRepresentation $resource,
        JobInterface $job,
        ArrayObject $frontMatter
    ): string {
        $allValues = $resource->values();
        if (!$allValues) {
            return '';
        }
        $block = [sprintf("## Values\n")];
        foreach ($allValues as $term => $valueData) {
            $property = $valueData['property'];
            $altLabel = $valueData['alternate_label'];
            $altComment = $valueData['alternate_comment'];
            $block[] = sprintf("%s", $altLabel ?? $property->label());
            foreach ($valueData['values'] as $value) {
                $dataType = $job->get('StaticSiteExport\DataTypeManager')->get($value->type());
                $block[] = sprintf(': %s', $dataType->getMarkdown($value, $job, $frontMatter));
            }
            $block[] = '';
        }
        return implode("\n", $block);
    }
}
