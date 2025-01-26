<?php
namespace StaticSiteExport\DataType;

use Omeka\Api\Representation\ValueRepresentation;
use Omeka\Job\JobInterface;

interface DataTypeInterface
{
    public function getMarkup(ValueRepresentation $value, JobInterface $job) : string;
}
