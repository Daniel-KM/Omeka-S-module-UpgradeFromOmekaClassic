<?php
namespace UpgradeFromOmekaClassic;

return [
    'view_helpers' => [
        'invokables' => [
            'allElementTexts' => View\Helper\AllElementTexts::class,
            'fileMarkup' => View\Helper\FileMarkup::class,
            'metadata' => View\Helper\Metadata::class,
        ],
        'factories' => [
            'upgrade' => Service\ViewHelper\UpgradeFactory::class,
        ],
    ],
    'form_elements' => [
        'invokables' => [
            Form\ConfigForm::class => Form\ConfigForm::class,
        ],
    ],
    'upgradefromomekaclassic' => [
        'config' => [
            'upgrade_add_old_routes' => false,
        ],
        'site_settings' => [
            'upgrade_use_advanced_search' => false,
            'upgrade_search_resource_types' => [],
            'upgrade_show_vocabulary_headings' => true,
            'upgrade_show_empty_properties' => false,
            'upgrade_use_square_thumbnail' => true,
            'upgrade_tag_delimiter' => ',',
        ],
        'dependencies' => [
            'Next',
        ],
    ],
];
