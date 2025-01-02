<?php
namespace StaticSiteExport;

use Laminas\Router\Http;

return [
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
