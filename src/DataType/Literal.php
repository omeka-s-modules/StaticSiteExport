<?php
namespace StaticSiteExport\DataType;

use ArrayObject;
use Omeka\Api\Representation\ValueRepresentation;
use Omeka\Job\JobInterface;

class Literal implements DataTypeInterface
{
    public function getMarkdown(
        JobInterface $job,
        ArrayObject $frontMatter,
        ValueRepresentation $value
    ): string {
        return sprintf('{{< omeka-literal >}}%s{{< /omeka-literal >}}', $value->value());
    }
}
