<?php

namespace UpgradeFromOmekaClassic;

use Omeka\Module\AbstractModule;
use Zend\EventManager\Event;
use Zend\EventManager\SharedEventManagerInterface;
use Zend\Form\Fieldset;
use Zend\Form\Element\Text;
use Zend\Form\Element\Checkbox;
use Zend\Form\Element\MultiCheckbox;

class Module extends AbstractModule
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager)
    {
        $sharedEventManager->attach(
            'Omeka\Form\SiteSettingsForm',
            'form.add_elements',
            [$this, 'addSiteSettingsFormElements']
        );

        $sharedEventManager->attach(
            'Omeka\Form\SiteSettingsForm',
            'form.add_input_filters',
            [$this, 'addSiteSettingsFormFilters']
        );
    }

    public function addSiteSettingsFormElements(Event $event)
    {
        $services = $this->getServiceLocator();
        $siteSettings = $services->get('Omeka\Settings\Site');
        $config = $services->get('Config');
        $form = $event->getTarget();

        $defaultSiteSettings = $config[strtolower(__NAMESPACE__)]['site_settings'];

        $fieldset = new Fieldset('upgrade_from');
        $fieldset->setLabel('Upgrade from Omeka Classic');

        $fieldset->add([
            'name' => 'upgrade_use_advanced_search',
            'type' => Checkbox::class,
            'options' => [
                'label' => 'Use advanced site-wide search', // @translate
                'info' => 'Check this box if you wish to allow users to search your whole site by record (i.e. item, item set, media).', // @translate
            ],
            'attributes' => [
                'value' => $siteSettings->get(
                    'upgrade_use_advanced_search',
                    $defaultSiteSettings['upgrade_use_advanced_search']
                ),
            ],
        ]);

        $searchResourceTypes = $siteSettings->get(
            'upgrade_search_resource_types',
            $defaultSiteSettings['upgrade_search_resource_types']
        );
        $valueOptions = [
            'Item' => [
                'label' => 'Item', // @translate
                'value' => 'Item',
                'selected' => in_array('Item', $searchResourceTypes),
            ],
            'ItemSet' => [
                'label' => 'Item set', // @translate
                'value' => 'ItemSet',
                'selected' => in_array('ItemSet', $searchResourceTypes),
            ],
            'Media' => [
                'label' => 'Media', // @translate
                'value' => 'Media',
                'selected' => in_array('Media', $searchResourceTypes),
            ],
            'Page' => [
                'label' => 'Page', // @translate
                'value' => 'Page',
                'selected' => in_array('Page', $searchResourceTypes),
                'disabled' => true,
            ],
        ];
        $fieldset->add([
            'name' => 'upgrade_search_resource_types',
            'type' => MultiCheckbox::class,
            'options' => [
                'label' => 'Search resources types', // @translate
                'info' => 'Customize which types of resources will be searchable in Omeka.', // @translate
                'value_options' => $valueOptions,
            ],
            'attributes' => [
                'value' => $searchResourceTypes,
                // 'disabled' => true,
            ],
        ]);

        $fieldset->add([
            'name' => 'upgrade_show_vocabulary_headings',
            'type' => Checkbox::class,
            'options' => [
                'label' => 'Show vocabulary headings', // @translate
            ],
            'attributes' => [
                'value' => $siteSettings->get(
                    'upgrade_show_vocabulary_headings',
                    $defaultSiteSettings['upgrade_show_vocabulary_headings']
                ),
            ],
        ]);

        $fieldset->add([
            'name' => 'upgrade_show_empty_properties',
            'type' => Checkbox::class,
            'options' => [
                'label' => 'Show empty properties', // @translate
            ],
            'attributes' => [
                'value' => $siteSettings->get(
                    'upgrade_show_empty_properties',
                    $defaultSiteSettings['upgrade_show_empty_properties']
                ),
            ],
        ]);

        $fieldset->add([
            'name' => 'upgrade_use_square_thumbnail',
            'type' => Checkbox::class,
            'options' => [
                'label' => 'Use square thumbnails', // @translate
                'info' => 'Use square-cropped images by default wherever thumbnails appear in the public interface.', // @translate
            ],
            'attributes' => [
                'value' => $siteSettings->get(
                    'upgrade_use_square_thumbnail',
                    $defaultSiteSettings['upgrade_use_square_thumbnail']
                ),
            ],
        ]);

        $fieldset->add([
            'name' => 'upgrade_tag_delimiter',
            'type' => Text::class,
            'options' => [
                'label' => 'Tag delimiter', // @translate
                'info' => 'Separate tags using this character or string. Be careful when changing this setting. You run the risk of splitting tags that contain the old delimiter.', // @translate
            ],
            'attributes' => [
                'value' => $siteSettings->get(
                    'upgrade_tag_delimiter',
                    $defaultSiteSettings['upgrade_tag_delimiter']
                ),
                'disabled' => true,
            ],
        ]);

        $form->add($fieldset);
    }

    public function addSiteSettingsFormFilters(Event $event)
    {
        $inputFilter = $event->getParam('inputFilter');
        $inputFilter->get('upgrade_from')->add([
            'name' => 'upgrade_search_resource_types',
            'required' => false,
        ]);
    }
}
