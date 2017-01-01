<?php
namespace UpgradeFromOmekaClassic\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\View\Exception\InvalidArgumentException;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Api\Representation\ItemRepresentation;
use Omeka\Api\Representation\PropertyRepresentation;

/**
 * Omeka
 *
 * @note Upgraded from Omeka Classic via the plugin "Upgrade to Omeka S".
 * @link https://github.com/Daniel-KM/UpgradeToOmekaS
 * @link https://github.com/Daniel-KM/UpgradeFromOmekaClassic
 * @todo Replaced by $resource->values() and ->displayValues() in most cases.
 *
 * @copyright Copyright 2007-2012 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * View helper for retrieving lists of metadata for any record that uses
 * Mixin_ElementText.
 *
 * @package Omeka\View\Helper
 */
// class Omeka_View_Helper_AllElementTexts extends Zend_View_Helper_Abstract
class AllElementTexts extends AbstractHelper
{
    const RETURN_HTML = 'html';
    const RETURN_ARRAY = 'array';

    /**
     * The record being printed.
     * @var Omeka_Record_AbstractRecord
     */
    protected $_record;

    /**
     * Flag to indicate whether to show elements that do not have text.
     * @see self::$_emptyElementString
     * @var boolean
     */
    protected $_showEmptyElements = true;

    /**
     * Whether to include a heading for each Element Set.
     * @var boolean
     */
    protected $_showElementSetHeadings = true;

    /**
     * String to display if elements without text are shown.
     * @see self::$_showEmptyElements
     * @var string
     */
    protected $_emptyElementString;

    /**
     * Element sets to list.
     *
     * @var array
     */
    protected $_elementSetsToShow = array();

    /**
     * Type of data to return.
     *
     * @var string
     */
    protected $_returnType = self::RETURN_HTML;

    /**
     * Path for the view partial.
     *
     * @var string
     */
    // protected $_partial = 'common/record-metadata.php';
    protected $_partial = 'common/record-metadata.phtml';

    /**
     * Get the record metadata list.
     *
     * @param Omeka_Record_AbstractRecord|string $record Record to retrieve
     *  metadata from.
     * @param array $options
     *  Available options:
     *  - show_empty_elements' => bool|string Whether to show elements that
     *    do not contain text. A string will set self::$_showEmptyElements to
     *    true and set self::$_emptyElementString to the provided string.
     *  - 'show_element_sets' => array List of names of element sets to display.
     *  - 'return_type' => string 'array', 'html'.  Defaults to 'html'.
     * @since 1.0 Added 'show_element_sets' and 'return_type' options.
     * @return string|array
     */
    // public function allElementTexts($record, array $options = array())
    public function __invoke($record, array $options = array())
    {
        // Use $record->values() in most cases.
        if (empty($record)) {
            return;
        }

        if (is_string($record)) {
    //         $record = $this->view->{$this->view->singularize($record)};
            $record = $this->getView()->upgrade()->get_current_record($record);
        }

    //     if (!($record instanceof Omeka_Record_AbstractRecord)) {
    //         throw new InvalidArgumentException('Invalid record passed to recordMetadata.');
        if (!($record instanceof AbstractResourceEntityRepresentation)) {
            throw new InvalidArgumentException('Invalid resource passed to values().');
        }

        $this->_record = $record;
        $this->_setOptions($options);
        return $this->_getOutput();
    }

    /**
     * Set the options.
     *
     * @param array $options
     * @return void
     */
    protected function _setOptions(array $options)
    {
        $upgrade = $this->getView()->upgrade();

        // Set default options based on site settings
    //     $this->_showEmptyElements = (bool) get_option('show_empty_elements');
    //     $this->_showElementSetHeadings = (bool) get_option('show_element_set_headings');
    //     $this->_emptyElementString = __('[no text]');
        $this->_showEmptyElements = (bool) $upgrade->get_option('upgrade_show_empty_properties');
        $this->_showElementSetHeadings = (bool) $upgrade->get_option('upgrade_show_vocabulary_headings');
        $this->_emptyElementString = $upgrade->stranslate('[no text]');

        // Handle show_empty_elements option
    //     if (array_key_exists('show_empty_elements', $options)) {
    //         if (is_string($options['show_empty_elements'])) {
    //             $this->_emptyElementString = $options['show_empty_elements'];
    //         } else {
    //             $this->_showEmptyElements = (bool) $options['show_empty_elements'];
    //         }
    //     }

    //     if (array_key_exists('show_element_set_headings', $options)) {
    //         $this->_showElementSetHeadings = (bool) $options['show_element_set_headings'];
    //     }

        if (array_key_exists('upgrade_show_empty_properties', $options)) {
            if (is_string($options['upgrade_show_empty_properties'])) {
                $this->_emptyElementString = $options['upgrade_show_empty_properties'];
            } else {
                $this->_showEmptyElements = (bool) $options['upgrade_show_empty_properties'];
            }
        }

        if (array_key_exists('upgrade_show_vocabulary_headings', $options)) {
            $this->_showElementSetHeadings = (bool) $options['upgrade_show_vocabulary_headings'];
        }

        if (array_key_exists('show_element_sets', $options)) {
            $namesOfElementSetsToShow = $options['show_element_sets'];
            if (is_string($namesOfElementSetsToShow)) {
                $this->_elementSetsToShow = array_map('trim', explode(',', $namesOfElementSetsToShow));
            } else {
                $this->_elementSetsToShow = $namesOfElementSetsToShow;
            }
        }

        if (array_key_exists('return_type', $options)) {
            $this->_returnType = (string)$options['return_type'];
        }

        if (array_key_exists('partial', $options)) {
            $this->_partial = (string)$options['partial'];
        }

    }

    /**
     * Get an array of all element sets containing their respective elements.
     *
     * @uses Item::getAllElements()
     * @uses Item::getItemTypeElements()
     * @return array
     */
    protected function _getElementsBySet()
    {
        $upgrade = $this->getView()->upgrade();

    //     $elementsBySet = $this->_record->getAllElements();

        // In Omeka S, properties are too many, so use the template if possible,
        // else use the full list, but with ordered properties for the Dublin Core.
        $elementsBySet = [];
        $resourceTemplate = $this->_record->resourceTemplate();
        if ($resourceTemplate) {
            foreach ($resourceTemplate->resourceTemplateProperties() as $templateProperty) {
                $property = $templateProperty->property();
                $elementsBySet[$property->vocabulary()->prefix()][$property->localName()] = $property;
            }
            // Properties that are set, but not in the template are added too.
            foreach ($this->_record->values() as $value) {
                $property = $value['property'];
                $elementsBySet[$property->vocabulary()->prefix()][$property->localName()] = $property;
            }
        }
        // Use all properties when there is no resource template.
        else {
            $result = $this->getView()->api()->search('vocabularies')->getContent();
            foreach ($result as $vocabulary) {
                // Order the Dublin Core according to the id instead of by name.
                $properties = $vocabulary->properties();
                if ($vocabulary->prefix() == 'dcterms') {
                    uasort($properties, function($a, $b) {
                        return $a->id() < $b->id() ? -1 : 1;
                    });
                }
                foreach ($properties as $property) {
                    $elementsBySet[$vocabulary->prefix()][$property->localName()] = $property;
                }
            }
        }

        // Only show the element sets that are passed in as options.
        if (!empty($this->_elementSetsToShow)) {
            // In Omeka S, the prefixes should be fetched and upgraded before.
            foreach ($this->_elementSetsToShow as &$elementSet) {
                $elementSet = $upgrade->mapElementSetToVocabulary($elementSet);
            }
            $elementsBySet = array_intersect_key($elementsBySet, array_flip($this->_elementSetsToShow));
        }

        $elementsBySet = $this->_filterItemTypeElements($elementsBySet);

        return $upgrade->apply_filters('display_elements', $elementsBySet);
    }

    /**
     * Filter the display of the Item Type element set, if present.
     *
     * @param array $elementsBySet
     * @return array
     */
    protected function _filterItemTypeElements($elementsBySet)
    {
        // NOTE This is managed differently in Omeka S, so skip the filter.
        return $elementsBySet;

    //     if ($this->_record instanceof Item) {
    //         if ($this->_record->item_type_id) {
        if ($this->_record instanceof ItemRepresentation) {
            if ($this->_record->resourceClass()) {
                // Overwrite elements assigned to the item type element set with only
                // those that belong to this item's particular item type. This is
                // necessary because, otherwise, all item type elements will be shown.
    //             $itemTypeElementSetName = $this->_record->getProperty('item_type_name') . ' ' . ElementSet::ITEM_TYPE_NAME;
                $itemTypeElementSetName = $this->_record->resourceClass()->resourceName();

                // Check to see if either the generic or specific Item Type element
                // set has been chosen, i.e. 'Item Type Metadata' or 'Document
                // Item Type Metadata', etc.

    //             $itemTypeElements = $this->_record->getItemTypeElements();
                // TODO Useless in Omeka S.
                $itemTypeElements = array();

                if (!empty($this->_elementSetsToShow)) {
                    if (in_array($itemTypeElementSetName, $this->_elementSetsToShow) or
    //                 in_array(ElementSet::ITEM_TYPE_NAME, $this->_elementSetsToShow)) {
                    in_array('Item Type Metadata', $this->_elementSetsToShow)) {
                            $elementsBySet[$itemTypeElementSetName] = $itemTypeElements;
                    }
                }
                else {
                    $elementsBySet[$itemTypeElementSetName] = $itemTypeElements;
                }
            }

            // Unset the existing 'Item Type' element set b/c it shows elements
            // for all item types.
    //         unset($elementsBySet[ElementSet::ITEM_TYPE_NAME]);
            unset($elementsBySet['Item Type Metadata']);
        }

        return $elementsBySet;
    }

    /**
     * Determine if an element is allowed to be shown.
     *
     * @param Element $element
     * @param array $texts
     * @return boolean
     */
    // protected function _elementIsShowable(Element $element, $texts)
    protected function _elementIsShowable(PropertyRepresentation $element, $texts)
    {
        return $this->_showEmptyElements || !empty($texts);
    }

    /**
     * Return a formatted version of all the texts for the requested element.
     *
     * @param Omeka_Record_AbstractRecord $record
     * @param array $metadata
     * @return array
     */
    protected function _getFormattedElementTexts($record, $metadata)
    {
        return $this->view->metadata($record, $metadata, array('all' => true));
    }

    /**
     * Output the default HTML format for displaying record metadata.
     * @return string
     */
    protected function _getOutputAsHtml()
    {
        $upgrade = $this->getView()->upgrade();

        // Prepare the metadata for display on the partial.  There should be no
        // need for method calls by default in the view partial.
        $elementSets = $this->_getElementsBySet();
    //     $emptyString = html_escape(__($this->_emptyElementString));
        $emptyString = $upgrade->html_escape($upgrade()->stranslate($this->_emptyElementString));
        $elementsForDisplay = array();
    //     foreach ($elementSets as $setName => $elementsInSet) {
        foreach ($elementSets as $prefix => $elementsInSet) {
            $setInfo = array();
            // For compatibility with old themes, use the label instead of the prefix.
            $firstProperty = reset($elementsInSet);
            $setName = $firstProperty->vocabulary()->label();
    //         foreach ($elementsInSet as $elementName => $element) {
            foreach ($elementsInSet as $elementLocalName => $element) {
                $elementName = $element->label();
                $elementTexts = $this->_getFormattedElementTexts(
    //                 $this->_record, array($element->set_name, $element->name)
                    $this->_record, array($setName, $elementName)
                );
                if (!$this->_elementIsShowable($element, $elementTexts)) {
                    continue;
                }

                // Use two keys for compatibility between old and new templates.
                $displayInfo = array();
                $displayInfo['element'] = $element;
                if (empty($elementTexts)) {
                    $displayInfo['texts'] = array($emptyString);
                } else {
                    $displayInfo['texts'] = $elementTexts;
                }

                $setInfo[$elementName] = $displayInfo;
            }
            if (!empty($setInfo)) {
                $elementsForDisplay[$setName] = $setInfo;
            }
        }
        // We're done preparing the data for display, so display it.
        return $this->_loadViewPartial(array(
            'elementsForDisplay' => $elementsForDisplay,
            // Set values too for compatibility between templates.
            'values' => $this->_record->values(),
            'record' => $this->_record,
            'showElementSetHeadings' => $this->_showElementSetHeadings
        ));
    }

    /**
     * Get the metadata list as a PHP array.
     *
     * @return array
     */
    protected function _getOutputAsArray()
    {
        // NOTE Use $record->values() in that case.
        // NOTE The set name is the prefix of the vocabulary.
        $elementSets = $this->_getElementsBySet();
        $outputArray = array();
        foreach ($elementSets as $setName => $elementsInSet) {
            $outputArray[$setName] = array();
    //         foreach ($elementsInSet as $key => $element) {
    //             $elementName = $element->name;
    //             $textArray = $this->_getFormattedElementTexts($this->_record, array($element->set_name, $elementName));
            foreach ($elementsInSet as $elementName => $element) {
                $textArray = $this->_getFormattedElementTexts($this->_record, array($setName, $elementName));
                if (!empty($textArray[0]) or $this->_showEmptyElements) {
                    $outputArray[$setName][$elementName] = $textArray;
                }
            }
        }
        return $outputArray;
    }

    /**
     * Get the metadata list.
     *
     * @return string|array
     */
    protected function _getOutput()
    {
        switch ($this->_returnType) {
            case self::RETURN_HTML:
                return $this->_getOutputAsHtml();
            case self::RETURN_ARRAY:
                return $this->_getOutputAsArray();
            default:
    //             throw new Omeka_View_Exception('Invalid return type!');
                throw new InvalidArgumentException('Invalid return type!');
        }
    }

    /**
     * Load a view partial to display the data.
     *
     * @param array $vars Variables to pass to the partial.
     * @return string
     */
    protected function _loadViewPartial($vars = array())
    {
        return $this->view->partial($this->_partial, $vars);
    }
}
