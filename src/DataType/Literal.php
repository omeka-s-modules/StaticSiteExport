<?php
namespace StaticSiteExport\DataType;

use Omeka\Api\Representation\ValueRepresentation;
use Omeka\Job\JobInterface;

class Literal implements DataTypeInterface
{
    public function getMarkup(ValueRepresentation $value, JobInterface $job) : string
    {
        return sprintf('{{< omeka-literal >}}%s{{< /omeka-literal >}}', $value->value());
    }
}
