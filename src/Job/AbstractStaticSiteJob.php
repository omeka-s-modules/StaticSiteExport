<?php
namespace StaticSiteExport\Job;

use Omeka\Job\AbstractJob;
use Omeka\Job\Exception;
use StaticSiteExport\Api\Representation\StaticSiteRepresentation;
use StaticSiteExport\Module;

abstract class AbstractStaticSiteJob extends AbstractJob
{
    /**
     * @var StaticSiteRepresentation
     */
    protected $staticSite;

    /**
     * @var string
     */
    protected $sitesDirectoryPath;

    /**
     * @var string
     */
    protected $siteDirectoryPath;

    /**
     * Get a named service. Proxy to $this->getServiceLocator().
     */
    public function get(string $serviceName)
    {
        return $this->getServiceLocator()->get($serviceName);
    }

    /**
     * Delete the static site directory.
     */
    public function deleteSiteDirectory(): void
    {
        $path = $this->getSiteDirectoryPath();
        if (is_dir($path) && is_writable($path)) {
            $command = sprintf(
                '%s -r %s',
                $this->get('Omeka\Cli')->getCommandPath('rm'),
                escapeshellarg($path)
            );
            $this->execute($command);
        }
    }

    /**
     * Delete the static site server ZIP file.
     */
    public function deleteSiteZip(): void
    {
        $path = sprintf('%s.zip', $this->getSiteDirectoryPath());
        if (is_file($path) && is_writable($path)) {
            $command = sprintf(
                '%s -r %s',
                $this->get('Omeka\Cli')->getCommandPath('rm'),
                escapeshellarg($path)
            );
            $this->execute($command);
        }
    }

    /**
     * Execute a command.
     */
    public function execute(string $command): void
    {
        $output = $this->get('Omeka\Cli')->execute($command);
        if (false === $output) {
            // Stop the job. Note that the Cli service already logged an error.
            throw new Exception\RuntimeException;
        }
        // Log every command output if configured to do so. Note that this is
        // off by default because for large sites the log will likely grow to
        // surpass the memory limit.
        $logCommands = $this->get('Config')['static_site_export']['log_commands'];
        if ($logCommands) {
            $this->get('Omeka\Logger')->notice(sprintf("Output for command: %s\n%s", $command, $output));
        }
    }

    /**
     * Get the directory path where the static sites are created.
     */
    public function getSitesDirectoryPath(): string
    {
        if (null === $this->sitesDirectoryPath) {
            $sitesDirectoryPath = $this->get('Omeka\Settings')->get('static_site_export_sites_directory_path');
            if (!Module::sitesDirectoryPathIsValid($sitesDirectoryPath)) {
                throw new Exception\RuntimeException('Invalid directory path');
            }
            $this->sitesDirectoryPath = $sitesDirectoryPath;
        }
        return $this->sitesDirectoryPath;
    }

    /**
     * Get the directory path of the static site.
     */
    public function getSiteDirectoryPath(): string
    {
        if (null === $this->siteDirectoryPath) {
            $this->siteDirectoryPath = sprintf(
                '%s/%s',
                $this->getSitesDirectoryPath(),
                $this->getStaticSiteName()
            );
        }
        return $this->siteDirectoryPath;
    }

    /**
     * Get the static site entity.
     */
    public function getStaticSite(): StaticSiteRepresentation
    {
        if (null === $this->staticSite) {
            // Validate the static site entity.
            $staticSiteId = $this->getArg('static_site_id');
            if (!is_numeric($staticSiteId)) {
                throw new Exception\RuntimeException('Missing static_site_id');
            }
            $this->staticSite = $this->get('Omeka\ApiManager')
                ->read('static_site_export_static_sites', $staticSiteId)
                ->getContent();
        }
        return $this->staticSite;
    }

    /**
     * Get the static site name.
     */
    public function getStaticSiteName(): string
    {
        return $this->getArg('static_site_name') ?? $this->getStaticSite()->name();
    }
}
