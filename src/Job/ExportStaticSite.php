<?php
namespace StaticSiteExport\Job;

use DateTime;
use Omeka\Job\AbstractJob;
use Omeka\Job\Exception;
use StaticSiteExport\Entity\StaticSite;
use StaticSiteExport\Module;

class ExportStaticSite extends AbstractJob
{
    protected $staticSite;

    protected $entityManager;

    protected $settings;

    protected $cli;

    protected $logger;

    protected $connection;

    public function perform()
    {
        $this->prepareSiteDirectory();

        // Iterate the site's items.
        $sql = 'SELECT item_site.item_id
            FROM item_site item_site
            INNER JOIN resource resource ON item_site.item_id = resource.id
            WHERE item_site.site_id = :site_id
            ORDER BY resource.created';
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindValue('site_id', $this->getStaticSite()->getSite()->getId());
        $itemIds = $stmt->executeQuery()->fetchFirstColumn();
        foreach (array_chunk($itemIds, 100) as $itemIdsChunk) {
            foreach ($itemIdsChunk as $itemId) {
                // $item = $api->read('items', $itemId);
            }
        }
    }

    protected function prepareSiteDirectory()
    {
        // Set the paths.
        $sitesDirectoryPath = $this->getSettings()->get('static_site_export_sites_directory_path');
        if (!Module::sitesDirectoryPathIsValid($sitesDirectoryPath)) {
            throw new Exception\RuntimeException('Invalid directory path');
        }
        $from = sprintf('%s/modules/StaticSiteExport/data/hugo-template', OMEKA_PATH);
        $to = sprintf('%s/%s', $sitesDirectoryPath, $this->getStaticSite()->getDirectoryName());

        // Copy template to sites directory.
        $command = sprintf(
            '%s -r %s %s',
            $this->getCli()->getCommandPath('cp'),
            escapeshellarg($from),
            escapeshellarg($to)
        );
        $output = $this->getCli()->execute($command);
        if (false === $output) {
            throw new Exception\RuntimeException('Error copying site template to sites directory');
        }

        // Make the necessary directories within the site directory.
        $command = sprintf('mkdir %s/content/items', $to);
        $this->getCli()->execute($command);
    }

    protected function getStaticSite()
    {
        if (null === $this->staticSite) {
            // Validate the static site entity.
            $staticSiteId = $this->getArg('static_site_id');
            if (!is_numeric($staticSiteId)) {
                throw new Exception\RuntimeException('Missing static_site_id');
            }
            $staticSite = $this->getEntityManager()->find(StaticSite::class, $staticSiteId);
            if (null === $staticSite) {
                throw new Exception\RuntimeException('Cannot find static site');
            }
            $this->staticSite = $staticSite;
        }
        return $this->staticSite;
    }

    protected function getEntityManager()
    {
        if (null === $this->entityManager) {
            $this->entityManager = $this->getServiceLocator()->get('Omeka\EntityManager');
        }
        return $this->entityManager;
    }

    protected function getSettings()
    {
        if (null === $this->settings) {
            $this->settings = $this->getServiceLocator()->get('Omeka\Settings');
        }
        return $this->settings;
    }

    protected function getCli()
    {
        if (null === $this->cli) {
            $this->cli = $this->getServiceLocator()->get('Omeka\Cli');
        }
        return $this->cli;
    }

    protected function getLogger()
    {
        if (null === $this->logger) {
            $this->logger = $this->getServiceLocator()->get('Omeka\Logger');
        }
        return $this->logger;
    }

    protected function getConnection()
    {
        if (null === $this->connection) {
            $this->connection = $this->getServiceLocator()->get('Omeka\Connection');
        }
        return $this->connection;
    }
}
