<?php
namespace StaticSiteExport\Job;

use DateTime;
use Omeka\Job\AbstractJob;
use Omeka\Job\Exception;
use StaticSiteExport\Entity\StaticSite;

class ExportStaticSite extends AbstractJob
{
    public function perform()
    {
        $services = $this->getServiceLocator();
        $em = $services->get('Omeka\EntityManager');

        // Validate the visualization.
        $staticSiteId = $this->getArg('static_site_id');
        if (!is_numeric($staticSiteId)) {
            throw new Exception\RuntimeException('Missing static_site_id');
        }
        $entity = $em->find(StaticSite::class, $staticSiteId);
        if (null === $entity) {
            throw new Exception\RuntimeException('Cannot find static site');
        }

        // @todo: export static site
    }
}
