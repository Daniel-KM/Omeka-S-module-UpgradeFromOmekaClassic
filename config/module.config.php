<?php

// Here is the slug of the site to use as main site in order to keep aliases
// from Omeka Classic. It must be an existing and public site.
// If it is not used, comment the router array below.
// $siteSlug = 'replace by the slug of the main site';
$siteSlug = '';

$config = [
    'view_helpers' => [
        'invokables' => [
            'allElementTexts' => 'UpgradeFromOmekaClassic\View\Helper\AllElementTexts',
            'fileMarkup' => 'UpgradeFromOmekaClassic\View\Helper\FileMarkup',
            'metadata' => 'UpgradeFromOmekaClassic\View\Helper\Metadata',
        ],
        'factories' => [
            'upgrade' => 'UpgradeFromOmekaClassic\Service\ViewHelper\UpgradeFactory',
        ],
    ],
];

if ($siteSlug) {
    // Two routes of Omeka Classic are aliased by default:
    // - "items/show/:id". This is the most important, because documents are the
    // main part of a digital library. The other records (resources: collections
    // and files) can’t be aliased in a simple way, because they lost their id
    // during the upgrade.
    // - classic home page: the default home page of Omeka S displays all sites,
    // but is not funny by default. This list can be inserted as a new block in
    // classic home page.
    // Comment the routes you don't want.
    // TODO Add a dcterms:replaces / isReplacedBy for items, collections and files during upgrade and alias them.
    // TODO Add alias for Omeka Classic simple pages and exhibits (slug is kept, but the site path is added).
    // The route for the home page may be changed below too.
    $config['router'] = [
        'routes' => [
            // Aliases from the items/show pages of Omeka Classic to the
            // upgraded items of the specified site of Omeka S.
            'items-show-id' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/items/show/:id',
                    'defaults' => [
                        '__NAMESPACE__' => 'Omeka\Controller\Site',
                        '__SITE__'      => true,
                        'controller'    => 'item',
                        'action'        => 'show',
                        'id'            => '\d+',
                        // Here is the slug of the site to use as main site.
                        // It must be an existing and public site.
                        'site-slug'     => $siteSlug,
                    ],
                ],
            ],
            // Alias for the home page of Omeka Classic (root of Omeka S).
            // NOTE This removes the list of the sites (and there is no block
            // for that currently).
            'classic' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/',
                    'defaults' => [
                        '__NAMESPACE__' => 'Omeka\Controller\Site',
                        '__SITE__'      => true,
                        'controller'    => 'Page',
                        'action'        => 'show',
                        // Here is the slug of the site to use as main site.
                        // It must be an existing and public site.
                        'site-slug'     => $siteSlug,
                        // Here is the slug of the page to use as home page for
                        // the main site. It must be an existing public page.
                        'page-slug'     => 'homepage-site',
                    ],
                ],
            ],
        ],
    ];
}

return $config;
