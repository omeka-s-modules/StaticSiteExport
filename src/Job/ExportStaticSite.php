<?php
namespace StaticSiteExport\Job;

use ArrayObject;
use DateTime;
use Doctrine\DBAL\Connection;
use Laminas\EventManager\Event;
use Omeka\Api\Representation\AbstractEntityRepresentation;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Api\Representation\AssetRepresentation;
use Omeka\Api\Representation\ItemRepresentation;
use Omeka\Api\Representation\ItemSetRepresentation;
use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Api\Representation\SitePageRepresentation;
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
     * An array of all asset IDs assigned to this site.
     *
     * @var array
     */
    protected $assetIds;

    /**
     * An array of resource page blocks configured in the site's theme.
     *
     * @var array
     */
    protected $resourcePageBlocks;

    /**
     * Export the static site.
     */
    public function perform() : void
    {
        $this->createSiteDirectory();

        // Create the items section.
        $this->makeFile('content/items/_index.md', json_encode(['title' => 'Items']));
        foreach (array_chunk($this->getItemIds(), 100) as $itemIdsChunk) {
            if ($this->shouldStop()) return;
            foreach ($itemIdsChunk as $itemId) {
                $this->createItemBundle($itemId);
            }
        }
        // Create the media section.
        $this->makeFile('content/media/_index.md', json_encode(['title' => 'Media']));
        foreach (array_chunk($this->getMediaIds(), 100) as $mediaIdsChunk) {
            if ($this->shouldStop()) return;
            foreach ($mediaIdsChunk as $mediaId) {
                $this->createMediaBundle($mediaId);
            }
        }
        // Create the item sets section.
        $this->makeFile('content/item-sets/_index.md', json_encode(['title' => 'Item sets']));
        foreach (array_chunk($this->getItemSetIds(), 100) as $itemSetIdsChunk) {
            if ($this->shouldStop()) return;
            foreach ($itemSetIdsChunk as $itemSetId) {
                $this->createItemSetBundle($itemSetId);
            }
        }
        // Create the assets section.
        $this->makeFile('content/assets/_index.md', json_encode(['title' => 'Assets']));
        foreach (array_chunk($this->getAssetIds(), 100) as $assetIdsChunk) {
            if ($this->shouldStop()) return;
            foreach ($assetIdsChunk as $assetId) {
                $this->createAssetBundle($assetId);
            }
        }
        // Create the pages section.
        $this->makeFile('content/pages/_index.md', json_encode(['title' => 'Site pages']));
        $sitePages = $this->getStaticSite()->site()->pages();
        foreach ($sitePages as $sitePage) {
            $this->createSitePageBundle($sitePage);
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
        $this->makeDirectory(sprintf('content/items/%s/blocks', $item->id()));

        $frontMatterPage = new ArrayObject([
            'date' => $item->created()->format('c'),
            'title' => $item->displayTitle(),
            'draft' => $item->isPublic() ? false : true,
            'params' => [
                'thumbnailSpec' => $this->getThumbnailSpec($item, 'square'),
            ],
        ]);

        // Make the block files.
        $i = 0;
        $blockNames = $this->getResourcePageBlocks()['items'];
        foreach ($blockNames as $blockName) {
            $block = $this->get('StaticSiteExport\ResourcePageBlockLayoutManager')->get($blockName);
            $blockPosition = $i++;
            $frontMatterBlock = new ArrayObject([
                'params' => [
                    'class' => sprintf('resource-page-block-%s', $blockName),
                ],
            ]);
            $blockMarkdown = $block->getMarkdown($this, $item, $frontMatterPage, $frontMatterBlock);
            $this->makeFile(
                sprintf('content/items/%s/blocks/%s-%s.md', $item->id(), $blockPosition, $blockName),
                sprintf("%s\n%s", json_encode($frontMatterBlock, JSON_PRETTY_PRINT), $blockMarkdown)
            );
        }

        // Trigger the "static_site_export.bundle.item" event.
        $this->triggerEvent(
            'static_site_export.bundle.item',
            [
                'resource' => $item,
                'frontMatter' => $frontMatterPage,
            ]
        );

        // Make the page file.
        $this->makeFile(
            sprintf('content/items/%s/index.md', $item->id()),
            json_encode($frontMatterPage, JSON_PRETTY_PRINT)
        );
    }

    /**
     * Create a media bundle.
     */
    public function createMediaBundle(int $mediaId) : void
    {
        $media = $this->get('Omeka\ApiManager')->read('media', $mediaId)->getContent();

        $this->makeDirectory(sprintf('content/media/%s', $media->id()));
        $this->makeDirectory(sprintf('content/media/%s/blocks', $media->id()));

        $frontMatterPage = new ArrayObject([
            'date' => $media->created()->format('c'),
            'title' => $media->displayTitle(),
            'draft' => $media->isPublic() ? false : true,
            'params' => [
                'itemID' => $media->item()->id(),
                'thumbnailSpec' => $this->getThumbnailSpec($media, 'square'),
            ],
        ]);

        // Make the block files.
        $i = 0;
        $blockNames = $this->getResourcePageBlocks()['media'];
        foreach ($blockNames as $blockName) {
            $block = $this->get('StaticSiteExport\ResourcePageBlockLayoutManager')->get($blockName);
            $blockPosition = $i++;
            $frontMatterBlock = new ArrayObject([
                'params' => [
                    'class' => sprintf('resource-page-block-%s', $blockName),
                ],
            ]);
            $blockMarkdown = $block->getMarkdown($this, $media, $frontMatterPage, $frontMatterBlock);
            $this->makeFile(
                sprintf('content/media/%s/blocks/%s-%s.md', $media->id(), $blockPosition, $blockName),
                sprintf("%s\n%s", json_encode($frontMatterBlock, JSON_PRETTY_PRINT), $blockMarkdown)
            );
        }

        // Trigger the "static_site_export.bundle.media" event.
        $this->triggerEvent(
            'static_site_export.bundle.media',
            [
                'resource' => $media,
                'frontMatter' => $frontMatterPage,
            ]
        );

        // Make the page file.
        $this->makeFile(
            sprintf('content/media/%s/index.md', $media->id()),
            json_encode($frontMatterPage, JSON_PRETTY_PRINT)
        );

        // Map the media data file.
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
        $this->makeDirectory(sprintf('content/item-sets/%s/blocks', $itemSet->id()));

        $frontMatterPage = new ArrayObject([
            'date' => $itemSet->created()->format('c'),
            'title' => $itemSet->displayTitle(),
            'draft' => $itemSet->isPublic() ? false : true,
            'params' => [
                'thumbnailSpec' => $this->getThumbnailSpec($itemSet, 'square'),
            ],
        ]);

        // Make the block files.
        $i = 0;
        $blockNames = $this->getResourcePageBlocks()['item_sets'];
        foreach ($blockNames as $blockName) {
            $block = $this->get('StaticSiteExport\ResourcePageBlockLayoutManager')->get($blockName);
            $blockPosition = $i++;
            $frontMatterBlock = new ArrayObject([
                'params' => [
                    'class' => sprintf('resource-page-block-%s', $blockName),
                ],
            ]);
            $blockMarkdown = $block->getMarkdown($this, $itemSet, $frontMatterPage, $frontMatterBlock);
            $this->makeFile(
                sprintf('content/item-sets/%s/blocks/%s-%s.md', $itemSet->id(), $blockPosition, $blockName),
                sprintf("%s\n%s", json_encode($frontMatterBlock, JSON_PRETTY_PRINT), $blockMarkdown)
            );
        }

        // Trigger the "static_site_export.bundle.item_set" event.
        $this->triggerEvent(
            'static_site_export.bundle.item_set',
            [
                'resource' => $itemSet,
                'frontMatter' => $frontMatterPage,
            ]
        );

        // Make the page file.
        $this->makeFile(
            sprintf('content/item-sets/%s/index.md', $itemSet->id()),
            json_encode($frontMatterPage, JSON_PRETTY_PRINT)
        );
    }

    /**
     * Create an asset bundle.
     */
    public function createAssetBundle(int $assetId) : void
    {
        $asset = $this->get('Omeka\ApiManager')->read('assets', $assetId)->getContent();

        $this->makeDirectory(sprintf('content/assets/%s', $asset->id()));

        // Add Hugo front matter.
        $frontMatter = new ArrayObject([
            'date' => date('c'),
            'title' => $asset->name(),
            'draft' => false,
            'params' => [
                'thumbnailSpec' => $this->getThumbnailSpec($asset, 'square'),
            ],
        ]);

        $markdown = sprintf(
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

        // Trigger the "static_site_export.bundle.asset" event.
        $this->triggerEvent(
            'static_site_export.bundle.asset',
            [
                'resource' => $asset,
                'frontMatter' => $frontMatter,
            ]
        );

        $this->makeFile(
            sprintf('content/assets/%s/index.md', $asset->id()),
            json_encode($frontMatter, JSON_PRETTY_PRINT) . "\n" . $markdown
        );

        // Note that $asset does not provide direct access to the asset's extension.
        $extension = substr($asset->filename(), strrpos($asset->filename(), '.') + 1);
        $filePath = sprintf(
            'content/assets/%s/%s',
            $asset->id(),
            sprintf('file.%s', $extension)
        );
        $this->makeFile($filePath);
        $client = $this->get('Omeka\HttpClient')
            ->setUri($asset->assetUrl())
            ->setStream(sprintf('%s/%s', $this->getSiteDirectoryPath(), $filePath))->send();
    }

    /**
     * Create a site page bundle.
     */
    public function createSitePageBundle(SitePageRepresentation $sitePage) : void
    {
        $this->makeDirectory(sprintf('content/pages/%s', $sitePage->slug()));

        $frontMatter = new ArrayObject([
            'date' => $sitePage->created()->format('c'),
            'title' => $sitePage->title(),
            'draft' => $sitePage->isPublic() ? false : true,
        ]);

        // @todo: Use named services to return block Markdown.
        // Iterate site page blocks.
        // foreach ($sitePage->blocks() as $sitePageBlock) {
        //     $block = $this->get('StaticSiteExport\BlockLayoutManager')->get($sitePageBlock->layout());
        //     $markdown[] = $block->getMarkdown($sitePageBlock, $this, $frontMatter);
        // }

        // Trigger the "static_site_export.bundle.site_page" event.
        $this->triggerEvent(
            'static_site_export.bundle.site_page',
            [
                'resource' => $sitePage,
                'frontMatter' => $frontMatter,
            ]
        );

        // Make the page file.
        $this->makeFile(
            sprintf('content/pages/%s/index.md', $sitePage->slug()),
            json_encode($frontMatter, JSON_PRETTY_PRINT)
        );
    }

    /**
     * Make a directory in the static site directory.
     */
    public function makeDirectory(string $directoryPath) : void
    {
        $command = sprintf(
            '%s -p %s',
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
        $modulePath = sprintf('%s/modules/StaticSiteExport', OMEKA_PATH);

        // Make the site directory.
        $this->makeDirectory('archetypes');
        $this->makeDirectory('assets');
        $this->makeDirectory('assets/thumbnails');
        $this->makeDirectory('content');
        $this->makeDirectory('content/assets');
        $this->makeDirectory('content/items');
        $this->makeDirectory('content/item-sets');
        $this->makeDirectory('content/media');
        $this->makeDirectory('content/pages');
        $this->makeDirectory('data');
        $this->makeDirectory('i18n');
        $this->makeDirectory('layouts');
        $this->makeDirectory('layouts/partials');
        $this->makeDirectory('layouts/shortcodes');
        $this->makeDirectory('static');
        $this->makeDirectory('static/js');
        $this->makeDirectory('themes');

        // Unzip the Omeka theme into the Hugo themes directory.
        $command = sprintf(
            '%s %s -d %s',
            $this->get('Omeka\Cli')->getCommandPath('unzip'),
            sprintf('%s/data/gohugo-theme-omeka-s.zip', $modulePath),
            sprintf('%s/themes/', $this->getSiteDirectoryPath())
        );
        $this->execute($command);

        // Copy shortcodes provided by modules.
        $shortcodes = $this->get('Config')['static_site_export']['shortcodes'];
        foreach ($shortcodes as $shortcodeName => $fromShortcodePath) {
            if (!is_file($fromShortcodePath)) {
                continue; // Skip non-files.
            }
            $command = sprintf(
                '%s %s %s',
                $this->get('Omeka\Cli')->getCommandPath('cp'),
                escapeshellarg($fromShortcodePath),
                escapeshellarg(sprintf('%s/layouts/shortcodes/%s.html', $this->getSiteDirectoryPath(), $shortcodeName))
            );
            $this->execute($command);
        }

        // Copy vendor packages provided by modules.
        $vendorPackages = $this->get('Config')['static_site_export']['vendor_packages'];
        foreach ($vendorPackages as $packageName => $fromDirectoryPath) {
            if (!is_dir($fromDirectoryPath)) {
                continue; // Skip non-directories.
            }
            if (in_array($packageName, ['mirador', 'openseadragon'])) {
                continue; // Skip existing packages.
            }
            // Make the package directory under vendor.
            $toDirectoryPath = sprintf('static/vendor/%s', $packageName);
            $this->makeDirectory($toDirectoryPath);
            // Copy packages into the vendor directory.
            $command = sprintf(
                '%s --recursive %s %s',
                $this->get('Omeka\Cli')->getCommandPath('cp'),
                sprintf('%s/*', escapeshellarg($fromDirectoryPath)),
                escapeshellarg(sprintf('%s/%s', $this->getSiteDirectoryPath(), $toDirectoryPath))
            );
            $this->execute($command);
        }

        // Build the Hugo menu from Omeka site navigation.
        $menu = new ArrayObject;
        $recurseNav = function (
            array $navLinks,
            ?string $parentId = null,
            int $weight = 0
        ) use (&$recurseNav, $menu) {
            foreach ($navLinks as $navLink) {
                $id = sprintf('a%s', md5(rand()));
                $weight++;
                $this->get('StaticSiteExport\NavigationLinkManager')
                    ->get($navLink['type'])
                    ->setMenuEntry($this, $menu, $navLink, $id, $parentId, $weight);
                if ($navLink['links']) {
                    $recurseNav($navLink['links'], $id, $weight);
                }
            }
        };
        $navLinks = $this->getStaticSite()->site()->navigation();
        $recurseNav($navLinks);

        // Get the homepage.
        $homepage = null;
        $omekaHomepage = $this->getStaticSite()->site()->homepage();
        if ($omekaHomepage) {
            $homepage = sprintf('pages/%s', $omekaHomepage->slug());
        } else {
            // No homepage set. Get the first page in the menu.
            foreach ($menu as $menuEntry) {
                $query = '/pages/';
                if (isset($menuEntry['pageRef']) && $query === substr($menuEntry['pageRef'], 0, strlen($query))) {
                    // Must remove the leading slash so Hugo builds the URL correctly.
                    $homepage = ltrim($menuEntry['pageRef'], '/');
                }
            }
        }

        // Make the hugo.json configuration file.
        $configContent = [
            'baseURL' => $this->getStaticSite()->dataValue('base_url'),
            'theme' => 'gohugo-theme-omeka-s',
            'title' => $this->getStaticSite()->site()->title(),
            'menus' => [
                'main' => $menu->getArrayCopy(),
            ],
            'params' => [
                'homepage' => $homepage,
            ],
            'pagination' => [
                'pagerSize' => 25,
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
            '%s -r %s',
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
     * Get the resource page blocks configuration from the site's theme.
     */
    public function getResourcePageBlocks()
    {
        if (null === $this->resourcePageBlocks) {
            // Must set some things before fetching the resolved resource page
            // blocks from the theme configuration.
            $themeManager = $this->get('Omeka\Site\ThemeManager');
            $resourcePageBlockLayoutManager = $this->get('Omeka\ResourcePageBlockLayoutManager');
            $siteSettings = $this->get('Omeka\Settings\Site');

            $site = $this->getStaticSite()->site();
            $currentTheme = $themeManager->getTheme($site->theme());
            $themeManager->setCurrentTheme($currentTheme);
            $siteSettings->setTargetId($site->id());
            $resourceTypes = $resourcePageBlockLayoutManager->getResourcePageBlocks($currentTheme);

            // Flatten the layouts for each resource type, prioritizing the
            // "main" region. We do this because the static site's resource
            // pages do not have regions.
            $itemsMain = $resourceTypes['items']['main'] ?? [];
            $itemSetsMain = $resourceTypes['item_sets']['main'] ?? [];
            $mediaMain = $resourceTypes['media']['main'] ?? [];
            unset(
                $resourceTypes['items']['main'],
                $resourceTypes['item_sets']['main'],
                $resourceTypes['media']['main']
            );
            $resourcePageBlocks = [
                'items' => $itemsMain,
                'item_sets' => $itemSetsMain,
                'media' => $mediaMain,
            ];
            foreach ($resourceTypes as $resourceType => $regions) {
                foreach ($regions as $region => $layouts) {
                    foreach ($layouts as $layout) {
                        $resourcePageBlocks[$resourceType][] = $layout;
                    }
                }
            }
            $this->resourcePageBlocks = $resourcePageBlocks;
        }
        return $this->resourcePageBlocks;
    }

    /**
     * Get a named service. Proxy to $this->getServiceLocator().
     */
    public function get(string $serviceName)
    {
        return $this->getServiceLocator()->get($serviceName);
    }

    /**
     * Trigger an event.
     */
    public function triggerEvent(string $eventName, array $eventParams)
    {
        $event = new Event($eventName, $this, $eventParams);
        $events = $this->get('EventManager');
        $events->setIdentifiers([self::class]);
        $events->triggerEvent($event);
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
     * Get the markdown for a link to a resource, including thumbnail.
     *
     * Options are the following:
     *
     *  - thumbnailType: The type of thumbnail, large, medium, or square (default: large)
     *  - thumbnailHeight: The height of the thumbnailm preserving aspect ratio (default: null)
     */
    public function getLinkMarkdown(AbstractResourceEntityRepresentation $resource, array $options = []) : string
    {
        $defaultOptions = [
            'thumbnailType' => 'large',
            'thumbnailHeight' => null,
        ];
        $options = array_merge($defaultOptions, $options);
        if ($resource instanceof ItemRepresentation) {
            $resourceTypePath = 'items';
        } elseif ($resource instanceof MediaRepresentation) {
            $resourceTypePath = 'media';
        } elseif ($resource instanceof ItemSetRepresentation) {
            $resourceTypePath = 'item-sets';
        }
        return sprintf(
            '%s[%s]({{< ref "/%s/%s" >}} "%s")',
            $this->getThumbnailShortcode($resource, $options),
            $this->escape(['[', ']'], $resource->displayTitle()),
            $resourceTypePath,
            $resource->id(),
            $this->escape(['"'], $resource->displayTitle()),
        );
    }

    /**
     * Get the thumbnail shortcode for the passed resource.
     */
    public function getThumbnailShortcode(AbstractResourceEntityRepresentation $resource, array $options = [])
    {
        $defaultOptions = [
            'thumbnailType' => 'large',
            'thumbnailHeight' => null,
        ];
        $options = array_merge($defaultOptions, $options);
        $thumbnailSpec = $this->getThumbnailSpec($resource, $options['thumbnailType']);
        if (!$thumbnailSpec['resource']) {
            return '';
        }
        return sprintf(
            '{{< omeka-thumbnail page="%s" resource="%s" height="%s" >}}',
            $thumbnailSpec['page'],
            $thumbnailSpec['resource'],
            $options['thumbnailHeight']
        );
    }

    /**
     * Get the thumbnail specification (page and resource).
     *
     * This returns a spec in priority order:
     *
     *  1. The resource's asset thumbnail.
     *  2. The primary media's asset thumbnail.
     *  3. The primary media's auto-generated thumbnail.
     *  4. The global thumbnail according to the primary media's file media type.
     */
    public function getThumbnailSpec(AbstractEntityRepresentation $resource, string $thumbnailType) : array
    {
        $thumbnailPage = null;
        $thumbnailResource = null;
        if ($resource instanceof AssetRepresentation) {
            $thumbnailPage = sprintf('/assets/%s', $resource->id());
            $thumbnailResource = 'file';
        } elseif ($resource instanceof AbstractResourceEntityRepresentation) {
            $primaryMedia = $resource->primaryMedia();
            if ($resource->thumbnail()) {
                $thumbnailPage = sprintf('/assets/%s', $resource->thumbnail()->id());
                $thumbnailResource = 'file';
            } elseif ($primaryMedia && $primaryMedia->thumbnail()) {
                $thumbnailPage = sprintf('/assets/%s', $primaryMedia->thumbnail()->id());
                $thumbnailResource = 'file';
            } elseif ($primaryMedia && $primaryMedia->hasThumbnails()) {
                $thumbnailType = in_array($thumbnailType, ['square', 'medium', 'large']) ? $thumbnailType : 'large';
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
        }
        return [
            'page' => $thumbnailPage,
            'resource' => $thumbnailResource,
        ];
    }
}
