<?php
namespace StaticSiteExport\DataType;

use ArrayObject;
use Omeka\Api\Representation\ValueRepresentation;
use Omeka\Job\JobInterface;

class Literal implements DataTypeInterface
{
    public function getMarkdown(
        JobInterface $job,
        ValueRepresentation $value,
        ArrayObject $frontMatterPage,
        ArrayObject $frontMatterBlock
    ): string {
        return sprintf('{{< omeka-literal lang="%s" >}}%s{{< /omeka-literal >}}', $value->lang(), $value->value());
    }
}
