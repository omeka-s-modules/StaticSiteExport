<?php
namespace StaticSiteExport\Api\Adapter;

use Doctrine\ORM\QueryBuilder;
use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\Api\Request;
use Omeka\Entity\EntityInterface;
use Omeka\Stdlib\ErrorStore;
use StaticSiteExport\Api\Representation\StaticSiteRepresentation;
use StaticSiteExport\Entity\StaticSite;

class StaticSiteAdapter extends AbstractEntityAdapter
{
    public function getResourceName()
    {
        return 'static_site_export_static_sites';
    }

    public function getRepresentationClass()
    {
        return StaticSiteRepresentation::class;
    }

    public function getEntityClass()
    {
        return StaticSite::class;
    }

    public function buildQuery(QueryBuilder $qb, array $query)
    {
    }

    public function validateRequest(Request $request, ErrorStore $errorStore)
    {
        if (Request::CREATE !== $request->getOperation()) {
            $errorStore->addError('o-module-static-site', 'Cannot update a static site'); // @translate
        }

        $data = $request->getContent();
        if (!isset($data['o:site']['o:id'])) {
            $errorStore->addError('o:site', 'A site must have an ID'); // @translate
        }
    }

    public function hydrate(Request $request, EntityInterface $entity, ErrorStore $errorStore)
    {
        $this->hydrateOwner($request, $entity);

        $siteData = $request->getValue('o:site');
        $site = $this->getAdapter('sites')->findEntity($siteData['o:id']);
        $entity->setSite($site);

        if ($this->shouldHydrate($request, 'o:label')) {
            $label = trim($request->getValue('o:label', ''));
            $entity->setLabel($label);
        }
        if ($this->shouldHydrate($request, 'o:data')) {
            $entity->setData($request->getValue('o:data'));
        }
    }

    public function validateEntity(EntityInterface $entity, ErrorStore $errorStore)
    {
        if (!is_string($entity->getLabel()) || '' === $entity->getLabel()) {
            $errorStore->addError('o:label', 'An static site must have a label'); // @translate
        }
    }
}