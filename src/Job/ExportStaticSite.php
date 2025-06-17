<?php
namespace StaticSiteExport\Job;

use ArrayObject;
use Doctrine\DBAL\Connection;
use Laminas\EventManager\Event;
use Locale;
use Omeka\Api\Exception\NotFoundException;
use Omeka\Api\Representation\AbstractEntityRepresentation;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Api\Representation\AssetRepresentation;
use Omeka\Api\Representation\ItemRepresentation;
use Omeka\Api\Representation\ItemSetRepresentation;
use Omeka\Api\Representation\MediaRepresentation;
use Omeka\Api\Representation\SitePageRepresentation;
use Omeka\File\Store\Local;
use StaticSiteExport\Module;

class ExportStaticSite extends AbstractStaticSiteJob
{
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
     * The current theme.
     */
    protected $currentTheme;

    /**
     * Export the static site.
     */
    public function perform(): void
    {
        // Must build a new entity manager here to avoid errors in dispatcher.
        $this->buildEntityManager();
        $this->prepareSite();

        $this->triggerEvent('static_site_export.site_export.pre', []);
        $this->createSiteDirectory();
        $this->createItemsSection();
        $this->createMediaSection();
        $this->createItemSetsSection();
        $this->createAssetsSection();
        $this->createPagesSection();
        $this->triggerEvent('static_site_export.site_export.post', []);

        $this->createSiteArchive();
        $this->deleteSiteDirectory();
    }

    /**
     * Build a new entity manager instance and set it to the service manager.
     */
    public function buildEntityManager()
    {
        $services = $this->getServiceLocator();
        $entityManager = $this->getServiceLocator()->build('Omeka\EntityManager');
        $services->setAllowOverride(true);
        $services->setService('Omeka\EntityManager', $entityManager);
        $services->setAllowOverride(false);
    }

    /**
     * Reset the entity manager.
     *
     * This is an attempt to resolve memory leaks related to the entity manager.
     * Normally, calling clear() on the entity manager is sufficient, but, while
     * some entities were being detached with clear(), many were not, so memory
     * usage was increasing on every chunk. We still don't know why.
     *
     * Here we call close() on the entity manager, which is a heavy-handed way
     * to to clear all entities, and then build a new entity manager.
     */
    public function resetEntityManager()
    {
        $this->getServiceLocator()->get('Omeka\EntityManager')->close();
        $this->buildEntityManager();
    }

    /**
     * Create the items section.
     */
    public function createItemsSection(): void
    {
        $frontMatter = [
            'title' => $this->translate('Items'),
            'params' => [
                'titleSingular' => $this->translate('Item'),
                'bodyClasses' => [
                    'item resource browse',
                ],
            ],
        ];
        $this->makeFile('content/items/_index.md', json_encode($frontMatter));
        foreach (array_chunk($this->getItemIds(), 100) as $itemIdsChunk) {
            if ($this->shouldStop()) {
                return;
            }
            foreach ($itemIdsChunk as $itemId) {
                $this->createItemBundle($itemId);
            }
            $this->resetEntityManager();
        }
    }

    /**
     * Create the media section.
     */
    public function createMediaSection(): void
    {
        $frontMatter = [
            'title' => $this->translate('Media'),
            'params' => [
                'titleSingular' => $this->translate('Media'),
                'bodyClasses' => [
                    'media resource browse',
                ],
            ],
        ];
        $this->makeFile('content/media/_index.md', json_encode($frontMatter));
        foreach (array_chunk($this->getMediaIds(), 100) as $mediaIdsChunk) {
            if ($this->shouldStop()) {
                return;
            }
            foreach ($mediaIdsChunk as $mediaId) {
                $this->createMediaBundle($mediaId);
            }
            $this->resetEntityManager();
        }
    }

    /**
     * Create the item-sets section.
     */
    public function createItemSetsSection(): void
    {
        // Create the item sets section.
        $frontMatter = [
            'title' => $this->translate('Item sets'),
            'params' => [
                'titleSingular' => $this->translate('Item set'),
                'bodyClasses' => [
                    'item-set resource browse',
                ],
            ],
        ];
        $this->makeFile('content/item-sets/_index.md', json_encode($frontMatter));
        foreach (array_chunk($this->getItemSetIds(), 100) as $itemSetIdsChunk) {
            if ($this->shouldStop()) {
                return;
            }
            foreach ($itemSetIdsChunk as $itemSetId) {
                $this->createItemSetBundle($itemSetId);
            }
            $this->resetEntityManager();
        }
    }

    /**
     * Create the assets section.
     */
    public function createAssetsSection(): void
    {
        // Create the assets section.
        $frontMatter = [
            'title' => $this->translate('Assets'),
            'params' => [
                'titleSingular' => $this->translate('Asset'),
                'bodyClasses' => [
                    'asset resource browse',
                ],
            ],
        ];
        $this->makeFile('content/assets/_index.md', json_encode($frontMatter));
        foreach (array_chunk($this->getAssetIds(), 100) as $assetIdsChunk) {
            if ($this->shouldStop()) {
                return;
            }
            foreach ($assetIdsChunk as $assetId) {
                $this->createAssetBundle($assetId);
            }
            $this->resetEntityManager();
        }
    }

    /**
     * Create the pages section.
     */
    public function createPagesSection(): void
    {
        // Create the pages section.
        $frontMatter = [
            'title' => $this->translate('Site pages'),
            'params' => [
                'bodyClasses' => [
                    'page resource browse',
                ],
            ],
        ];
        $this->makeFile('content/pages/_index.md', json_encode($frontMatter));
        $sitePages = $this->getStaticSite()->site()->pages();
        foreach ($sitePages as $sitePage) {
            $this->createSitePageBundle($sitePage);
        }
    }

    /**
     * Create an item bundle.
     */
    public function createItemBundle(int $itemId): void
    {
        try {
            $item = $this->get('Omeka\ApiManager')->read('items', $itemId)->getContent();
        } catch (NotFoundException $e) {
            return;
        }

        $this->makeDirectory(sprintf('content/items/%s', $item->id()));
        $this->makeDirectory(sprintf('content/items/%s/blocks', $item->id()));

        $frontMatterPage = new ArrayObject([
            'date' => $item->created()->format('c'),
            'title' => $item->displayTitle(),
            'draft' => false,
            'params' => [
                'itemID' => $item->id(),
                'description' => $item->displayDescription(),
                'thumbnailSpec' => $this->getThumbnailSpec($item, 'square'),
                'bodyClasses' => [
                    'item resource show',
                ],
            ],
        ]);

        $blocks = $this->getResourcePageBlocks('items', $item, $frontMatterPage);

        // Trigger the item bundle event.
        $this->triggerEvent(
            'static_site_export.page_bundle.item',
            [
                'resource' => $item,
                'frontMatter' => $frontMatterPage,
                'blocks' => $blocks,
            ]
        );

        $this->makeBundleFiles(sprintf('items/%s', $item->id()), $item, $frontMatterPage, $blocks);
    }

    /**
     * Create a media bundle.
     */
    public function createMediaBundle(int $mediaId): void
    {
        try {
            $media = $this->get('Omeka\ApiManager')->read('media', $mediaId)->getContent();
        } catch (NotFoundException $e) {
            return;
        }

        $this->makeDirectory(sprintf('content/media/%s', $media->id()));
        $this->makeDirectory(sprintf('content/media/%s/blocks', $media->id()));

        $frontMatterPage = new ArrayObject([
            'date' => $media->created()->format('c'),
            'title' => $media->displayTitle(),
            'draft' => false,
            'params' => [
                'mediaID' => $media->id(),
                'itemID' => $media->item()->id(),
                'description' => $media->displayDescription(),
                'thumbnailSpec' => $this->getThumbnailSpec($media, 'square'),
                'bodyClasses' => [
                    'media resource show',
                ],
            ],
        ]);

        $blocks = $this->getResourcePageBlocks('media', $media, $frontMatterPage);

        // Trigger the media bundle event.
        $this->triggerEvent(
            'static_site_export.page_bundle.media',
            [
                'resource' => $media,
                'frontMatter' => $frontMatterPage,
                'blocks' => $blocks,
            ]
        );

        $this->makeBundleFiles(sprintf('media/%s', $media->id()), $media, $frontMatterPage, $blocks);

        // Make the media data file.
        $this->makeFile(
            sprintf('content/media/%s/data.json', $media->id()),
            json_encode($media->mediaData(), JSON_PRETTY_PRINT)
        );

        // Copy original and thumbnail files, if any. Use copy() if the installation
        // uses the Local file store, otherwise use HTTP client data streaming.
        $fileStore = $this->get('Omeka\File\Store');
        if ($media->hasOriginal()) {
            $filePath = sprintf(
                'content/media/%s/%s',
                $media->id(),
                sprintf('file%s', $media->extension() ? sprintf('.%s', $media->extension()) : '')
            );
            $toPath = sprintf('%s/%s', $this->getSiteDirectoryPath(), $filePath);
            if ($fileStore instanceof Local) {
                $fromPath = $fileStore->getLocalPath(sprintf('original/%s', $media->filename()));
                copy($fromPath, $toPath);
            } else {
                $fromPath = $media->originalUrl();
                $this->makeFile($filePath);
                $this->get('Omeka\HttpClient')->setUri($fromPath)->setStream($toPath)->send();
            }
        }
        if ($media->hasThumbnails()) {
            foreach (['large', 'medium', 'square'] as $type) {
                $filePath = sprintf(
                    'content/media/%s/%s',
                    $media->id(),
                    sprintf('thumbnail_%s.jpg', $type)
                );
                $toPath = sprintf('%s/%s', $this->getSiteDirectoryPath(), $filePath);
                if ($fileStore instanceof Local) {
                    $fromPath = $fileStore->getLocalPath(sprintf('%s/%s.jpg', $type, $media->storageId()));
                    copy($fromPath, $toPath);
                } else {
                    $fromPath = $media->thumbnailUrl($type);
                    $this->makeFile($filePath);
                    $this->get('Omeka\HttpClient')->setUri($fromPath)->setStream($toPath)->send();
                }
            }
        }
    }

    /**
     * Create an item set bundle.
     */
    public function createItemSetBundle(int $itemSetId): void
    {
        try {
            $itemSet = $this->get('Omeka\ApiManager')->read('item_sets', $itemSetId)->getContent();
        } catch (NotFoundException $e) {
            return;
        }

        $this->makeDirectory(sprintf('content/item-sets/%s', $itemSet->id()));
        $this->makeDirectory(sprintf('content/item-sets/%s/blocks', $itemSet->id()));

        $frontMatterPage = new ArrayObject([
            'date' => $itemSet->created()->format('c'),
            'title' => $itemSet->displayTitle(),
            'draft' => false,
            'params' => [
                'itemSetID' => $itemSet->id(),
                'description' => $itemSet->displayDescription(),
                'thumbnailSpec' => $this->getThumbnailSpec($itemSet, 'square'),
                'bodyClasses' => [
                    'item-set resource show',
                ],
            ],
        ]);

        $blocks = $this->getResourcePageBlocks('item_sets', $itemSet, $frontMatterPage);

        // Trigger the item set bundle event.
        $this->triggerEvent(
            'static_site_export.page_bundle.item_set',
            [
                'resource' => $itemSet,
                'frontMatter' => $frontMatterPage,
                'blocks' => $blocks,
            ]
        );

        $this->makeBundleFiles(sprintf('item-sets/%s', $itemSet->id()), $itemSet, $frontMatterPage, $blocks);
    }

    /**
     * Create an asset bundle.
     */
    public function createAssetBundle(int $assetId): void
    {
        try {
            $asset = $this->get('Omeka\ApiManager')->read('assets', $assetId)->getContent();
        } catch (NotFoundException $e) {
            return;
        }

        $this->makeDirectory(sprintf('content/assets/%s', $asset->id()));
        $this->makeDirectory(sprintf('content/assets/%s/blocks', $asset->id()));

        // Add Hugo front matter.
        $frontMatterPage = new ArrayObject([
            'title' => $asset->name(),
            'draft' => false,
            'params' => [
                'thumbnailSpec' => $this->getThumbnailSpec($asset, 'square'),
                'bodyClasses' => [
                    'asset resource show',
                ],
            ],
        ]);

        $blocks = new ArrayObject;

        // Trigger the asset bundle event.
        $this->triggerEvent(
            'static_site_export.page_bundle.asset',
            [
                'resource' => $asset,
                'frontMatter' => $frontMatterPage,
                'blocks' => $blocks,
            ]
        );

        $this->makeBundleFiles(sprintf('assets/%s', $asset->id()), $asset, $frontMatterPage, $blocks);

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
    public function createSitePageBundle(SitePageRepresentation $sitePage): void
    {
        $this->makeDirectory(sprintf('content/pages/%s', $sitePage->slug()));
        $this->makeDirectory(sprintf('content/pages/%s/blocks', $sitePage->slug()));

        $frontMatterPage = new ArrayObject([
            'date' => $sitePage->created()->format('c'),
            'title' => $sitePage->title(),
            'draft' => false,
            'params' => [
                'pageSlug' => $sitePage->slug(),
                'layout' => $sitePage->layout(),
                'layoutData' => $sitePage->layoutData(),
                'layoutClasses' => [],
                'layoutStyles' => [],
                'bodyClasses' => [
                    sprintf('page site-page-%s', $sitePage->slug()),
                ],
            ],
        ]);

        $blocks = $this->getSitePageBlocks($sitePage, $frontMatterPage);

        // Trigger the site page bundle event.
        $this->triggerEvent(
            'static_site_export.page_bundle.site_page',
            [
                'resource' => $sitePage,
                'frontMatter' => $frontMatterPage,
                'blocks' => $blocks,
            ]
        );

        // Set the resolved classes and inline styles to front matter.
        switch ($sitePage->layout()) {
            case 'grid':
                $gridColumns = (int) $sitePage->layoutDataValue('grid_columns');
                $gridColumnGap = (int) $sitePage->layoutDataValue('grid_column_gap', 10);
                $gridRowGap = (int) $sitePage->layoutDataValue('grid_row_gap', 10);

                $frontMatterPage['params']['layoutClasses'][] = 'page-layout-grid';
                $frontMatterPage['params']['layoutClasses'][] = sprintf('grid-template-columns-%s', $gridColumns);
                $frontMatterPage['params']['layoutStyles'][] = sprintf('column-gap: %spx;', $gridColumnGap);
                $frontMatterPage['params']['layoutStyles'][] = sprintf('row-gap: %spx;', $gridRowGap);
                break;
            case '':
            default:
                $frontMatterPage['params']['layoutClasses'][] = 'page-layout-normal';
                break;
        }

        $this->makeBundleFiles(sprintf('pages/%s', $sitePage->slug()), $sitePage, $frontMatterPage, $blocks);
    }

    /**
     * Make a directory in the static site directory.
     */
    public function makeDirectory(string $directoryPath): void
    {
        mkdir(sprintf('%s/%s', $this->getSiteDirectoryPath(), $directoryPath), 0755, true);
    }

    /**
     * Make a file in the static site directory.
     */
    public function makeFile(string $filePath, string $content = ''): void
    {
        file_put_contents(
            sprintf('%s/%s', $this->getSiteDirectoryPath(), $filePath),
            $content
        );
    }

    /**
     * Set added IDs to IDs that were set automaticlly.
     *
     * Modules can use the "static_site_export.resource_add.*" event to add
     * resources that were not automatically added by this module.
     */
    public function setAddedIds(string $resourceType, array $ids): array
    {
        $addIds = new ArrayObject;
        $this->triggerEvent(
            sprintf('static_site_export.resource_add.%s', $resourceType),
            [
                'ids' => $ids,
                'addIds' => $addIds,
            ]
        );
        // Include IDs added via the event.
        $addIds = array_filter(array_values($addIds->getArrayCopy()), 'is_numeric');
        return array_unique(array_merge($ids, $addIds));
    }

    /**
     * Get the IDs of items that are assigned to this site.
     */
    public function getItemIds(): array
    {
        if (null === $this->itemIds) {
            $includePrivate = $this->getStaticSite()->dataValue('include_private');
            $sql = sprintf('SELECT item_site.item_id
                FROM item_site item_site
                INNER JOIN resource resource ON item_site.item_id = resource.id
                WHERE item_site.site_id = :site_id
                %s
                ORDER BY resource.id',
                $includePrivate ? '' : 'AND resource.is_public = 1'
            );
            $stmt = $this->get('Omeka\Connection')->prepare($sql);
            $stmt->bindValue('site_id', $this->getStaticSite()->site()->id());
            $itemIds = $stmt->executeQuery()->fetchFirstColumn();
            $this->itemIds = $this->setAddedIds('items', $itemIds);
        }
        return $this->itemIds;
    }

    /**
     * Get the IDs of media that are assigned to this site.
     */
    public function getMediaIds(): array
    {
        if (null === $this->mediaIds) {
            $includePrivate = $this->getStaticSite()->dataValue('include_private');
            $sql = sprintf('SELECT media.id
                FROM media media
                INNER JOIN item_site item_site ON media.item_id = item_site.item_id
                INNER JOIN resource resource ON media.id = resource.id
                WHERE item_site.site_id = :site_id
                %s
                ORDER BY resource.id',
                $includePrivate ? '' : 'AND resource.is_public = 1'
            );
            $stmt = $this->get('Omeka\Connection')->prepare($sql);
            $stmt->bindValue('site_id', $this->getStaticSite()->site()->id());
            $mediaIds = $stmt->executeQuery()->fetchFirstColumn();
            $this->mediaIds = $this->setAddedIds('media', $mediaIds);
        }
        return $this->mediaIds;
    }

    /**
     * Get the IDs of item sets that are assigned to this site.
     */
    public function getItemSetIds(): array
    {
        if (null === $this->itemSetIds) {
            $includePrivate = $this->getStaticSite()->dataValue('include_private');
            $sql = sprintf('SELECT site_item_set.item_set_id
                FROM site_item_set site_item_set
                INNER JOIN resource resource ON site_item_set.item_set_id = resource.id
                WHERE site_item_set.site_id = :site_id
                %s
                ORDER BY resource.id',
                $includePrivate ? '' : 'AND resource.is_public = 1'
            );
            $stmt = $this->get('Omeka\Connection')->prepare($sql);
            $stmt->bindValue('site_id', $this->getStaticSite()->site()->id());
            $itemSetIds = $stmt->executeQuery()->fetchFirstColumn();
            $this->itemSetIds = $this->setAddedIds('item_sets', $itemSetIds);
        }
        return $this->itemSetIds;
    }

    /**
     * Get the IDs of assets that are assigned to this site.
     */
    public function getAssetIds(): array
    {
        if (null === $this->assetIds) {
            $sql = 'SELECT asset.id
                FROM asset asset
                INNER JOIN resource resource ON asset.id = resource.thumbnail_id
                WHERE resource.id IN (?)';
            $assetIds = $this->get('Omeka\Connection')->executeQuery(
                $sql,
                [array_merge($this->getItemIds(), $this->getMediaIds(), $this->getItemSetIds())],
                [Connection::PARAM_INT_ARRAY]
            )->fetchFirstColumn();
            $this->assetIds = $this->setAddedIds('assets', $assetIds);
        }
        return $this->assetIds;
    }

    /**
     * Prepare the site.
     *
     * - Sets the current theme
     * - Enables site settings
     * - Sets the site locale
     */
    public function prepareSite(): void
    {
        $themeManager = $this->get('Omeka\Site\ThemeManager');
        $siteSettings = $this->get('Omeka\Settings\Site');
        $site = $this->getStaticSite()->site();
        $this->currentTheme = $themeManager->getTheme($site->theme());
        $themeManager->setCurrentTheme($this->currentTheme);
        $siteSettings->setTargetId($site->id());
        $locale = $siteSettings->get('locale');
        if ($locale) {
            if (extension_loaded('intl') && 'debug' !== $locale) {
                Locale::setDefault($locale);
            }
            $this->get('MvcTranslator')->getDelegatedTranslator()->setLocale($locale);
        }
    }

    /**
     * Create the static site directory.
     */
    public function createSiteDirectory(): void
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
                    break;
                }
            }
        }

        // Get pager size (pagination per page).
        $settings = $this->get('Omeka\Settings');
        $siteSettings = $this->get('Omeka\Settings\Site');
        $pagerSize = $siteSettings->get('pagination_per_page') ?? $settings->get('pagination_per_page', 25);

        // Make the hugo.json configuration file.
        $siteConfig = new ArrayObject([
            'theme' => 'gohugo-theme-omeka-s',
            'title' => $this->getStaticSite()->site()->title(),
            'menus' => [
                'main' => $menu->getArrayCopy(),
            ],
            'params' => [
                'homepage' => $homepage,
                'theme' => $this->getStaticSite()->dataValue('theme'),
            ],
            'pagination' => [
                'pagerSize' => $pagerSize,
            ],
        ]);

        // Trigger the site_config event.
        $this->triggerEvent(
            'static_site_export.site_config',
            [
                'siteConfig' => $siteConfig,
            ]
        );

        $this->makeFile('hugo.json', json_encode($siteConfig->getArrayCopy(), JSON_PRETTY_PRINT));
    }

    /**
     * Create the static site archive (ZIP).
     */
    public function createSiteArchive(): void
    {
        $command = sprintf(
            '%s %s && %s --recurse-paths %s %s',
            $this->get('Omeka\Cli')->getCommandPath('cd'),
            $this->getSitesDirectoryPath(),
            $this->get('Omeka\Cli')->getCommandPath('zip'),
            sprintf('%s.zip', $this->getStaticSiteName()),
            $this->getStaticSiteName()
        );
        $this->execute($command);
    }

    /**
     * Get the resource page blocks configuration from the site's theme.
     */
    public function getResourcePageBlockLayouts(): array
    {
        if (null === $this->resourcePageBlocks) {
            // Fetch the resolved resource page blocks from theme configuration.
            $resourcePageBlockLayoutManager = $this->get('Omeka\ResourcePageBlockLayoutManager');
            $resourceTypes = $resourcePageBlockLayoutManager->getResourcePageBlocks($this->currentTheme);

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
     * Get resource page blocks for the passed resource.
     */
    public function getResourcePageBlocks(
        string $resourceType,
        AbstractEntityRepresentation $resource,
        ArrayObject $frontMatterPage
    ): ArrayObject {
        $blocks = new ArrayObject;
        $blockLayoutNames = $this->getResourcePageBlockLayouts()[$resourceType];
        foreach ($blockLayoutNames as $blockLayoutName) {
            $frontMatterBlock = new ArrayObject([
                'params' => [
                    'layout' => $blockLayoutName,
                ],
            ]);
            $blockLayout = $this->get('StaticSiteExport\ResourcePageBlockLayoutManager')->get($blockLayoutName);
            $blockMarkdown = $blockLayout->getMarkdown($this, $resource, $frontMatterPage, $frontMatterBlock);
            if ('' === trim($blockMarkdown)) {
                continue;
            }
            $blocks[] = [
                'name' => $blockLayoutName,
                'frontMatter' => $frontMatterBlock,
                'markdown' => $blockMarkdown,
            ];
        }
        return $blocks;
    }

    /**
     * Get blocks for the passed site page.
     */
    public function getSitePageBlocks(SitePageRepresentation $sitePage, ArrayObject $frontMatterPage): ArrayObject
    {
        $blocks = new ArrayObject;
        foreach ($sitePage->blocks() as $block) {
            $frontMatterBlock = new ArrayObject([
                'params' => [
                    'blockId' => $block->id(),
                    'layout' => $block->layout(),
                    'layoutData' => $block->layoutData(),
                    'layoutClasses' => [],
                    'layoutStyles' => [],
                    'data' => $block->data(),
                ],
            ]);
            $blockLayout = $this->get('StaticSiteExport\BlockLayoutManager')->get($block->layout());
            $blockMarkdown = $blockLayout->getMarkdown($this, $block, $frontMatterPage, $frontMatterBlock);

            // Set the resolved classes and inline styles to front matter.
            $blockLayoutHelper = $this->get('ViewHelperManager')->get('blockLayout');
            $classes = $blockLayoutHelper->getBlockClasses($block);
            $styles = $blockLayoutHelper->getBlockInlineStyles($block);
            // Remove the "background-image" style, if any. This will be handled
            // in the layout.
            $styles = array_filter($styles, function ($style) {
                return !preg_match('/^background-image:/', $style);
            });
            // Set special classes on blockGroups when page layout is a grid.
            if ('blockGroup' === $block->layout() && 'grid' === $sitePage->layout()) {
                $classes[] = 'block-group-grid';
                $classes[] = 'grid-position-1';
                $classes[] = sprintf('grid-span-%s', $sitePage->layoutDataValue('grid_columns'));
            }
            $frontMatterBlock['params']['layoutClasses'] = array_merge(
                $frontMatterBlock['params']['layoutClasses'],
                $classes
            );
            $frontMatterBlock['params']['layoutStyles'] = array_merge(
                $frontMatterBlock['params']['layoutStyles'],
                $styles
            );

            $blocks[] = [
                'name' => sprintf('%s-%s', $block->layout(), $block->id()),
                'frontMatter' => $frontMatterBlock,
                'markdown' => $blockMarkdown,
            ];
        }
        return $blocks;
    }

    /**
     * Make bundle files, including the index page and its block resources.
     *
     * Each block in $blocks must be an array containing the following elements:
     *   - "name" (string): the unique block name
     *   - "frontMatter" (ArrayObject): the block front matter
     *   - "markdown" (string): the block markdown
     */
    public function makeBundleFiles(
        string $resourceContentPath,
        AbstractEntityRepresentation $resource,
        ArrayObject $frontMatterPage,
        ArrayObject $blocks
    ): void {
        // Make the block files.
        $blockPosition = 0;
        foreach ($blocks as $block) {
            // Pad block numbers to get natural sorting for free.
            $blockNumber = str_pad($blockPosition++, 4, '0', STR_PAD_LEFT);
            $this->makeFile(
                sprintf('content/%s/blocks/%s-%s.md', $resourceContentPath, $blockNumber, $block['name']),
                sprintf("%s\n%s", json_encode($block['frontMatter'], JSON_PRETTY_PRINT), $block['markdown'])
            );
        }
        // Make the markdown file.
        $this->makeFile(
            sprintf('content/%s/index.md', $resourceContentPath),
            json_encode($frontMatterPage, JSON_PRETTY_PRINT)
        );
        // Make the JSON file.
        $this->makeFile(
            sprintf('content/%s/index.json', $resourceContentPath),
            json_encode($resource, JSON_PRETTY_PRINT)
        );
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
    public function escape(array $characters, string $string): string
    {
        foreach ($characters as $character) {
            $string = str_replace($character, sprintf('\%s', $character), $string);
        }
        return $string;
    }

    /**
     * Translate a message.
     */
    public function translate($message)
    {
        return $this->get('MvcTranslator')->translate($message);
    }

    /**
     * Get the markdown for a link to a resource, including thumbnail.
     *
     * Options are the following:
     *
     *  - thumbnailType: The type of thumbnail, large, medium, or square (default: null)
     *  - thumbnailHeight: The height of the thumbnailm preserving aspect ratio (default: null)
     */
    public function getLinkMarkdown(AbstractResourceEntityRepresentation $resource, array $options = []): string
    {
        $defaultOptions = [
            'thumbnailType' => null,
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
            $options['thumbnailType'] ? $this->getThumbnailShortcode($resource, $options) : '',
            $this->escape(['[', ']'], $resource->displayTitle()),
            $resourceTypePath,
            $resource->id(),
            $this->escape(['"'], $resource->displayTitle()),
        );
    }

    /**
     * Get the markdown for the values of a resource.
     */
    public function getValuesMarkdown(
        AbstractResourceEntityRepresentation $resource,
        ArrayObject $frontMatterPage,
        ArrayObject $frontMatterBlock
    ): string {
        $markdown = [];
        foreach ($resource->values() as $term => $valueData) {
            $property = $valueData['property'];
            $altLabel = $valueData['alternate_label'];
            $altComment = $valueData['alternate_comment'];
            $markdown[] = sprintf("%s", $altLabel ?? $this->translate($property->label()));
            foreach ($valueData['values'] as $value) {
                $dataType = $this->get('StaticSiteExport\DataTypeManager')->get($value->type());
                $markdown[] = sprintf(': %s', $dataType->getMarkdown($this, $value, $frontMatterPage, $frontMatterBlock));
            }
            $markdown[] = '';
        }
        return $markdown ? implode("\n", $markdown) : '';
    }

    /**
     * Get the markdown for the item sets of an item.
     */
    public function getItemSetListMarkdown(AbstractResourceEntityRepresentation $resource): string
    {
        $markdown = [$this->translate('Item sets')];
        foreach ($resource->itemSets() as $itemSet) {
            if (!in_array($itemSet->id(), $this->getItemSetIds())) {
                continue; // Item set not in site.
            }
            $markdown[] = sprintf(
                ': %s',
                $this->getLinkMarkdown($itemSet, [
                    'thumbnailType' => 'square',
                    'thumbnailHeight' => 40,
                ])
            );
        }
        return implode("\n", $markdown);
    }

    /**
     * Get the markdown for the media of an item.
     */
    public function getMediaListMarkdown(AbstractResourceEntityRepresentation $resource): string
    {
        $markdown = [$this->translate('Media')];
        foreach ($resource->media() as $media) {
            if (!in_array($media->id(), $this->getMediaIds())) {
                continue; // Media not in site.
            }
            $markdown[] = sprintf(
                ': %s',
                $this->getLinkMarkdown($media, [
                    'thumbnailType' => 'square',
                    'thumbnailHeight' => 40,
                ])
            );
        }
        return implode("\n", $markdown);
    }

    /**
     * Get the markdown for an oEmbed.
     */
    public function getOembedMarkdown(array $data): string
    {
        if (isset($data['html'])) {
            return sprintf('{{< omeka-html >}}%s{{< /omeka-html >}}', $data['html']);
        }
        $type = $data['type'] ?? null;
        if ('photo' === $type && isset($data['url'])) {
            return sprintf(
                '{{< figure src="%s" width="%s" height="%s" alt="%s" >}}',
                $data['url'],
                $data['width'] ?? '',
                $data['height'] ?? '',
                $data['title'] ?? ''
            );
        }
        return '';
    }

    /**
     * Get the "omeka-figure" shortcode using the passed arguments.
     */
    public function getFigureShortcode(array $args): string
    {
        $shortcodeArgs = [];
        foreach ($args as $key => $value) {
            $shortcodeArgs[] = sprintf('%s="%s"', $key, $value);
        }
        return sprintf('{{< omeka-figure %s >}}', implode(' ', $shortcodeArgs));
    }

    /**
     * Get the "omeka-thumbnail" shortcode for the passed resource.
     */
    public function getThumbnailShortcode(AbstractResourceEntityRepresentation $resource, array $options = []): string
    {
        $defaultOptions = [
            'thumbnailType' => 'large',
            'thumbnailHeight' => null,
            'linkPage' => null,
            'linkResource' => null,
        ];
        $options = array_merge($defaultOptions, $options);
        $thumbnailSpec = $this->getThumbnailSpec($resource, $options['thumbnailType']);
        if (!$thumbnailSpec['resource']) {
            return '';
        }
        return sprintf(
            '{{< omeka-thumbnail imgPage="%s" imgResource="%s" linkPage="%s" linkResource="%s" height="%s" >}}',
            $thumbnailSpec['page'],
            $thumbnailSpec['resource'],
            $options['linkPage'],
            $options['linkResource'],
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
     *  5. The default thumbnail if there's a primary media
     */
    public function getThumbnailSpec(AbstractEntityRepresentation $resource, string $thumbnailType): array
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
            } elseif ($primaryMedia) {
                $thumbnailResource = '/thumbnails/default.png';
            }
        }
        return [
            'page' => $thumbnailPage,
            'resource' => $thumbnailResource,
        ];
    }
}
