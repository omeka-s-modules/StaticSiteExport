<?php
namespace StaticSiteExport\DataType;

use ArrayObject;
use Omeka\Api\Representation\ValueRepresentation;
use Omeka\Job\JobInterface;

class Uri implements DataTypeInterface
{
    public function getMarkdown(
        JobInterface $job,
        ArrayObject $frontMatter,
        ValueRepresentation $value
    ): string {
        $uri = $value->uri();
        $label = $value->value();
        return sprintf("[%s](%s)", $label ? $label : $uri, $uri);
    }
}
