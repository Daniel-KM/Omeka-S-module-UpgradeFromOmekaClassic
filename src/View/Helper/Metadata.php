<?php
namespace UpgradeFromOmekaClassic\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\View\Exception\InvalidArgumentException;
use Omeka\Api\Representation\AbstractResourceRepresentation;

/**
 * Omeka
 *
 * @note Upgraded from Omeka Classic via the plugin "Upgrade to Omeka S".
 * @link https://github.com/Daniel-KM/UpgradeToOmekaS
 * @link https://github.com/Daniel-KM/UpgradeFromOmekaClassic
 * @todo Is replaced by $resource->value() in most cases.
 *
 * @copyright Copyright 2007-2012 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * Helper used to retrieve record metadata for for display.
 *
 * @package Omeka\View\Helper
 */
// class Omeka_View_Helper_Metadata extends Zend_View_Helper_Abstract
class Metadata extends AbstractHelper
{
    const SNIPPET = 'snippet';
    const INDEX = 'index';
    const ALL = 'all';
    const NO_ESCAPE = 'no_escape';
    const NO_FILTER = 'no_filter';
    const DELIMITER = 'delimiter';
    const IGNORE_UNKNOWN = 'ignore_unknown';

    /**
     * Retrieve a specific piece of a record's metadata for display.
     *
     * @param Omeka_Record_AbstractRecord $record Database record representing
     * the item from which to retrieve field data.
     * @param string|array $metadata The metadata field to retrieve.
     *  If a string, refers to a property of the record itself.
     *  If an array, refers to an Element: the first entry is the set name,
     *  the second is the element name.
     * @param array|string|integer $options Options for formatting the metadata
     * for display.
     * - Array options:
     *   - 'all': If true, return an array containing all values for the field.
     *   - 'delimiter': Return the entire set of metadata as a string, where
     *     entries are separated by the given delimiter.
     *   - 'index': Return the metadata entry at the given zero-based index.
     *   - 'no_escape' => If true, do not escape the resulting values for HTML
     *     entities.
     *   - 'no_filter': If true, return the set of metadata without
     *     running any of the filters.
     *   - 'snippet': Trim the length of each piece of text to the given
     *     length in characters.
     * - Passing simply the string 'all' is equivalent to array('all' => true)
     * - Passing simply an integer is equivalent to array('index' => [the integer])
     * @return string|array|null Null if field does not exist for item. Array
     * if certain options are passed.  String otherwise.
     */
    // public function metadata($record, $metadata, $options = array())
    public function __invoke($record, $metadata, $options = array())
    {
        $upgrade = $this->getView()->upgrade();

        // Use $record->value() in most cases.
        if (empty($record) || empty($metadata)) {
            return;
        }

        if (is_string($record)) {
    //         $record = $this->view->getCurrentRecord($record);
            $record = $upgrade->get_current_record($record);
        }

    //     if (!($record instanceof Omeka_Record_AbstractRecord)) {
    //         throw new InvalidArgumentException('Invalid record passed to recordMetadata.');
        // metadata() is used for pages too. so don't use AbstractResourceEntityRepresentation.
        if (!($record instanceof AbstractResourceRepresentation)) {
            throw new InvalidArgumentException('Invalid resource passed to value().');
        }

        // Convert the shortcuts for the options into a proper array.
        $options = $this->_getOptions($options);

        $snippet = isset($options[self::SNIPPET]) ? (int) $options[self::SNIPPET] : false;
        $escape = empty($options[self::NO_ESCAPE]);
        $filter = empty($options[self::NO_FILTER]);
        $all = isset($options[self::ALL]) && $options[self::ALL];
        $delimiter = isset($options[self::DELIMITER]) ? (string) $options[self::DELIMITER] : false;
        $index = isset($options[self::INDEX]) ? (int) $options[self::INDEX] : 0;
        $ignoreUnknown = isset($options[self::IGNORE_UNKNOWN]) && $options[self::IGNORE_UNKNOWN];

        try {
            $text = $this->_getText($record, $metadata);
    //     } catch (Omeka_Record_Exception $e) {
        } catch (InvalidArgumentException $e) {
            if ($ignoreUnknown) {
                $text = null;
            } else {
                throw $e;
            }
        }

        if (is_array($text)) {
            // If $all or $delimiter isn't specified, pare the array down to
            // just one entry, otherwise we need to work on the whole thing
            if ($all || $delimiter) {
                foreach ($text as $key => $value) {
                    $text[$key] = $this->_process(
                        $record, $metadata, $value, $snippet, $escape, $filter);
                }

                // Return the joined text if there was a delimiter
                if ($delimiter) {
                    return join($delimiter, $text);
                } else {
                    return $text;
                }
            } else {
                // Return null if the index doesn't exist for the record.
                if (!isset($text[$index])) {
                    $text = null;
                } else {
                    $text = $text[$index];
                }
            }
        }

        // If we get here, we're working with a single value only.
        return $this->_process($record, $metadata, $text, $snippet, $escape, $filter);
    }

    /**
     * Options can sometimes be an integer or a string instead of an array,
     * which functions as a handy shortcut for theme writers.  This converts
     * the short form of the options into its proper array form.
     *
     * @param string|integer|array $options
     * @return array
     */
    protected function _getOptions($options)
    {
        $converted = array();
        if (is_integer($options)) {
            $converted = array(self::INDEX => $options);
        } else if (self::ALL == $options) {
            $converted = array(self::ALL => true);
        } else {
            $converted = (array) $options;
        }
        return $converted;
    }

    /**
     * Retrieve the text associated with a given element or field of the record.
     *
     * @param Omeka_Record_AbstractRecord $record
     * @param string|array $metadata
     * @return string|array Either an array of ElementText records or a string.
     */
    protected function _getText($record, $metadata)
    {
        // If $metadata is a string, we assume that it refers to a
        // special value, e.g. id, item type name, date added, etc.
        if (is_string($metadata)) {
            return $this->_getRecordMetadata($record, $metadata);
        }
        // If we get an array of length 2, retrieve the ElementTexts
        // that correspond to the given field.
        if (is_array($metadata) && count($metadata) == 2) {
            return $this->_getElementText($record, $metadata[0], $metadata[1]);
        }

        // If we didn't fit either of those categories, it's an invalid
        // argument.
    //     throw new Omeka_View_Exception('Unrecognized metadata specifier.');
        throw new InvalidArgumentException('Unrecognized metadata specifier.');
    }

    /**
     * Retrieve record metadata that is not stored as ElementTexts.
     *
     * @uses Omeka_Record_AbstractRecord::getProperty()
     * @param Omeka_Record_AbstractRecord $record
     * @param string $specialValue Field name.
     * @return mixed
     */
    protected function _getRecordMetadata($record, $specialValue)
    {
        // NOTE This method is similar in the file "mapping_regex.php" in the plugin "Upgrade to Omeka S".

        // Normalize to a valid record property.
        $property = str_replace(' ', '_', strtolower($specialValue));

    //     return $record->getProperty($property);
        switch ($property) {
            // Manage the case where a special value is a term.
            case strpos($property, ':'): return $record->value($property, ['all' => true]);

            // For all records.
            case 'id': return $record->id();
            case 'display_title': return $record->displayTitle();
            case 'title': return $record->title();
            case 'description': return $record->description();
            case 'added': return $record->created();
            case 'modified': return $record->modified();
            // Unmanaged in Omeka S.
            case 'featured': return isset($record->isFeatured) ? $record->isFeatured : null;
            case 'public': return $record->isPublic();
            case 'owner_id': return $record->owner();
            case 'permalink': return $record->url(null, true);

            // For items.
            case 'collection_id': return $record->itemSets() ? $record->itemSets()[0]->id() : null;
            case 'collection_name': return $record->itemSets() ? $record->itemSets()[0]->displayTitle() : null;
            case 'item_type_id': return $record->resourceClass() ? $record->resourceClass()->localName() : null;
            case 'item_type_name': return $record->resourceClass() ? $record->resourceClass()->label() : null;
            case 'has_files': return (boolean) count($record->media());
            // Unmanaged in Omeka S.
            case 'has_tags': return !empty($record->tags);
            case 'file_count': return count($record->media());
            case 'has_thumbnail': return $record->media() ? $record->media()[0]->hasThumbnails() : false;
            // Unmanaged in Omeka S.
            case 'citation': return $this->getView()->upgrade()->getCitation($record);

            // For collections.
            case 'total_items': return $record->itemCount();

            // For files.
            case 'uri': return $record->originalUrl();
            case 'fullsize_uri': return $record->thumbnailUrl('large');
            case 'thumbnail_uri': return $record->thumbnailUrl('medium');
            case 'square_thumbnail_uri': return $record->thumbnailUrl('square');
            case 'item_id': return $record->item()->id();
            // Managed in Omeka S via sql only.
            case 'order': return isset($record->position) ?  $record->position : null;
            // Unmanaged in Omeka S.
            case 'size': return $this->getView()->upgrade()->mediaFilesize($record);
            case 'has_derivative_image': return $record->hasThumbnails();
            case 'authentication': return $record->sha256();
            case 'mime_type': return $record->mediaType();
            // Unmanaged in Omeka S.
            case 'type_os': return $record->mediaData();
            case 'filename': return $record->storage_id() . ($record->extension() ? '.' . $record->extension() : '');
            case 'original_filename': return $record->source();
            case 'metadata': return $record->mediaData();

            // For simple pages.
            case 'text': return $this->getView()->vars()->pageViewModel->getVariable('content');

            // For exhibits.
            case 'slug': return $record->slug();
            case 'credits': return $record->owner()->name(); // Replace credits().
        }
    }

    /**
     * Retrieve the set of ElementText records that correspond to a given
     * element set and element.
     *
     * @param Omeka_Record_AbstractRecord $record
     * @param string $elementSetName
     * @param string $elementName
     * @return array Set of ElementText records.
     */
    protected function _getElementText($record, $elementSetName, $elementName)
    {
        $upgrade = $this->getView()->upgrade();

    //     $elementTexts = $record->getElementTexts($elementSetName, $elementName);
        $term = $upgrade->mapElementToTerm($elementSetName, $elementName);
        if (empty($term)) {
            return array();
        }
        $elementTexts = $record->value($term, ['all' => true]);
        // Lock the records so that they can't be accidentally saved back to the
        // database, since we are modifying their values directly at this point.
        // Also clone the record because otherwise it would be passed by
        // reference to all the display filters, which results in munged text.
    //     foreach ($elementTexts as $key => $textRecord) {
    //         $elementTexts[$key] = clone $textRecord;
    //         $textRecord->lock();
    //     }

        return $elementTexts;
    }

    /**
     * Process an individual piece of text.
     *
     * If given an ElementText record, the actual text string will be
     * extracted automatically.
     *
     * @param Omeka_Record_AbstractRecord $record
     * @param string|array $metadata
     * @param string|ElementText $text Text to process.
     * @param int|bool $snippet Snippet length, or false if no snippet.
     * @param bool $escape Whether to HTML escape the text.
     * @param bool $filter Whether to pass the output through plugin
     *  filters.
     * @return string
     */
    protected function _process($record, $metadata, $text, $snippet, $escape, $filter)
    {
        $upgrade = $this->getView()->upgrade();

    //     if ($text instanceof ElementText) {
        if ($text instanceof ValueRepresentation) {
            $elementText = $text;
    //         $isHtml = $elementText->isHtml();
    //         $text = $elementText->getText();
            $isHtml = false;
            $text = $elementText->type() == 'uri'
                ? $elementText->uri()
                : $elementText->value();
        } else {
            $elementText = false;
            $isHtml = false;
        }

        if (is_string($text)) {
            // Apply the snippet option before escaping text HTML. If applied after
            // escaping the HTML, this may result in invalid markup.
            if ($snippet) {
    //             $text = snippet($text, 0, $snippet);
                $text = $upgrade->snippet($text, 0, $snippet);
            }

            // Escape the non-HTML text if necessary.
            if ($escape && !$isHtml) {
    //             $text = html_escape($text);
                $text = $upgrade->html_escape($text);
            }
        }

        // Apply plugin filters.
        if ($filter) {
            $text = $this->_filterText($record, $metadata, $text, $elementText);
        }

        return $text;
    }

    /**
     * Apply filters to a text value.
     *
     * @param Omeka_Record_AbstractRecord $record
     * @param string|array $metadata
     * @param string $text
     * @param ElementText|bool $elementText
     * @return string
     */
    protected function _filterText($record, $metadata, $text, $elementText)
    {
        $upgrade = $this->getView()->upgrade();

        // Build the name of the filter to use. This will end up looking like:
        // array('Display', 'Item', 'Dublin Core', 'Title') or something similar.
        $filterName = array('Display', get_class($record));
        if (is_array($metadata)) {
            $filterName = array_merge($filterName, $metadata);
        } else {
            $filterName[] = $metadata;
        }
    //     return apply_filters($filterName, $text, array('record' => $record, 'element_text' => $elementText));
        return $upgrade->apply_filters($filterName, $text, array('record' => $record, 'element_text' => $elementText));
    }
}
