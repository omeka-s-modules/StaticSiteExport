<?php
namespace StaticSiteExport\ResourcePageBlockLayout;

use ArrayObject;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Job\JobInterface;

class LinkedResources implements ResourcePageBlockLayoutInterface
{
    public function getMarkdown(
        AbstractResourceEntityRepresentation $resource,
        JobInterface $job,
        ArrayObject $frontMatter
    ): string {
        $resourceEntity = $job->get('Omeka\EntityManager')->find('Omeka\Entity\Resource', $resource->id());
        $adapter = $job->get('Omeka\ApiAdapterManager')->get('items');
        $subjectValues = $adapter->getSubjectValues($resourceEntity);
        if (!$subjectValues) {
            return '';
        }
        // Build a linked resources array to simplify markdown generation.
        $linkedResourcesProperties = [];
        foreach ($subjectValues as $subjectValue) {
            $propertyId = $subjectValue['property_id'];
            if (!isset($linkedResourcesProperties[$propertyId])) {
                $linkedResourcesProperties[$propertyId] = [
                    'property_label' => $subjectValue['order_by_label'],
                    'resources' => [],
                ];
            }
            $resource = $subjectValue['val']->getResource();
            $linkedResourcesProperties[$propertyId]['resources'][] = $adapter->getRepresentation($resource);
        }
        // Generate the linked resources markdown.
        $block = [sprintf("## Linked resources\n")];
        foreach ($linkedResourcesProperties as $linkedResourcesProperty) {
            $block[] = sprintf("%s", $linkedResourcesProperty['property_label']);
            foreach ($linkedResourcesProperty['resources'] as $linkedResource) {
                if (in_array($linkedResource->id(), $job->getItemIds())) {
                    $contentDirectory = 'items';
                } elseif (in_array($linkedResource->id(), $job->getItemSetIds())) {
                    $contentDirectory = 'item-sets';
                } else {
                    continue; // Resource not in site.
                }
                // @todo: Add media linked resources.
                $block[] = sprintf(
                    ': [%s]({{< ref "/%s/%s" >}} "%s")',
                    $job->escape(['[', ']'], $linkedResource->displayTitle()),
                    $contentDirectory,
                    $linkedResource->id(),
                    $job->escape(['"'], $linkedResource->displayTitle()),
                );
            }
            $block[] = '';
        }
        return implode("\n", $block);

    }
}
