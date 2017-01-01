<?php

namespace UpgradeFromOmekaClassic;

use Omeka\Module\AbstractModule;
use Zend\EventManager\Event;
use Zend\EventManager\SharedEventManagerInterface;
use Zend\Form\Fieldset;

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

    public function addSiteSettingsFormElements($event)
    {
        $siteSettings = $this->getServiceLocator()->get('Omeka\SiteSettings');
        $form = $event->getTarget();

        $fieldset = new Fieldset('upgrade_from');
        $fieldset->setLabel('Upgrade from Omeka Classic');

        $useAdvancedSearch = (boolean) $siteSettings->get('upgrade_use_advanced_search', false);
        $searchResourceTypes = $siteSettings->get('upgrade_search_resource_types', []);
        $showVocabularyHeadings = (boolean) $siteSettings->get('upgrade_show_vocabulary_headings', true);
        $showEmptyProperties = (boolean) $siteSettings->get('upgrade_show_empty_properties', false);
        $useSquareThumbnail = (boolean) $siteSettings->get('upgrade_use_square_thumbnail', true);
        $tagDelimiter = $siteSettings->get('upgrade_tag_delimiter', ',');

        $fieldset->add([
            'name' => 'upgrade_use_advanced_search',
            'type' => 'checkbox',
            'options' => [
                'label' => 'Use Advanced Site-wide Search', // @translate
                'info' => 'Check this box if you wish to allow users to search your whole site by record (i.e. item, item set, media).', // @translate
            ],
            'attributes' => [
                'value' => $useAdvancedSearch,
            ],
        ]);

        $valueOptions = [
            'Item' => [
                'label' => 'Item', // @translate
                'value' => 'Item',
                'selected' => in_array('Item', $searchResourceTypes),
            ],
            'ItemSet' => [
                'label' => 'Item Set', // @translate
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
            'type' => 'multiCheckbox',
            'options' => [
                'label' => 'Search Resources Types', // @translate
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
            'type' => 'checkbox',
            'options' => [
                'label' => 'Show Vocabulary Headings', // @translate
            ],
            'attributes' => [
                'value' => $showVocabularyHeadings,
            ],
        ]);

        $fieldset->add([
            'name' => 'upgrade_show_empty_properties',
            'type' => 'checkbox',
            'options' => [
                'label' => 'Show Empty Properties', // @translate
            ],
            'attributes' => [
                'value' => $showEmptyProperties,
            ],
        ]);

        $fieldset->add([
            'name' => 'upgrade_use_square_thumbnail',
            'type' => 'checkbox',
            'options' => [
                'label' => 'Use Square Thumbnails', // @translate
                'info' => 'Use square-cropped images by default wherever thumbnails appear in the public interface.', // @translate
            ],
            'attributes' => [
                'value' => $useSquareThumbnail,
            ],
        ]);

        $fieldset->add([
            'name' => 'upgrade_tag_delimiter',
            'type' => 'Text',
            'options' => [
                'label' => 'Tag Delimiter', // @translate
                'info' => 'Separate tags using this character or string. Be careful when changing this setting. You run the risk of splitting tags that contain the old delimiter.', // @translate
            ],
            'attributes' => [
                'value' => $tagDelimiter,
                'disabled' => true,
            ],
        ]);

        $form->add($fieldset);
    }

    public function addSiteSettingsFormFilters($event)
    {
        $inputFilter = $event->getParam('inputFilter');
        $inputFilter->get('upgrade_from')->add([
            'name' => 'upgrade_search_resource_types',
            'required' => false,
        ]);
    }
}
