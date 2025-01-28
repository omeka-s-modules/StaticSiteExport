<?php
namespace StaticSiteExport;

use Laminas\Router\Http;

$shortcodesPath = sprintf('%s/modules/StaticSiteExport/data/shortcodes', OMEKA_PATH);

return [
    'static_site_export' => [
        'shortcodes' => [
            'omeka-figure' => sprintf('%s/omeka-figure.html', $shortcodesPath),
            'omeka-html' => sprintf('%s/omeka-html.html', $shortcodesPath),
            'omeka-literal' => sprintf('%s/omeka-literal.html', $shortcodesPath),
            'omeka-thumbnail' => sprintf('%s/omeka-thumbnail.html', $shortcodesPath),
        ],
        'block_layouts' => [
            'invokables' => [
                'pageTitle' => BlockLayout\PageTitle::class,
                'media' => BlockLayout\Media::class,
                // 'browsePreview' => Site\BlockLayout\BrowsePreview::class,
                // 'listOfSites' => Site\BlockLayout\ListOfSites::class,
                // 'tableOfContents' => Site\BlockLayout\TableOfContents::class,
                // 'lineBreak' => Site\BlockLayout\LineBreak::class,
                'itemWithMetadata' => BlockLayout\ItemWithMetadata::class,
                'pageDateTime' => Site\BlockLayout\PageDateTime::class,
                // 'blockGroup' => Site\BlockLayout\BlockGroup::class,
                'asset' => BlockLayout\Asset::class,
                'html' => BlockLayout\Html::class,
                // 'listOfPages' => BlockLayout\PageList::class,
                'oembed' => BlockLayout\Oembed::class,
            ],
        ],
        'data_types' => [
            'invokables' => [
                'literal' => DataType\Literal::class,
                'uri' => DataType\Uri::class,
                'resource' => DataType\Resource::class,
            ],
            'aliases' => [
                'resource:item' => 'resource',
                'resource:itemset' => 'resource',
                'resource:media' => 'resource',
            ],
        ],
        'file_renderers' => [
            'invokables' => [
                'thumbnail' => FileRenderer\Thumbnail::class,
                'audio' => FileRenderer\Audio::class,
                'video' => FileRenderer\Video::class,
            ],
            'aliases' => [
                'audio/ogg' => 'audio',
                'audio/x-aac' => 'audio',
                'audio/mpeg' => 'audio',
                'audio/mp4' => 'audio',
                'audio/x-wav' => 'audio',
                'audio/x-aiff' => 'audio',
                'application/ogg' => 'video',
                'video/mp4' => 'video',
                'video/quicktime' => 'video',
                'video/x-msvideo' => 'video',
                'video/ogg' => 'video',
                'video/webm' => 'video',
                'mp3' => 'audio',
            ],
        ],
        'media_renderers' => [
            'invokables' => [
                'youtube' => MediaRenderer\Youtube::class,
                'html' => MediaRenderer\Html::class,
                // 'iiif' => MediaRenderer\IIIF::class,
                // 'iiif_presentation' => MediaRenderer\IiifPresentation::class,
                'file' => MediaRenderer\File::class,
                'oembed' => MediaRenderer\Oembed::class,
            ],
        ],
        'resource_page_block_layouts' => [
            'invokables' => [
                'itemSets' => ResourcePageBlockLayout\ItemSets::class,
                'linkedResources' => ResourcePageBlockLayout\LinkedResources::class,
                'mediaList' => ResourcePageBlockLayout\MediaList::class,
                'mediaRender' => ResourcePageBlockLayout\MediaRender::class,
                'resourceClass' => ResourcePageBlockLayout\ResourceClass::class,
                'values' => ResourcePageBlockLayout\Values::class,
            ],
        ],
    ],
    'entity_manager' => [
        'mapping_classes_paths' => [
            dirname(__DIR__) . '/src/Entity',
        ],
        'proxy_paths' => [
            dirname(__DIR__) . '/data/doctrine-proxies',
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            sprintf('%s/../view', __DIR__),
        ],
    ],
    'translator' => [
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => sprintf('%s/../language', __DIR__),
                'pattern' => '%s.mo',
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            'StaticSiteExport\BlockLayoutManager' => Service\BlockLayoutManagerFactory::class,
            'StaticSiteExport\DataTypeManager' => Service\DataTypeManagerFactory::class,
            'StaticSiteExport\FileRendererManager' => Service\FileRendererManagerFactory::class,
            'StaticSiteExport\MediaRendererManager' => Service\MediaRendererManagerFactory::class,
            'StaticSiteExport\ResourcePageBlockLayoutManager' => Service\ResourcePageBlockLayoutManagerFactory::class,
        ],
    ],
    'api_adapters' => [
        'invokables' => [
            'static_site_export_static_sites' => Api\Adapter\StaticSiteAdapter::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            'StaticSiteExport\Controller\SiteAdmin\Index' => Service\Controller\SiteAdmin\IndexControllerFactory::class,
        ],
    ],
    'navigation' => [
        'site' => [
            [
                'label' => 'Static Site Export', // @translate
                'route' => 'admin/site/slug/static-site-export',
                'action' => 'index',
                'useRouteMatch' => true,
                'resource' => 'StaticSiteExport\Controller\SiteAdmin\Index',
                'privilege' => 'index',
                'pages' => [
                    [
                        'route' => 'admin/site/slug/static-site-export',
                        'visible' => false,
                    ],
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'admin' => [
                'child_routes' => [
                    'site' => [
                        'child_routes' => [
                            'slug' => [
                                'child_routes' => [
                                    'static-site-export' => [
                                        'type' => Http\Segment::class,
                                        'options' => [
                                            'route' => '/static-site-export[/:action[/:id]]',
                                            'constraints' => [
                                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                                'id' => '\d+',
                                            ],
                                            'defaults' => [
                                                '__NAMESPACE__' => 'StaticSiteExport\Controller\SiteAdmin',
                                                'controller' => 'index',
                                                'action' => 'browse',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
?>
