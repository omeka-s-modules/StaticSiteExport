<?php
namespace StaticSiteExport\DataType;

use Omeka\Api\Representation\ValueRepresentation;
use Omeka\Job\JobInterface;

class Uri implements DataTypeInterface
{
    public function getMarkup(ValueRepresentation $value, JobInterface $job) : string
    {
        $uri = $value->uri();
        $label = $value->value();
        return sprintf("[%s](%s)", $label ? $label : $uri, $uri);
    }
}
