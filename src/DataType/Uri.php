<?php
namespace StaticSiteExport\DataType;

use ArrayObject;
use Omeka\Api\Representation\ValueRepresentation;
use Omeka\Job\JobInterface;

class Uri implements DataTypeInterface
{
    public function getMarkdown(
        ValueRepresentation $value,
        JobInterface $job,
        ArrayObject $frontMatter
    ): string {
        $uri = $value->uri();
        $label = $value->value();
        return sprintf("[%s](%s)", $label ? $label : $uri, $uri);
    }
}
