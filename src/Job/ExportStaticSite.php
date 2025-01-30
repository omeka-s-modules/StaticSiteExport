<?php
namespace StaticSiteExport\Job;

use DateTime;
use Doctrine\DBAL\Connection;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Api\Representation\AssetRepresentation;
use Omeka\Api\Representation\ItemRepresentation;
use Omeka\Api\Representation\ItemSetRepresentation;
use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Job\AbstractJob;
use Omeka\Job\Exception;
use StaticSiteExport\Api\Representation\StaticSiteRepresentation;
use StaticSiteExport\Module;

class ExportStaticSite extends AbstractJob
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
     * An array of all item IDs assigned to this site.
     *
     * @var array
     */
    protected $itemIds;

    /**
     * An array of all media IDs assigned to items asigned to this site.
     *
     * @var array
     */
    protected $mediaIds;

    /**
     * An array of all item set IDs assigned to this site.
     *
     * @var array
     */
    protected $itemSetIds;

    /**
     * Export the static site.
     */
    public function perform() : void
    {
        $this->createSiteDirectory();

        // Create item bundles.
        foreach (array_chunk($this->getItemIds(), 100) as $itemIdsChunk) {
            if ($this->shouldStop()) return;
            foreach ($itemIdsChunk as $itemId) {
                $this->createItemBundle($itemId);
            }
        }
        // Create media bundles.
        foreach (array_chunk($this->getMediaIds(), 100) as $mediaIdsChunk) {
            if ($this->shouldStop()) return;
            foreach ($mediaIdsChunk as $mediaId) {
                $this->createMediaBundle($mediaId);
            }
        }
        // Create item set bundles.
        foreach (array_chunk($this->getItemSetIds(), 100) as $itemSetIdsChunk) {
            if ($this->shouldStop()) return;
            foreach ($itemSetIdsChunk as $itemSetId) {
                $this->createItemSetBundle($itemSetId);
            }
        }
        // Create asset bundles.
        foreach (array_chunk($this->getAssetIds(), 100) as $assetIdsChunk) {
            if ($this->shouldStop()) return;
            foreach ($assetIdsChunk as $assetId) {
                $this->createAssetBundle($assetId);
            }
        }

        $this->createSiteArchive();
        $this->deleteSiteDirectory();
    }

    /**
     * Create an item bundle.
     */
    public function createItemBundle(int $itemId) : void
    {
        $item = $this->get('Omeka\ApiManager')->read('items', $itemId)->getContent();
        $this->makeDirectory(sprintf('content/items/%s', $item->id()));
        $this->makeFile(
            sprintf('content/items/%s/index.md', $item->id()),
            $this->getItemPage($item)
        );
    }

    /**
     * Create a media bundle.
     */
    public function createMediaBundle(int $mediaId) : void
    {
        $media = $this->get('Omeka\ApiManager')->read('media', $mediaId)->getContent();
        $this->makeDirectory(sprintf('content/media/%s', $media->id()));
        $this->makeFile(
            sprintf('content/media/%s/index.md', $media->id()),
            $this->getMediaPage($media)
        );

        // Copy media data.
        $this->makeFile(
            sprintf('content/media/%s/data.json', $media->id()),
            json_encode($media->mediaData(), JSON_PRETTY_PRINT)
        );

        // Copy the original file.
        if ($media->hasOriginal()) {
            $filePath = sprintf(
                'content/media/%s/%s',
                $media->id(),
                sprintf('file%s', $media->extension() ? sprintf('.%s', $media->extension()) : '')
            );
            $this->makeFile($filePath);
            $client = $this->get('Omeka\HttpClient')
                ->setUri($media->originalUrl())
                ->setStream(sprintf('%s/%s', $this->getSiteDirectoryPath(), $filePath))->send();
        }

        // Copy the thumbnail files.
        if ($media->hasThumbnails()) {
            foreach (['large', 'medium', 'square'] as $type) {
                $filePath = sprintf(
                    'content/media/%s/%s',
                    $media->id(),
                    sprintf('thumbnail_%s.jpg', $type)
                );
                $this->makeFile($filePath);
                $client = $this->get('Omeka\HttpClient')
                    ->setUri($media->thumbnailUrl($type))
                    ->setStream(sprintf('%s/%s', $this->getSiteDirectoryPath(), $filePath))->send();
            }
        }
    }

    /**
     * Create an item set bundle.
     */
    public function createItemSetBundle(int $itemSetId) : void
    {
        $itemSet = $this->get('Omeka\ApiManager')->read('item_sets', $itemSetId)->getContent();
        $this->makeDirectory(sprintf('content/item-sets/%s', $itemSet->id()));
        $this->makeFile(
            sprintf('content/item-sets/%s/index.md', $itemSet->id()),
            $this->getItemSetPage($itemSet)
        );
    }

    /**
     * Create an asset bundle.
     */
    public function createAssetBundle(int $assetId) : void
    {
        $asset = $this->get('Omeka\ApiManager')->read('assets', $assetId)->getContent();
        $this->makeDirectory(sprintf('content/assets/%s', $asset->id()));
        $this->makeFile(
            sprintf('content/assets/%s/index.md', $asset->id()),
            $this->getAssetPage($asset)
        );

        // Note that $asset does not provide direct access to the asset's extension.
        $extension = substr($asset->filename(), strrpos($asset->filename(), '.') + 1);
        $filePath = sprintf(
            'content/assets/%s/%s',
            $asset->id(),
            sprintf('file.%s',$extension)
        );
        $this->makeFile($filePath);
        $client = $this->get('Omeka\HttpClient')
            ->setUri($asset->assetUrl())
            ->setStream(sprintf('%s/%s', $this->getSiteDirectoryPath(), $filePath))->send();
    }

    /**
     * Get item content (in markdown).
     */
    public function getItemPage(ItemRepresentation $item) : string
    {
        $page = [];

        // Add Hugo front matter.
        $frontMatter = [
            'date' => $item->created()->format('c'),
            'title' => $item->displayTitle(),
            'draft' => $item->isPublic() ? false : true,
        ];
        $page[] = json_encode($frontMatter, JSON_PRETTY_PRINT);

        // Iterate resource page blocks.
        $blockNames = ['mediaRender', 'resourceClass', 'values', 'itemSets', 'mediaList', 'linkedResources'];
        foreach ($blockNames as $blockName) {
            $block = $this->get('StaticSiteExport\ResourcePageBlockLayoutManager')->get($blockName);
            $page[] = $block->getMarkup($item, $this);
        }

        return implode("\n\n", $page);
    }

    /**
     * Get media content (in markdown).
     */
    public function getMediaPage(MediaRepresentation $media) : string
    {
        $page = [];

        // Add Hugo front matter.
        $frontMatter = [
            'date' => $media->created()->format('c'),
            'title' => $media->displayTitle(),
            'draft' => $media->isPublic() ? false : true,
        ];
        $page[] = json_encode($frontMatter, JSON_PRETTY_PRINT);

        // Iterate resource page blocks.
        $blockNames = ['mediaRender', 'mediaItem', 'resourceClass', 'values', 'linkedResources'];
        foreach ($blockNames as $blockName) {
            $block = $this->get('StaticSiteExport\ResourcePageBlockLayoutManager')->get($blockName);
            $page[] = $block->getMarkup($media, $this);
        }

        return implode("\n\n", $page);
    }

    /**
     * Get item set content (in markdown).
     */
    public function getItemSetPage(ItemSetRepresentation $itemSet) : string
    {
        $page = [];

        // Add Hugo front matter.
        $frontMatter = [
            'date' => $itemSet->created()->format('c'),
            'title' => $itemSet->displayTitle(),
            'draft' => $itemSet->isPublic() ? false : true,
        ];
        $page[] = json_encode($frontMatter, JSON_PRETTY_PRINT);

        // Iterate resource page blocks.
        $blockNames = ['mediaRender', 'resourceClass', 'values', 'linkedResources'];
        foreach ($blockNames as $blockName) {
            $block = $this->get('StaticSiteExport\ResourcePageBlockLayoutManager')->get($blockName);
            $page[] = $block->getMarkup($itemSet, $this);
        }

        return implode("\n\n", $page);
    }

    /**
     * Get item content (in markdown).
     */
    public function getAssetPage(AssetRepresentation $asset) : string
    {
        $page = [];

        // Add Hugo front matter.
        $frontMatter = [
            'date' => date('c'),
            'title' => $asset->name(),
            'draft' => false,
        ];
        $page[] = json_encode($frontMatter, JSON_PRETTY_PRINT);

        $page[] = sprintf(
            '{{< omeka-figure
                type="image"
                linkPage="/assets/%s"
                linkResource="file"
                imgPage="/assets/%s"
                imgResource="file"
            >}}',
            $asset->id(),
            $asset->id()
        );

        return implode("\n\n", $page);
    }

    /**
     * Make a directory in the static site directory.
     */
    public function makeDirectory(string $directoryPath) : void
    {
        $command = sprintf(
            '%s --parents %s',
            $this->get('Omeka\Cli')->getCommandPath('mkdir'),
            escapeshellarg(sprintf('%s/%s', $this->getSiteDirectoryPath(), $directoryPath))
        );
        $this->execute($command);
    }

    /**
     * Make a file in the static site directory.
     */
    public function makeFile(string $filePath, string $content = '') : void
    {
        file_put_contents(
            sprintf('%s/%s', $this->getSiteDirectoryPath(), $filePath),
            $content
        );
    }

    /**
     * Get the IDs of items that are assigned to this site.
     */
    public function getItemIds() : array
    {
        if (null === $this->itemIds) {
            $sql = 'SELECT item_site.item_id
                FROM item_site item_site
                INNER JOIN resource resource ON item_site.item_id = resource.id
                WHERE item_site.site_id = :site_id
                ORDER BY resource.id';
            $stmt = $this->get('Omeka\Connection')->prepare($sql);
            $stmt->bindValue('site_id', $this->getStaticSite()->site()->id());
            $this->itemIds = $stmt->executeQuery()->fetchFirstColumn();
        }
        return $this->itemIds;
    }

    /**
     * Get the IDs of media that are assigned to this site.
     */
    public function getMediaIds() : array
    {
        if (null === $this->mediaIds) {
            $sql = 'SELECT media.id
                FROM media media
                INNER JOIN item_site item_site ON media.item_id = item_site.item_id
                INNER JOIN resource resource ON item_site.item_id = resource.id
                WHERE item_site.site_id = :site_id
                ORDER BY resource.id';
            $stmt = $this->get('Omeka\Connection')->prepare($sql);
            $stmt->bindValue('site_id', $this->getStaticSite()->site()->id());
            $this->mediaIds = $stmt->executeQuery()->fetchFirstColumn();
        }
        return $this->mediaIds;
    }

    /**
     * Get the IDs of item sets that are assigned to this site.
     */
    public function getItemSetIds() : array
    {
        if (null === $this->itemSetIds) {
            $sql = 'SELECT site_item_set.item_set_id
                FROM site_item_set site_item_set
                INNER JOIN resource resource ON site_item_set.item_set_id = resource.id
                WHERE site_item_set.site_id = :site_id
                ORDER BY resource.id';
            $stmt = $this->get('Omeka\Connection')->prepare($sql);
            $stmt->bindValue('site_id', $this->getStaticSite()->site()->id());
            $this->itemSetIds = $stmt->executeQuery()->fetchFirstColumn();
        }
        return $this->itemSetIds;
    }

    /**
     * Get the IDs of assets that are assigned to this site.
     */
    public function getAssetIds() : array
    {
        if (null === $this->assetIds) {
            $sql = 'SELECT asset.id
                FROM asset asset
                INNER JOIN resource resource ON asset.id = resource.thumbnail_id
                WHERE resource.id IN (?)';
            $this->assetIds = $this->get('Omeka\Connection')->executeQuery(
                $sql,
                [array_merge($this->getItemIds(), $this->getMediaIds(), $this->getItemSetIds())],
                [Connection::PARAM_INT_ARRAY]
            )->fetchFirstColumn();
        }
        return $this->assetIds;
    }

    /**
     * Create the static site directory.
     */
    public function createSiteDirectory() : void
    {
        // Make the site directory.
        $command = sprintf(
            '%s %s -d %s && %s %s %s',
            $this->get('Omeka\Cli')->getCommandPath('unzip'),
            sprintf('%s/modules/StaticSiteExport/data/static-site.zip', OMEKA_PATH),
            $this->getSitesDirectoryPath(),
            $this->get('Omeka\Cli')->getCommandPath('mv'),
            sprintf('%s/static-site', $this->getSitesDirectoryPath()),
            $this->getSiteDirectoryPath()
        );
        $this->execute($command);

        // Make the hugo.json configuration file.
        $configContent = [
            'baseURL' => $this->getStaticSite()->dataValue('base_url'),
            'theme' => $this->getStaticSite()->dataValue('theme'),
            'title' => $this->getStaticSite()->site()->title(),
            'menus' => [
                'main' => [
                    [
                        'name' => 'Items',
                        'pageRef' => '/items',
                        'weight' => 10,
                    ],
                    [
                        'name' => 'Item sets',
                        'pageRef' => '/item-sets',
                        'weight' => 20,
                    ],
                ],
            ],
        ];
        $this->makeFile('hugo.json', json_encode($configContent, JSON_PRETTY_PRINT));
    }

    /**
     * Create the static site archive (ZIP).
     */
    public function createSiteArchive() : void
    {
        $command = sprintf(
            '%s %s && %s --recurse-paths %s %s',
            $this->get('Omeka\Cli')->getCommandPath('cd'),
            $this->getSitesDirectoryPath(),
            $this->get('Omeka\Cli')->getCommandPath('zip'),
            sprintf('%s.zip', $this->getStaticSite()->name()),
            $this->getStaticSite()->name()
        );
        $this->execute($command);
    }

    /**
     * Delete the static site directory.
     */
    public function deleteSiteDirectory() : void
    {
        $command = sprintf(
            '%s --recursive --force %s',
            $this->get('Omeka\Cli')->getCommandPath('rm'),
            escapeshellarg($this->getSiteDirectoryPath())
        );
        $this->execute($command);
    }

    /**
     * Execute a command.
     */
    public function execute(string $command) : void
    {
        $output = $this->get('Omeka\Cli')->execute($command);
        if (false === $output) {
            // Stop the job. Note that the Cli service already logged an error.
            throw new Exception\RuntimeException;
        }
        // Log every command output.
        $this->get('Omeka\Logger')->notice(sprintf("Output for command: %s\n%s", $command, $output));
    }

    /**
     * Get the directory path where the static sites are created.
     */
    public function getSitesDirectoryPath() : string
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
    public function getSiteDirectoryPath() : string
    {
        if (null === $this->siteDirectoryPath) {
            $this->siteDirectoryPath = sprintf(
                '%s/%s',
                $this->getSitesDirectoryPath(),
                $this->getStaticSite()->name()
            );
        }
        return $this->siteDirectoryPath;
    }

    /**
     * Get the static site entity.
     */
    public function getStaticSite() : StaticSiteRepresentation
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
     * Get a named service.
     */
    public function get(string $serviceName)
    {
        switch ($serviceName) {
            case 'Omeka\Settings\Site':
                $site = $this->getStaticSite()->site();
                $siteSettings = $services->get('Omeka\Settings\Site');
                $siteSettings->setTargetId($site->id());
                $service = $siteSettings;
                break;
            default:
                $service = $this->getServiceLocator()->get($serviceName);
                break;
        }
        return $service;
    }

    /**
     * Escape characters in a string using backslash.
     *
     * Especially useful for Markdown links, where square brackets must be
     * escaped in the link text, and double quotes must be escaped in the title
     * text: [link_text](http://example.com "title_text")
     */
    public function escape(array $characters, string $string) : string
    {
        foreach ($characters as $character) {
            $string = str_replace($character, sprintf('\%s', $character), $string);
        }
        return $string;
    }

    /**
     * Get the thumbnail shortcode for the passed resource.
     *
     * This renders a thumbnail in priority order:
     *
     *   1. The resource's asset thumbnail.
     *   2. The primary media's asset thumbnail.
     *   3. The primary media's auto-generated thumbnail.
     *   4. The global thumbnail according to the primary media's file media type.
     *
     * The valid thumbnail types are square, medium, and large. Default is large.
     *
     * The thumbnail height, if provided, will preserve aspect ratio. Default is no height.
     */
    public function getThumbnailShortcode(AbstractResourceEntityRepresentation $resource, string $thumbnailType, ?int $thumbnailHeight = null)
    {
        $thumbnailPage = null;
        $thumbnailResource = null;
        $thumbnailType = in_array($thumbnailType, ['square', 'medium', 'large']) ? $thumbnailType : 'large';
        $primaryMedia = $resource->primaryMedia();

        if ($resource->thumbnail()) {
            $thumbnailPage = sprintf('/assets/%s', $resource->thumbnail()->id());
            $thumbnailResource = 'file';
        } elseif ($primaryMedia && $primaryMedia->thumbnail()) {
            $thumbnailPage = sprintf('/assets/%s', $primaryMedia->thumbnail()->id());
            $thumbnailResource = 'file';
        } elseif ($primaryMedia && $primaryMedia->hasThumbnails()) {
            $thumbnailPage = sprintf('/media/%s', $primaryMedia->id());
            $thumbnailResource = sprintf('thumbnail_%s', $thumbnailType);
        } elseif ($primaryMedia && $primaryMedia->hasOriginal()) {
            $topLevelType = strstr((string) $primaryMedia->mediaType(), '/', true);
            if ('audio' === $topLevelType) {
                $thumbnailResource = '/thumbnails/audio.png';
            } elseif ('video' === $topLevelType) {
                $thumbnailResource = '/thumbnails/video.png';
            } elseif ('image' === $topLevelType) {
                $thumbnailResource = '/thumbnails/image.png';
            } else {
                $thumbnailResource = '/thumbnails/default.png';
            }
        }

        if (!$thumbnailResource) {
            return '';
        }

        return sprintf(
            '{{< omeka-thumbnail page="%s" resource="%s" height="%s" >}}',
            $thumbnailPage,
            $thumbnailResource,
            $thumbnailHeight
        );
    }
}
