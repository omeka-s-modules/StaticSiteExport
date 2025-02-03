<?php
namespace StaticSiteExport\DataType;

use ArrayObject;
use Omeka\Api\Representation\ValueRepresentation;
use Omeka\Job\JobInterface;

class Literal implements DataTypeInterface
{
    public function getMarkdown(
        ValueRepresentation $value,
        JobInterface $job,
        ArrayObject $frontMatter
    ): string {
        return sprintf('{{< omeka-literal >}}%s{{< /omeka-literal >}}', $value->value());
    }
}
