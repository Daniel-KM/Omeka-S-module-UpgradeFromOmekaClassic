<?php
namespace UpgradeFromOmekaClassic\View\Helper;

use Interop\Container\ContainerInterface;
use Omeka\Api\Exception\NotFoundException;
use Omeka\Api\Representation\AbstractResourceRepresentation;
use Omeka\Api\Representation\ItemRepresentation;
use Omeka\Api\Representation\MediaRepresentation;
use Zend\View\Exception\InvalidArgumentException;
use Zend\View\Helper\AbstractHelper;

/**
 * Return all functions converted from Omeka Classic to Omeka Semantic.
 *
 * @internal This adaptation is made for public themes, not for plugins:
 * get_view() and all methods that need an instantiated view may need to be
 * adapted and some methods check "is_public". Anyway, it's recommended to use
 * native Omeka S or Zend features.
 *
 * @internal The doc is not updated.
 */
class Upgrade extends AbstractHelper
{

    /**
     * Mapping of plugins between Omeka C and Omeka S
     *
     * @internal Only names that changed are required. "true" means integrated.
     *
     * @var array
     */
    public $mapping_plugins = [
        // Integrated plugins.
        'DublinCoreExtended' => true,
        'ExhibitBuilder' => true,
        'ItemRelations' => true,
        'MoreUserRoles' => true,
        'SimplePages' => true,

        'ArchiveFolder' => false,
        'Ark' => false,
        'BeamMeUpToInternetArchive' => false,
        'BeamMeUpToSoundcloud' => false,
        'CleanUrl' => false,
        'Coins' => false,
        'CollectionTree' => false,
        'Commenting' => false,
        'Contribution' => 'Collecting',
        'CsvImport' => 'CSVImport',
        'CsvImportPlus' => false,
        'Dropbox' => false,
        'EmbedCodes' => 'Sharing',
        'Escher' => 'EasyInstall',
        'Geolocation' => 'Mapping',
        'GuestUser' => false,
        'HistoryLog' => false,
        'MultiCollections' => false,
        'NeatlineTime' => false,
        'OpenlayersZoom' => false,
        'Rating' => false,
        'Scripto' => false,
        'SimpleContact' => false,
        'SimpleVocab' => 'CustomVocab',
        'SimpleVocabPlus' => false,
        'SocialBookmarking' => 'Sharing',
        'Stats' => false,
        'Tagging' => false,
        'Taxonomy' => false,
        'UniversalViewer' => 'UniversalViewer',
        'ZoteroImport' => 'ZoteroImport',
    ];

    /**
     * Mapping of models between Omeka C and Omeka S.
     *
     * This mapping allows to get a record, the current record, the loop, and in
     * many other places. Some are never used: the list is for info too.
     *
     * @internal Loops can be replaced directly by the variables.
     *
     * @var array
     */
    public $mapping_models = [
        'element_set' => 'vocabulary',
        'element' => 'property',
        'item_type' => 'resource_class',
        'item_type_element' => 'property',
        'record' => 'resource',
        'item' => 'item',
        'collection' => 'item_set',
        'file' => 'media',
        'element_text' => 'value',

        'user' => 'user',
        'users_activation' => 'password_creation',
        'key' => 'api_key',
        // Options can be "site_setting" or theme setting too.
        'option' => 'setting',
        'plugin' => 'module',
        'process' => 'job',
        'schema_migration' => 'migration',
        'search_text' => '',
        'session' => 'session',
        'tag' => '',

        'simple_pages_page' => 'page',

        'exhibit' => 'site',
        'exhibit_page' => 'page',
        'exhibit_page_block' => 'site_page_block',
        'exhibit_block_attachment' => 'site_block_attachment',

        // 'collecting_form', 'collecting_input', 'collecting_prompt',
        'guest_user_detail' => 'collecting_user',
        'contribution_contributed_item' => 'collecting_item',
        'contribution_type' => '',
        'contribution_type_element' => '',

        'csv_import_imported_item' => 'csvimport_entity',
        'csv_import_import' => 'csvimport_import',

        // The zoom level is replaced by "mapping".
        'location' => 'mapping_marker',

        'neatline_time_timeline' => 'timeline',

        'simple_vocab_term' => 'custom_vocab',
    ];

    /**
     * Mapping of urls between Omeka C and Omeka S .
     *
     * @var array
     */
    public $mapping_routes = [
        '~^items\b~' => 'item',
        '~^items/browse~' => 'item',
        '~^items/show/(\d+)~' => 'item/\1',
        '~^collections\b~' => 'item-set',
        '~^collections/browse~' => 'item-set',
        '~^collections/show/(\d+)~' => 'item-set/\1',
        '~^items/search\b~' => 'item/search',
        '~^search\b~' => 'item/search',
    ];

    /**
     * Mapping of derivatives between Omeka C and Omeka S.
     *
     * @var array
     */
    public $mapping_derivatives = [
        // Omeka C type to Omeka S type.
        'original' => 'original',
        'fullsize' => 'large',
        'thumbnail' => 'medium',
        'square_thumbnail' => 'square',

        // For simplicity (in Omeka C, the name may be different from the path).
        'originals' => 'original',
        'fullsizes' => 'large',
        'thumbnails' => 'medium',
        'square_thumbnails' => 'square',
    ];

    /**
     * Mapping keys of queries between Omeka C and Omeka S.
     *
     * @var array
     */
    public $mapping_query_keys = [
        'collection' => 'item_set_id',
        'collection_id' => 'item_set_id',
        'type' => 'resource_class_id',
        'item_type' => 'resource_class_id',
        'item_type_id' => 'resource_class_id',
        // May be used if needed.
        'collection[]' => 'item_set_id[]',
        'collection_id[]' => 'item_set_id[]',
        'type[]' => 'resource_class_id[]',
        'item_type[]' => 'resource_class_id[]',
        'item_type_id[]' => 'resource_class_id[]',
    ];

    /**
     * Mapping values of queries between Omeka C and Omeka S.
     *
     * @var array
     */
    public $mapping_query_values = [
        'added' => 'created',
    ];

    protected $services;

    public function __construct(ContainerInterface $services)
    {
        $this->services = $services;
    }

    /**
     * Returns this and allows access to all global functions of Omeka Classic.
     *
     * @return $this
     */
    public function __invoke()
    {
        return $this;
    }

    /**
     * Fallback for functions of plugins.
     *
     * @param string $name The name of the function not upgraded.
     * @param mix
     * @return void
     */
    public function fallback(...$params)
    {
    }

    /**
     * Map the name of a plugin to the name of a module.
     *
     * @param string $pluginName
     * @return boolean|string True if plugin is integrated, false if not
     * upgraded, else the matching module name, else the plugin name.
     */
    public function mapPluginToModule($pluginName)
    {
        return isset($this->mapping_plugins[$pluginName])
            ? $this->mapping_plugins[$pluginName]
            : $pluginName;
    }

    /**
     * Map the name of the matching name of a model from Omeka C to Omeka S.
     *
     * @internal Manage the US exception for "media" (plural in Omeka: medias).
     *
     * @param string|object $modelName The name of the model, in any format.
     * @param string $format "underscore" (default), "camelize", "classify", "html-id".
     * @param boolean $plural Return a plural name if true, else a singular.
     * @param boolean $asEntity Remove the generic part ("Adapter", "Controller",
     * "Form" and "Representation").
     * @return string The matching model name of Omeka S, usable in api and
     * queries.
     */
    public function mapModel($modelName, $format = 'underscore', $plural = true, $asEntity = true)
    {
        if (is_object($modelName)) {
            $modelName = get_class($name);
            $pos = strrpos($modelName, '\\');
            if ($pos !== false) {
                $modelName = substr($modelName, $pos + 1);
            }
        }

        $var = Inflector::underscore(
            \Doctrine\Common\Inflector\Inflector::singularize($modelName));

        if ($asEntity) {
            $var = preg_replace('~(?:Adapter|Controller|Form|Representation)$~', '', $var);
        }

        if (isset($this->mapping_models[$var])) {
            $var = $this->mapping_models[$var];
        }

        // Manage plural/singular, with an exception.
        // Manage an exception.
        if ($var == 'media' || $var == 'medium') {
            $var =  $plural ? 'media' : 'media';
        } elseif ($plural) {
            $var = \Doctrine\Common\Inflector\Inflector::pluralize($var);
        }

        switch ($format) {
            case 'camelize':
                return \Doctrine\Common\Inflector\Inflector::camelize($var);
            case 'classify':
                return \Doctrine\Common\Inflector\Inflector::classify($var);
            case 'html-id':
                return str_replace('_', '-', $var);
            case 'underscore':
            default:
                return $var;
        }
    }

    /**
     * Helper to map an Omeka C route to an Omeka S troute.
     *
     * @param string $route
     * @return string.
     */
    public function mapRoute($route)
    {
        return preg_replace(
            array_keys($this->mapping_routes),
            array_values($this->mapping_routes),
            $route);
    }

    /**
     * Map the name of the matching format from Omeka C to Omeka S.
     *
     * @param string $format The name of the format.
     * @param boolean $plural Return a plural name if true, else a singular.
     * @return string The matching format in Omeka S.
     */
    public function mapDerivative($format = null, /* bool */ $plural = false)
    {
        static $useSquareThumbnail;

        if (empty($format)) {
            if (is_null($useSquareThumbnail)) {
                $useSquareThumbnail = $this->get_option('use_square_thumbnail') == 1;
            }
            $format =$useSquareThumbnail ? 'square' : 'medium';
        } else {
            $var = Inflector::underscore(
                \Doctrine\Common\Inflector\Inflector::singularize($format));
            if (isset($this->mapping_derivatives[$var])) {
                $var = $this->mapping_derivatives[$var];
            }
        }

        return $plural
            ? \Doctrine\Common\Inflector\Inflector::pluralize($var)
            : $var;
    }

    /**
     * Map an element set name to a vocabulary prefix.
     *
     *  @param string $elementSetName
     *  @return string
     */
    public function mapElementSetToVocabulary($elementSetName)
    {
        // NOTE The api doesn't manage the vocabulary label or the property
        // label. Because this is to be used in an upgraded theme, the check is
        // done against the prefix, the local name and the labels.
        static $vocabularies;

        // List of vocabularies by Omeka 2 label and Omeka S prefix.
        if (is_null($vocabularies)) {
            $vocabularies = [];
            $api = $this->getView()->api();
            $result = $api->search('vocabularies')->getContent();
            foreach ($result as $vocabulary) {
                $vocabularies[$vocabulary->label()] = $vocabulary->prefix();
                $vocabularies[$vocabulary->prefix()] = $vocabulary->prefix();
            }
        }

        return isset($vocabularies[$elementSetName])
            ? $vocabularies[$elementSetName]
            : null;
    }

    /**
     * Get the property term from a specific element set name and element name.
     *
     * @internal Only Dublin Core is really managed.
     *
     * @param string $elementSetName The element set name.
     * @param string $elementName The element name.
     * @return string|null
     */
    public function mapElementToTerm($elementSetName, $elementName)
    {
        static $properties;

        $prefix = $this->mapElementSetToVocabulary($elementSetName);
        if (empty($prefix)) {
            return;
        }

        // List of properties by label and prefix.
        if (is_null($properties) || !isset($properties[$prefix])) {
            $properties[$prefix] = [];
            $result = $this->getView()->api()->search('properties', [
                'vocabulary_prefix' => $prefix,
            ])->getContent();
            foreach ($result as $property) {
                $properties[$prefix][$property->localName()] = strtolower($property->label());
            }
        }

        if (isset($properties[$prefix][$elementName])) {
            return $prefix . ':' . $elementName;
        }

        $localName = array_search(strtolower($elementName), $properties[$prefix]);
        if ($localName !== false) {
            return $prefix . ':' . $localName;
        }
    }

    /**
     * Map main keys in queries.
     *
     * @param array $query
     * @return array
     */
    public function mapQuery($query)
    {
        $result = array();
        foreach ($query as $key => $value) {
            if (isset($this->mapping_query_keys[$key])) {
                $key = $this->mapping_query_keys[$key];
            }

            $result[$key] = $this->mapColumn($value);
        }

        return $result;
    }

    /**
     * Map columns for queries.
     *
     * @param string $column
     * @return string
     */
    public function mapColumn($column)
    {
        if (is_array($column)) {
            $result = [];
            foreach ($column as $key => $col) {
                $result[$key] = $this->mapColumn($col);
            }
            return $result;
        }

        if (is_object($column)) {
            $column = $column->id();
        }

        if (is_null($column) || $column === '') {
            return '';
        }

        if ($column === false) {
            return '0';
        }
        if ($column === true) {
            return '1';
        }

        if (isset($this->mapping_query_values[$column])) {
            return $this->mapping_query_values[$column];
        }

        $elementSetName = strtok($column, ',');
        $elementName = strtok(',');
        if (!empty($elementSetName) && !empty($elementName)) {
            $property = $this->mapElementToTerm($elementSetName, $elementName);
            if ($property) {
                return $property;
            }
        }

        return (string) $column;
    }

    /**
     * Check the config to use internal assets or not, in order to respect privacy.
     *
     * Default is true.
     *
     * @return boolean
     */
    function useInternalAssets()
    {
        //     $config = Zend_Registry::get('bootstrap')->getResource('Config');
        //     $useInternalAssets = isset($config->theme->useInternalAssets)
        //         ? (bool) $config->theme->useInternalAssets
        //         : true;
        //     return $useInternalAssets;
        $config = $this->services->get('Config');
        $useInternalAssets = isset($config['assets']['use_externals'])
            ? !$config['assets']['use_externals']
            : true;
        return $useInternalAssets;
    }

    /**
     * Check if a template exists in the current theme.
     *
     * @internal This may be useful to use these functions with standard themes.
     *
     * @param string $file Path of the template, relative to the view.
     * @return boolean
     */
    public function isInTheme($file)
    {
        $currentTheme = $this->services
            ->get('Omeka\Site\ThemeManager')
            ->getCurrentTheme();
        if (!$currentTheme) {
            return false;
        }

        $physical = OMEKA_PATH
            . '/themes/' . $currentTheme->getId()
            . '/view/' . $file;
        return file_exists($physical);
    }

    /**
     * Get the current site from the view.
     *
     * This is useful, because there is only one site in Omeka Classic and
     * "$site" is not available in some views or partials.
     *
     * @return \Omeka\Api\Representation\SiteRepresentation
     */
    public function currentSite()
    {
        $view = $this->getView();
        $slug = $view
            ->params()
            ->fromRoute('site-slug');
        if ($slug) {
            try {
                $site = $view
                    ->api()
                    ->read('sites', ['slug' => $slug]);
                return $site->getContent();
            } catch (NotFoundException $e) {
                return;
            }
        }

        // A second way to find the site via the route and the database.
        $site = $this->getView()
            ->getHelperPluginManager()
            ->get('Zend\View\Helper\ViewModel')
            ->getRoot()
            ->getVariable('site');
        if (!empty($site)) {
            return $site;
        }
    }

    /**
     * Check if the current page is the home page (first page in main menu).
     *
     * According to Omeka S, the home page is not the first link in the menu,
     * but the first page in the menu.
     *
     * @return boolean
     */
    public function isHomePage()
    {
        $site = $this->currentSite();
        if (empty($site)) {
            return false;
        }

        $view = $this->getView();

        // Check the alias of the root of Omeka S with rerouting.
        if ($this->is_current_url($view->basePath())) {
            return true;
        }

        // Check the first normal pages.
        $linkedPages = $site->linkedPages();
        if ($linkedPages) {
            $firstPage = current($linkedPages);
            $url = $view->url('site/page', [
                'site-slug' => $site->slug(),
                'page-slug' => $firstPage->slug(),
            ]);
            if ($this->is_current_url($url)) {
                return true;
            }
        }

        // Check the root of the site.
        $url = $view->url('site', [
            'site-slug' => $site->slug(),
        ]);
        if ($this->is_current_url($url)) {
            return true;
        }

        return false;
    }

    /**
     * Return the size of a media file.
     *
     * @param MediaRepresentation $media
     * @return integer|null|false
     */
    function mediaFilesize(MediaRepresentation $media)
    {
        if (!$media->hasOriginal()) {
            return;
        }
        $filepath = OMEKA_PATH . '/files/original/' . $media->filename();
        if (!file_exists($filepath)) {
            return false;
        }
        return filesize($filepath);
    }

    /**
     * Omeka
     *
     * Global functions.
     *
     * @copyright Copyright 2007-2012 Roy Rosenzweig Center for History and New Media
     * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
     * @package Omeka\Function
     */

    /**
     * Get an option from the options table.
     *
     * If the returned value represents an object or array, it must be unserialized
     * by the caller before use. For example:
     * <code>
     * $object = unserialize(get_option('plugin_object'));
     * </code>
     *
     * @package Omeka\Function\Db\Option
     * @param string $name The option name.
     * @return string The option value.
     */
    public function get_option($name)
    {
    //     $options = Zend_Registry::get('bootstrap')->getResource('Options');
    //     if (isset($options[$name])) {
    //         return $options[$name];
    //     }
        switch ($name) {
            case 'author':
            case 'site_title':
                $site = $this->currentSite();
                if (empty($site)) {
                    return;
                }
                if ($name == 'author') {
                    $owner = $site->owner();
                    return $owner ? $owner->name() : null;
                }
                return $site->title();

            // Not used in public themes.
            case 'path_to_convert':
                $config = $this->services->get('Config');
                if (!isset($config['cli']['phpcli_path'])) {
                    return;
                }
                return $config['cli']['phpcli_path'];

                // These options are no more managed: use a page instead.
            case 'description':
            case 'copyright':
                return null;

            // TODO Check other main settings / site / theme settings.

            // The default name used in some theme to display the advanced
            // search drop down in the main search form.
            case 'use_advanced_search':
                return $this->getView()->themeSetting($name);

            // These options are managed at site level in Omeka S.
            case 'search_resource_types':
            case 'show_vocabulary_headings':
            case 'show_empty_properties':
            case 'tag_delimiter':
            case 'use_square_thumbnail':
                    $name = 'upgrade_' . $name;
                // No break.
            // For compatibility if needed.
            case 'upgrade_search_resource_types':
            case 'upgrade_show_vocabulary_headings':
            case 'upgrade_show_empty_properties':
            case 'upgrade_tag_delimiter':
            // The name used in the params of the site.
            case 'upgrade_use_advanced_search':
            case 'upgrade_use_square_thumbnail':
                $site = $this->currentSite();
                if (empty($site)) {
                    return;
                }
                $siteSettings = $this->services
                    ->get('Omeka\SiteSettings');
                $siteSettings->setSite($site);
                return $siteSettings->get($name);

            case 'administrator_email':
            default:
                return $this->getView()->setting($name);
        }
    }

    /**
     * Set an option to the options table.
     *
     * Note that objects and arrays must be serialized before being saved.
     *
     * @package Omeka\Function\Db\Option
     * @param string $name The option name.
     * @param string $value The option value.
     */
    public function set_option($name, $value)
    {
        // NOTE Useless in public theme of Omeka S.
    //     $db = get_db();
    //     $sql = "REPLACE INTO {$db->Option} (name, value) VALUES (?, ?)";
    //     $db->query($sql, array($name, $value));

    //     // Update the options cache.
    //     $bootstrap = Zend_Registry::get('bootstrap');
    //     $options = $bootstrap->getResource('Options');
    //     $options[$name] = $value;
    //     $bootstrap->getContainer()->options = $options;
    }

    /**
     * Delete an option from the options table.
     *
     * @package Omeka\Function\Db\Option
     * @param string $name The option name.
     */
    public function delete_option($name)
    {
        // NOTE Useless in public theme of Omeka S.
    //     $db = get_db();
    //     $sql = "DELETE FROM {$db->Option} WHERE `name` = ?";
    //     $db->query($sql, array($name));

    //     // Update the options cache.
    //     $bootstrap = Zend_Registry::get('bootstrap');
    //     $options = $bootstrap->getResource('Options');
    //     if (isset($options[$name])) {
    //         unset($options[$name]);
    //     }
    //     $bootstrap->getContainer()->options = $options;
    }

    /**
     * Return one column of a multidimensional array as an array.
     *
     * @package Omeka\Function\Utility
     * @param string|integer $col The column to pluck.
     * @param array $array The array from which to pluck.
     * @return array The column as an array.
     */
    public function pluck($col, $array)
    {
        $res = array();
        foreach ($array as $k => $row) {
            $res[$k] = $row[$col];
        }
        return $res;
    }

    /**
     * Return the currently logged in User record.
     *
     * @package Omeka\Function\User
     * @return User|null Null if no user is logged in.
     */
    public function current_user()
    {
    //     return Zend_Registry::get('bootstrap')->getResource('CurrentUser');
        return $this->getView()->identity();
    }

    /**
     * Get the database object.
     *
     * @package Omeka\Function\Db
     * @throws RuntimeException
     * @return Omeka_Db
     */
    public function get_db()
    {
    //     $db = Zend_Registry::get('bootstrap')->getResource('Db');
    //     if (!$db) {
    //         throw new RuntimeException("Database not available!");
    //     }
    //     return $db;
        return $this->services->get('Omeka\Connection');
    }

    /**
     * Log a message with 'DEBUG' priority.
     *
     * @package Omeka\Function\Log
     * @uses _log()
     * @param string $msg
     */
    public function debug($msg)
    {
    //     _log($msg, Zend_Log::DEBUG);
        $this->_log($msg, \Zend\Log\Logger::DEBUG);
    }

    /**
     * Log a message.
     *
     * Enabled via config.ini: log.errors.
     *
     * @package Omeka\Function\Log
     * @param mixed $msg The log message.
     * @param integer $priority See Zend_Log for a list of available priorities.
     */
    public function _log($msg, $priority = \Zend\Log\Logger::INFO)
    {
    //     try {
    //         $bootstrap = Zend_Registry::get('bootstrap');
    //     } catch (Zend_Exception $e) {
    //         return;
    //     }
    //     if (!($log = $bootstrap->getResource('Logger'))) {
    //         return;
    //     }
    //     $log->log($msg, $priority);
        $this->getView()->logger->log($priority, $msg);
    }

    /**
     * Declare a plugin hook implementation within a plugin.
     *
     * @package Omeka\Function\Plugin
     * @uses Omeka_Plugin_Broker::addHook()
     * @param string $hook Name of hook being implemented.
     * @param mixed $callback Any valid PHP callback.
     */
    public function add_plugin_hook($hook, $callback)
    {
        // NOTE Useless in public theme of Omeka S. Replaced by events.
    //     get_plugin_broker()->addHook($hook, $callback);
    }

    /**
     * Declare the point of execution for a specific plugin hook.
     *
     * All plugin implementations of a given hook will be executed when this is
     * called. The first argument corresponds to the string name of the hook. The
     * second is an associative array containing arguments that will be passed to
     * the plugin hook implementations.
     *
     * <code>
     * // Calls the hook 'after_save_item' with the arguments '$item' and '$arg2'
     * fire_plugin_hook('after_save_item', array('item' => $item, 'foo' => $arg2));
     * </code>
     *
     * @package Omeka\Function\Plugin
     * @uses Omeka_Plugin_Broker::callHook()
     * @param string $name The hook name.
     * @param array $args Arguments to be passed to the hook implementations.
     */
    public function fire_plugin_hook($name, array $args = array())
    {
    //     if ($pluginBroker = get_plugin_broker()) {
    //         $pluginBroker->callHook($name, $args);
    //     }
        // Hooks are replaced by events. The managed hooks are called during the
        // upgrade process, then the output is saved in a file, that is returned
        // here. In fact, only public_head is really used, since the upgraded
        // plugins manage events themselves and their hooks are not fetched.
        echo $this->common($name, $args, 'omeka/hook');
    }

    /**
     * Get the output of fire_plugin_hook() as a string.
     *
     * @package Omeka\Function\Plugin
     * @uses fire_plugin_hook()
     * @param string $name The hook name.
     * @param array $args Arguments to be passed to the hook implementations.
     * @return string
     */
    public function get_plugin_hook_output($name, array $args = array())
    {
        // NOTE Useless in public theme of Omeka S. Replaced by events.
    //     ob_start();
    //     fire_plugin_hook($name, $args);
    //     $content = ob_get_contents();
    //     ob_end_clean();
    //     return $content;
    }

    /**
     * Get the output of a specific plugin's hook as a string.
     *
     * This is like get_plugin_hook_output() but only calls the hook within the
     * provided plugin.
     *
     * @package Omeka\Function\Plugin
     * @uses Omeka_Plugin_Broker::getHook()
     * @param string $pluginName Directory name of the plugin to execute the hook for.
     * @param string $hookName Name of the hook to fire.
     * @param mixed $args Any arguments to be passed to the hook implementation.
     * @return string|null
     */
    public function get_specific_plugin_hook_output($pluginName, $hookName, $args = array())
    {
        // NOTE Useless in public theme of Omeka S. Replaced by events.
    //     // Get the specific hook.
    //     $pluginBroker = get_plugin_broker();
    //     $hookNameSpecific = $pluginBroker->getHook($pluginName, $hookName);

    //     // Return null if the specific hook doesn't exist.
    //     if (!$hookNameSpecific) {
    //         return null;
    //     }

    //     // Buffer and return any output originating from the hook.
    //     ob_start();
    //     foreach ($hookNameSpecific as $cb) {
    //         call_user_func($cb, $args);
    //     }
    //     $content = ob_get_contents();
    //     ob_end_clean();

    //     return $content;
    }

    /**
     * Get the broker object for Omeka plugins.
     *
     * @package Omeka\Function\Plugin
     * @return Omeka_Plugin_Broker|null
     */
    public function get_plugin_broker()
    {
        // NOTE Useless in public theme of Omeka S.
        // Only used in Scripto.
    //     try {
    //         return Zend_Registry::get('pluginbroker');
    //     } catch (Zend_Exception $e) {
    //         return null;
    //     }
    }

    /**
     * Get specified descriptive info for a plugin from its ini file.
     *
     * @package Omeka\Function\Plugin
     * @param string $pluginDirName The directory name of the plugin.
     * @param string $iniKeyName The name of the key in the ini file.
     * @return string|null The value of the specified plugin key. If the key does
     * not exist, it returns null.
     */
    public function get_plugin_ini($pluginDirName, $iniKeyName)
    {
        // NOTE Useless in public theme of Omeka S.
    //     $pluginIniReader = Zend_Registry::get('plugin_ini_reader');
    //     if ($pluginIniReader->hasPluginIniFile($pluginDirName)) {
    //         return $pluginIniReader->getPluginIniValue($pluginDirName, $iniKeyName);
    //     }
    }

    /**
     * Add a callback for displaying files with a given mimetype and/or extension.
     *
     * @package Omeka\Function\Plugin
     * @uses Omeka_View_Helper_FileMarkup::addMimeTypes() See for info on usage.
     * @param array|string $fileIdentifiers Set of MIME types and/or file extensions
     * to which the provided callback will respond.
     * @param callback $callback Any valid callback.
     * @param array $options
     */
    public function add_file_display_callback($fileIdentifiers, $callback, array $options = array())
    {
    //     Omeka_View_Helper_FileMarkup::addMimeTypes($fileIdentifiers, $callback, $options);
        FileMarkup::addMimeTypes($fileIdentifiers, $callback, $options);
    }

    /**
     * Add a fallback image for files of the given mime type or type family.
     *
     * The fallback is used when there are no generated derivative images and one
     * is requested (for example, by a call to file_image()).
     *
     * @since 2.2
     * @package Omeka\Function\Plugin
     * @uses Omeka_View_Helper_FileMarkup::addFallbackImage()
     * @param string $mimeType The mime type this fallback is for, or the mime
     *  "prefix" it is for (video, audio, etc.)
     * @param string $image The name of the image to use, as would be passed to
     *  img()
     */
    public function add_file_fallback_image($mimeType, $image)
    {
    //     Omeka_View_Helper_FileMarkup::addFallbackImage($mimeType, $image);
        FileMarkup::addFallbackImage($mimeType, $image);
    }

    /**
     * Apply a set of plugin filters to a given value.
     *
     * @package Omeka\Function\Plugin
     * @uses Omeka_Plugin_Broker::applyFilters()
     * @param string|array $name The filter name.
     * @param mixed $value The value to filter.
     * @param array $args Additional arguments to pass to filter implementations.
     * @return mixed Result of applying filters to $value.
     */
    public function apply_filters($name, $value, array $args = array())
    {
    //     if ($pluginBroker = get_plugin_broker()) {
    //         return $pluginBroker->applyFilters($name, $value, $args);
    //     }
    //     // If the plugin broker is not enabled for this request (possibly for
    //     // testing), return the original value.
    //     return $value;
        // TODO apply_filters() (filters are replaced by events).
        return $value;
    }

    /**
     * Declare a filter implementation.
     *
     * @package Omeka\Function\Plugin
     * @uses Omeka_Plugin_Broker::addFilter()
     * @param string|array $name The filter name.
     * @param callback $callback The function to call.
     * @param integer $priority Defaults to 10.
     */
    public function add_filter($name, $callback, $priority = 10)
    {
        // NOTE Useless in public theme of Omeka S.
    //     if ($pluginBroker = get_plugin_broker()) {
    //         $pluginBroker->addFilter($name, $callback, $priority);
    //     }
    }

    /**
     * Clear all implementations for a filter (or all filters).
     *
     * @package Omeka\Function\Plugin
     * @uses Omeka_Plugin_Broker::clearFilters()
     * @param string|null $name The name of the filter to clear. If null or omitted,
     *  all filters will be cleared.
     */
    public function clear_filters($filterName = null)
    {
        // NOTE Useless in public theme of Omeka S.
    //     if ($pluginBroker = get_plugin_broker()) {
    //         $pluginBroker->clearFilters($filterName);
    //     }
    }

    /**
     * Get the ACL object.
     *
     * @package Omeka\Function\User
     * @return Zend_Acl
     */
    public function get_acl()
    {
    //     try {
    //         return Zend_Registry::get('bootstrap')->getResource('Acl');
    //     } catch (Zend_Exception $e) {
    //         return null;
    //     }
        return $this->services->get('Omeka\Acl');
    }

    /**
     * Determine whether the script is being executed through the admin interface.
     *
     * Can be used to branch behavior based on whether or not the admin theme is
     * being accessed, but should not be relied upon in place of using the ACL for
     * controlling access to scripts.
     *
     * @package Omeka\Function\View
     * @return boolean
     */
    public function is_admin_theme()
    {
        // NOTE Useless in public theme of Omeka S.
    //    return (bool) Zend_Controller_Front::getInstance()->getParam('admin');
        return false;
    }

    /**
     * Get all record types that may be indexed and searchable.
     *
     * Plugins may add record types via the "search_record_types" filter. The
     * keys should be the record's class name and the respective values should
     * be the human readable and internationalized version of the record type.
     *
     * These record classes must extend Omeka_Record_AbstractRecord and
     * implement this search mixin (Mixin_Search).
     *
     * @package Omeka\Function\Search
     * @see Mixin_Search
     * @return array
     */
    public function get_search_record_types()
    {
    //     $searchRecordTypes = array(
    //         'Item'       => __('Item'),
    //         'File'       => __('File'),
    //         'Collection' => __('Collection'),
    //     );
    //     $searchRecordTypes = apply_filters('search_record_types', $searchRecordTypes);
    //     return $searchRecordTypes;
        // TODO apply filter for get_search_record_types().
        $searchRecordTypes = array(
            'Item' => $this->stranslate('Items'),
            'Media' => $this->stranslate('Medias'),
            'ItemSet' => $this->stranslate('Collections'),
            'Page' => $this->stranslate('Pages'),
        );
        return $searchRecordTypes;
    }

    /**
     * Get all record types that have been customized to be searchable.
     *
     * @package Omeka\Function\Search
     * @uses get_search_record_types()
     * @return array
     */
    public function get_custom_search_record_types()
    {
        // Get the custom search record types from the database.
    //     $customSearchRecordTypes = unserialize(get_option('search_record_types'));
        $customSearchRecordTypes = $this->get_option('search_resource_types');
        if (!is_array($customSearchRecordTypes)) {
            $customSearchRecordTypes = array();
        }

        // Compare the custom list to the full list.
    //     $searchRecordTypes = get_search_record_types();
        $searchRecordTypes = $this->get_search_record_types();
        foreach ($searchRecordTypes as $key => $value) {
            // Remove record types that have been omitted.
            if (!in_array($key, $customSearchRecordTypes)) {
                unset($searchRecordTypes[$key]);
            }
        }

        return $searchRecordTypes;
    }

    /**
     * Get all available search query types.
     *
     * Plugins may add query types via the "search_query_types" filter. The keys
     * should be the type's GET query value and the respective values should be the
     * human readable and internationalized version of the query type.
     *
     * Plugins that add a query type must modify the select object via the
     * "search_sql" hook to account for whatever custom search strategy they
     * implement.
     *
     * @package Omeka\Function\Search
     * @see Table_SearchText::applySearchFilters()
     * @return array
     */
    public function get_search_query_types()
    {
    //     // Apply the filter only once.
    //     static $searchQueryTypes;
    //     if ($searchQueryTypes) {
    //         return $searchQueryTypes;
    //     }

    //     $searchQueryTypes = array(
    //         'keyword'     => __('Keyword'),
    //         'boolean'     => __('Boolean'),
    //         'exact_match' => __('Exact match'),
    //     );
    //     $searchQueryTypes = apply_filters('search_query_types', $searchQueryTypes);
    //     return $searchQueryTypes;
        // Apply the filter only once.
        static $searchQueryTypes;
        if ($searchQueryTypes) {
            return $searchQueryTypes;
        }

        $searchQueryTypes = array(
            'keyword'     => $this->stranslate('Keyword'),
            'boolean'     => $this->stranslate('Boolean'),
            'exact_match' => $this->stranslate('Exact match'),
        );
        $searchQueryTypes = $this->apply_filters('search_query_types', $searchQueryTypes);
        return $searchQueryTypes;
    }

    /**
     * Insert a new item into the Omeka database.
     *
     * @package Omeka\Function\Db\Item
     * @uses Builder_Item
     * @param array $metadata Set of metadata options for configuring the item.
     *  The array can include the following properties:
     *  - 'public' (boolean)
     *  - 'featured' (boolean)
     *  - 'collection_id' (integer)
     *  - 'item_type_id' (integer)
     *  - 'item_type_name' (string)
     *  - 'tags' (string, comma-delimited)
     *  - 'overwriteElementTexts' (boolean) -- determines whether or not to
     *    overwrite existing element texts.  If true, this will loop through the
     *    element texts provided in $elementTexts, and it will update existing records
     *    where possible.  All texts that are not yet in the DB will be added in the
     *    usual manner.  False by default.
     *
     * @param array $elementTexts Array of element texts to assign to the item.
     * This follows the following format::
     *    array(
     *      [element set name] => array(
     *        [element name] => array(
     *          array('text' => [string], 'html' => [false|true]),
     *          array('text' => [string], 'html' => [false|true])
     *         ),
     *        [element name] => array(
     *          array('text' => [string], 'html' => [false|true]),
     *          array('text' => [string], 'html' => [false|true])
     *        )
     *      ),
     *      [element set name] => array(
     *        [element name] => array(
     *          array('text' => [string], 'html' => [false|true]),
     *          array('text' => [string], 'html' => [false|true])
     *        ),
     *        [element name] => array(
     *          array('text' => [string], 'html' => [false|true]),
     *          array('text' => [string], 'html' => [false|true])
     *        )
     *      )
     *    );
     *
     * @param array $fileMetadata Settings and data used to ingest files into Omeka
     *  and add them to this item.  Includes the following options:
     *  - 'file_transfer_type' (string = 'Url|Filesystem|Upload' or
     *    Omeka_File_Transfer_Adapter_Interface). Corresponds to the $transferStrategy
     *    argument for addFiles().
     *  - 'file_ingest_options' (array) Optional array of options to
     *    modify the behavior of the ingest.  Corresponds to the $options
     *    argument for addFiles().
     *  - 'files' (array or string) Represents information indicating the file to
     *    ingest. Corresponds to the $files argument for addFiles().
     *
     * @return Item
     */
    public function insert_item($metadata = array(), $elementTexts = array(), $fileMetadata = array())
    {
        // NOTE Useless in public theme of Omeka S, even with Contribution.
    //     $builder = new Builder_Item(get_db());
    //     $builder->setRecordMetadata($metadata);
    //     $builder->setElementTexts($elementTexts);
    //     $builder->setFileMetadata($fileMetadata);
    //     return $builder->build();
    }

    /**
     * Add files to an item.
     *
     * @package Omeka\Function\Db\Item
     * @uses Builder_Item::addFiles()
     * @param Item|integer $item Item record or ID of item to add files to
     * @param string|Omeka_File_Ingest_AbstractIngest $transferStrategy Strategy
     *  to use when ingesting the file. The strings 'Url', 'Filesystem' and 'Upload'
     *  correspond to those built-in strategies. Alternatively a strategy object
     *  can be passed.
     * @param array $files Information about the file(s) to ingest. See addFiles()
     *  for details
     * @param array $options Array of options to
     *  modify the behavior of the ingest. Available options include:
     *  - 'ignore_invalid_files': boolean, false by default. Whether or not to
     *    throw exceptions when a file is not valid.
     *  - 'ignoreNoFile': (for Upload only) boolean, false by default. Whether to
     *    ignore validation errors that occur when an uploaded file is missing, like
     *    when a file input is left empty on a form.
     * @return array The added File records.
     */
    public function insert_files_for_item($item, $transferStrategy, $files, $options = array())
    {
        // NOTE Useless in public theme of Omeka S, even with Contribution.
    //     $builder = new Builder_Item(get_db());
    //     $builder->setRecord($item);
    //     return $builder->addFiles($transferStrategy, $files, $options);
    }

    /**
     * Update an existing item.
     *
     * @package Omeka\Function\Db\Item
     * @see insert_item()
     * @uses Builder_Item
     * @param Item|int $item Either an Item object or the ID for the item.
     * @param array $metadata Set of options that can be passed to the item.
     * @param array $elementTexts Element texts to assign. See insert_item() for details.
     * @param array $fileMetadata File ingest data. See insert_item() for details.
     * @return Item
     */
    public function update_item($item, $metadata = array(), $elementTexts = array(), $fileMetadata = array())
    {
        // NOTE Useless in public theme of Omeka S.
    //     $builder = new Builder_Item(get_db());
    //     $builder->setRecord($item);
    //     $builder->setRecordMetadata($metadata);
    //     $builder->setElementTexts($elementTexts);
    //     $builder->setFileMetadata($fileMetadata);
    //     return $builder->build();
    }

    /**
     * Update an existing collection.
     *
     * @package Omeka\Function\Db\Collection
     * @see insert_collection()
     * @uses Builder_Collection
     * @param Collection|int $collection Either an Collection object or the ID for the collection.
     * @param array $metadata Set of options that can be passed to the collection.
     * @param array $elementTexts The element texts for the collection.
     * @return Collection
     */
    public function update_collection($collection, $metadata = array(), $elementTexts = array())
    {
        // NOTE Useless in public theme of Omeka S.
    //     $builder = new Builder_Collection(get_db());
    //     $builder->setRecord($collection);
    //     $builder->setRecordMetadata($metadata);
    //     $builder->setElementTexts($elementTexts);
    //     return $builder->build();
    }

    /**
     * Insert a new item type.
     *
     * @package Omeka\Function\Db\ItemType
     * @uses Builder_ItemType
     * @param array $metadata Follows the format:
     * <code>
     * array(
     *   'name'        => [string],
     *   'description' => [string]
     * );
     * </code>
     * @param array $elementInfos An array containing element data. Each entry
     * follows one or more of the following formats:
     * <ol>
     *   <li>An array containing element metadata</li>
     *   <li>An Element object</li>
     * </ol>
     * <code>
     * array(
     *   array(
     *     'name' => [(string) name, required],
     *     'description' => [(string) description, optional],
     *     'order' => [(int) order, optional],
     *   ),
     *   [(Element)],
     * );
     * </code>
     * @return ItemType
     */
    public function insert_item_type($metadata = array(), $elementInfos = array())
    {
        // NOTE Useless in public theme of Omeka S.
    //     $builder = new Builder_ItemType(get_db());
    //     $builder->setRecordMetadata($metadata);
    //     $builder->setElements($elementInfos);
    //     return $builder->build();
    }

    /**
     * Insert a collection
     *
     * @package Omeka\Function\Db\Collection
     * @uses Builder_Collection
     * @param array $metadata Follows the format:
     * <code>
     * array(
     *   'public'      => [true|false],
     *   'featured'    => [true|false]
     * )
     * </code>
     * @param array $elementTexts Array of element texts to assign to the collection.
     * This follows the format:
     * <code>
     * array(
     *   [element set name] => array(
     *     [element name] => array(
     *       array('text' => [string], 'html' => [false|true]),
     *       array('text' => [string], 'html' => [false|true])
     *      ),
     *     [element name] => array(
     *       array('text' => [string], 'html' => [false|true]),
     *       array('text' => [string], 'html' => [false|true])
     *     )
     *   ),
     *   [element set name] => array(
     *     [element name] => array(
     *       array('text' => [string], 'html' => [false|true]),
     *       array('text' => [string], 'html' => [false|true])
     *     ),
     *     [element name] => array(
     *       array('text' => [string], 'html' => [false|true]),
     *       array('text' => [string], 'html' => [false|true])
     *     )
     *   )
     * );
     * </code>
     * @return Collection
     */
    public function insert_collection($metadata = array(), $elementTexts = array())
    {
        // NOTE Useless in public theme of Omeka S.
    //     $builder = new Builder_Collection(get_db());
    //     $builder->setRecordMetadata($metadata);
    //     $builder->setElementTexts($elementTexts);
    //     return $builder->build();
    }

    /**
     * Insert an element set and its elements into the database.
     *
     * @package Omeka\Function\Db\ElementSet
     * @uses Builder_ElementSet
     * @param string|array $elementSetMetadata Element set information.
     * <code>
     * [(string) element set name]
     * // OR
     * array(
     *   'name'        => [(string) element set name, required, unique],
     *   'description' => [(string) element set description, optional],
     *   'record_type' => [(string) record type name, optional]
     * );
     * </code>
     * @param array $elements An array containing element data. Follows one of more
     * of the following formats:
     * <ol>
     * <li>An array containing element metadata</li>
     * <li>A string of the element name</li>
     * </ol>
     * <code>
     * array(
     *   array(
     *     'name' => [(string) name, required],
     *     'description' => [(string) description, optional],
     *   ),
     *   [(string) element name]
     * );
     * </code>
     * @return ElementSet
     */
    public function insert_element_set($elementSetMetadata = array(), array $elements = array())
    {
        // NOTE Useless in public theme of Omeka S.
    //     // Set the element set name if a string is provided.
    //     if (is_string($elementSetMetadata)) {
    //         $elementSetMetadata = array('name' => $elementSetMetadata);
    //     }

    //     $builder = new Builder_ElementSet(get_db());
    //     $builder->setRecordMetadata($elementSetMetadata);
    //     $builder->setElements($elements);
    //     return $builder->build();
    }

    /**
     * Release an object from memory.
     *
     * Use this fuction after you are done using an Omeka model object to prevent
     * memory leaks.  Required because PHP 5.2 does not do garbage collection on
     * circular references.
     *
     * @package Omeka\Function\Utility
     * @param mixed &$var The object to be released, or an array of such objects.
     */
    public function release_object(&$var)
    {
        // NOTE Useless, because Omeka S requires php 5.6.
    //     if (is_array($var)) {
    //         array_walk($var, 'release_object');
    //     } else if (is_object($var)) {
    //         $var->__destruct();
    //     }
    //     $var = null;
    }

    /**
     * Get a theme option.
     *
     * @package Omeka\Function\Db\Option
     * @uses Theme::getOption()
     * @param string $optionName The option name.
     * @param string $themeName The theme name.  If null, it will use the current
     * public theme.
     * @return string The option value.
     */
    public function get_theme_option($optionName, $themeName = null)
    {
    //     if (!$themeName) {
    //         $themeName = Theme::getCurrentThemeName('public');
    //     }
    //     return Theme::getOption($themeName, $optionName);
        $view = $this->getView();
        // get_theme_option() is managed only for current site: useless else.
        /*
         if (empty($themeName)) {
             $currentTheme = $this->services
                ->get('Omeka\Site\ThemeManager')
                ->getCurrentTheme();
             if ($currentTheme) {
                 $themeName = $currentTheme->getId();
             }
         }

         $themeName = trim($themeName);
         */

        // See Theme::getOption().
        $themeOptionName = Inflector::underscore($optionName);
        $themeOptionValue = $view->themeSetting($themeOptionName);
        return $themeOptionValue;
    }

    /**
     * Set a theme option.
     *
     * @package Omeka\Function\Db\Option
     * @uses Theme::setOption()
     * @param string $optionName The option name.
     * @param string $optionValue The option value.
     * @param string $themeName The theme name. If null, it will use the current
     * public theme.
     * @return
     */
    public function set_theme_option($optionName, $optionValue, $themeName = null)
    {
        // NOTE Useless in public theme of Omeka S.
    //     if (!$themeName) {
    //         $themeName = Theme::getCurrentThemeName('public');
    //     }
    //     Theme::setOption($themeName, $optionName, $optionValue);
    }

    /**
     * Get the names of all user roles.
     *
     * @package Omeka\Function\User
     * @uses Zend_Acl::getRoles()
     * @return array
     */
    public function get_user_roles()
    {
        // NOTE Useless in public theme of Omeka S.
    //     $roles = get_acl()->getRoles();
    //     foreach($roles as $key => $val) {
    //         $roles[$val] = __(Inflector::humanize($val));
    //         unset($roles[$key]);
    //     }
    //     return $roles;
    }

    /**
     * Check whether an element set contains a specific element.
     *
     * @package Omeka\Function\Db\ElementSet
     * @uses Table_Element::findByElementSetNameAndElementName()
     * @param string $elementSetName The element set name.
     * @param string $elementName The element name.
     * @return bool
     */
    public function element_exists($elementSetName, $elementName)
    {
    //     $element = get_db()->getTable('Element')->findByElementSetNameAndElementName($elementSetName, $elementName);
    //     return (bool) $element;
        return (boolean) $this->mapElementToTerm($elementSetName, $elementName);
    }

    /**
     * Determine whether a plugin is installed and active.
     *
     * May be used by theme/plugin writers to customize behavior based on the
     * existence of certain plugins. Some examples of how to use this function:
     *
     * Check if ExhibitBuilder is installed and activated.
     * <code>
     *     if (plugin_is_active('ExhibitBuilder')):
     * </code>
     *
     * Check if installed version of ExhibitBuilder is at least version 1.0 or
     * higher.
     * <code>
     *     if (plugin_is_active('ExhibitBuilder', '1.0')):
     * </code>
     *
     * Check if installed version of ExhibitBuilder is anything less than 2.0.
     * <code>
     *     if (plugin_is_active('ExhibitBuilder', '2.0', '<')):
     * </code>
     *
     * @package Omeka\Function\Plugin
     * @uses Table_Plugin::findByDirectoryName()
     * @param string $name Directory name of the plugin.
     * @param string $version Version of the plugin to check.
     * @param string $compOperator Comparison operator to use when checking the
     *  installed version of ExhibitBuilder.
     * @return boolean
     */
    public function plugin_is_active($name, $version = null, $compOperator = '>=')
    {
    //     $plugin = get_db()->getTable('Plugin')->findByDirectoryName($name);
    //     if (!$plugin) {
    //         return false;
    //     }
    //     if (!$plugin->isActive()) {
    //         return false;
    //     }
    //     if ($version) {
    //         return version_compare($plugin->getDbVersion(), $version, $compOperator);
    //     } else {
    //         return true;
    //     }
        static $versionsOfactiveModules;

        $name = $this->mapPluginToModule($name);
        if (is_bool($name)) {
            return $name;
        }

        if (is_null($versionsOfactiveModules)) {
            $versionsOfactiveModules = [];
            $sql = "SELECT * FROM module ORDER BY id;";
            $conn = $this->get_db();
            $stmt = $conn->query($sql);
            $result = $stmt->fetchAll();
            if (!$result) {
                return false;
            }

            foreach ($result as $row) {
                if ($row['is_active']) {
                    $versionsOfactiveModules[$row['id']] = $row['version'];
                }
            }
        }

        if (!isset($versionsOfactiveModules[$name])) {
            return false;
        }

        if ($version) {
            return version_compare($versionsOfactiveModules[$name], $version, $compOperator);
        }

        return true;
    }

    /**
     * Translate a string.
     *
     * @deprecated Use "$this->translate(new Message())" or the directive "`// @translate`" instead.
     * @package Omeka\Function\Locale
     * @uses Zend_Translate::translate()
     * @param string|array $msgid The string to be translated, or Array for plural
     *  translations.
     * @param mixed $args string formatting args. If any extra args are passed, the
     *  args and the translated string will be formatted with sprintf().
     * @return string The translated string.
     */
    public function __($msgid)
    {
    //     // Avoid getting the translate object more than once.
    //     static $translate;

    //     if (!isset($translate)) {
    //         try {
    //             $translate = Zend_Registry::get('Zend_Translate');
    //         } catch (Zend_Exception $e) {
    //             $translate = null;
    //         }
    //     }

    //     if ($translate) {
    //         $string = $translate->translate($msgid);
    //     } elseif (is_array($msgid)) {
    //         $string = ($msgid[2] === 1) ? $msgid[0] : $msgid[1];
    //     } else {
    //         $string = $msgid;
    //     }

    //     $args = func_get_args();
    //     array_shift($args);

    //     if (!empty($args)) {
    //         return vsprintf($string, $args);
    //     }

    //     return $string;
        if (is_array($msgid)) {
            $string = $this->getView()->translatePlural($msgid[0], $msgid[1], $msgid[2]);
        } else {
            $string = $this->getView()->translate($msgid);
        }

        $args = func_get_args();
        array_shift($args);
        if (!empty($args)) {
            return vsprintf($string, $args);
        }

        return $string;
    }

    /**
     * Translate a string, singular or plural (only one by one).
     *
     * If there are multiple plural, translate them separately.
     *
     * @internal Use "$this->translate(new Omeka\Stdlib\Message())" or the directive "`// @translate`" instead.
     * @internal Use preferably named placehoders (%2$s, %1$d...).
     *
     * @param string|array $msgid The string to be translated, or Array for plural
     *  translations.
     * @return string The translated string.
     */
    public function stranslate($msgid)
    {
        if (is_array($msgid)) {
            $string = $this->getView()->translatePlural($msgid[0], $msgid[1], $msgid[2]);
        } else {
            $string = $this->getView()->translate($msgid);
        }

        $args = func_get_args();
        array_shift($args);
        if (!empty($args)) {
            return vsprintf($string, $args);
        }

        return $string;
    }

    /**
     * Transform arguments in an array suitable for __.
     *
     * <code>
     *     $n = count($items);
     *     echo __(plural('one item', '%s items', $n), $n);
     * </code>
     *
     * @package Omeka\Function\Locale
     * @param string $msgid The string to be translated, singular form
     * @param string $msgid_plural The string to be translated, plural form
     * @param int $n Used to determine the plural form
     * @return array Array to pass to __
     */
    public function plural($msgid, $msgid_plural, $n)
    {
        return array($msgid, $msgid_plural, $n);
    }

    /**
     * Add an translation source directory.
     *
     * The directory's contents should be .mo files following the naming scheme of
     * Omeka's application/languages directory. If a .mo for the current locale
     * exists, the translations will be loaded.
     *
     * @package Omeka\Function\Locale
     * @uses Zend_Translate::addTranslation()
     * @param string $dir Directory from which to load translations.
     */
    public function add_translation_source($dir)
    {
        // NOTE Useless in public theme of Omeka S.
    //     try {
    //         $translate = Zend_Registry::get('Zend_Translate');
    //     } catch (Zend_Exception $e) {
    //         return;
    //     }

    //     $locale = $translate->getAdapter()->getOptions('localeName');

    //     try {
    //         $translate->addTranslation(array(
    //             'content' => "$dir/$locale.mo",
    //             'locale' => $locale
    //         ));
    //     } catch (Zend_Translate_Exception $e) {
    //         // Do nothing, allow the user to set a locale or dir without a
    //         // translation.
    //     }
    }

    /**
     * Get the HTML "lang" attribute for the current locale.
     *
     * @package Omeka\Function\Locale
     * @return string
     */
    public function get_html_lang()
    {
    //     try {
    //         $locale = Zend_Registry::get('Zend_Locale');
    //     } catch (Zend_Exception $e) {
    //         return 'en-US';
    //     }
    //     return str_replace('_', '-', $locale->toString());
        $config = $this->services->get('Config');
        if (!isset($config['translator']['locale'])) {
            return 'en-US';
        }
        $locale = $config['translator']['locale'];
        return str_replace('_', '-', $locale);
    }

    /**
     * Format a date for output according to the current locale.
     *
     * @package Omeka\Function\Locale
     * @uses Zend_Date
     * @param mixed $date Date to format. If an integer, the date is intepreted as a
     * Unix timestamp. If a string, the date is interpreted as an ISO 8601 date.
     * @param string $format Format to apply. See Zend_Date for possible formats.
     * The default format is the current locale's "medium" format.
     * @return string
     */
    public function format_date($date, $format = 'j M Y' /*Zend_Date::DATE_MEDIUM */)
    {
    //     if (is_int($date)) {
    //         $sourceFormat = Zend_Date::TIMESTAMP;
    //     } else {
    //         $sourceFormat = Zend_Date::ISO_8601;
    //     }
    //     $dateObj = new Zend_Date($date, $sourceFormat);
    //     return $dateObj->toString($format);
        // TODO Manage locale for format_date().
        if (is_int($date)) {
            $sourceFormat = 'U';
        } else {
            // Manage common cases.
            $date = trim($date);
            if (strpos($date, 'T') !== false) {
                if (strpos($date, '+') !== false) {
                    $sourceFormat = \DateTime::ISO8601;
                } else {
                    $sourceFormat = strpos($date, '/') !== false ? 'Y/m/d\TH:i:s' : 'Y-m-d\TH:i:s';
                }
            } elseif (strpos($date, ' ') !== false) {
                $sourceFormat = strpos($date, '/') !== false ? 'Y/m/d H:i:s' : 'Y-m-d H:i:s';
            } else {
                $sourceFormat = strpos($date, '/') !== false ? 'Y/m/d' : 'Y-m-d';
            }
        }
        $datetime = \DateTime::createFromFormat($sourceFormat, $date);
        if ($datetime) {
            return $datetime->format($format);
        }
    }

    /**
     * Add a local JavaScript file or files to the current page.
     *
     * All scripts will be included in the page's head. This needs to be
     * called either before head(), or in a plugin_header hook.
     *
     * @package Omeka\Function\View\Asset
     * @see head_js()
     * @param string|array $file File to use, if an array is passed, each array
     * member will be treated like a file.
     * @param string $dir Directory to search for the file. Keeping the default is
     * recommended.
     * @param array $options An array of options.
     */
    public function queue_js_file($file, $dir = 'javascripts', $options = array())
    {
    //     if (is_array($file)) {
    //         foreach ($file as $singleFile) {
    //             queue_js_file($singleFile, $dir, $options);
    //         }
    //         return;
    //     }

    //     queue_js_url(src($file, $dir, 'js'), $options);
        if (is_array($file)) {
            foreach ($file as $singleFile) {
                $this->queue_js_file($singleFile, $dir, $options);
            }
            return;
        }
        $this->queue_js_url($this->src($file, $dir, 'js'), $options);
    }

    /**
     * Add a JavaScript file to the current page by URL.
     *
     * The script link will appear in the head element. This needs to be called
     * either before head() or in a plugin_header hook.
     *
     * @package Omeka\Function\View\Asset
     * @see head_js()
     * @param string $string URL to script.
     * @param array $options An array of options.
     */
    public function queue_js_url($url, $options = array())
    {
    //     get_view()->headScript()->appendFile($url, null, $options);
        $this->getView()->headScript()->appendFile($url, null, $options);
    }

    /**
     * Add a JavaScript string to the current page.
     *
     * The script will appear in the head element. This needs to be called either
     * before head() or in a plugin_header hook.
     *
     * @package Omeka\Function\View\Asset
     * @see head_js()
     * @param string $string JavaScript string to include.
     * @param array $options An array of options.
     */
    public function queue_js_string($string, $options = array())
    {
    //     get_view()->headScript()->appendScript($string, null, $options);
        $this->getView()->headScript()->appendScript($string, null, $options);
    }

    /**
     * Add a CSS file or files to the current page.
     *
     * All stylesheets will be included in the page's head. This needs to be
     * called either before head(), or in a plugin_header hook.
     *
     * @package Omeka\Function\View\Asset
     * @see head_css()
     * @param string|array $file File to use, if an array is passed, each array
     * member will be treated like a file.
     * @param string $media CSS media declaration, defaults to 'all'.
     * @param string|bool $conditional IE-style conditional comment, used generally
     * to include IE-specific styles. Defaults to false.
     * @param string $dir Directory to search for the file.  Keeping the default is
     * recommended.
     */
    public function queue_css_file($file, $media = 'all', $conditional = false, $dir = 'css')
    {
    //     if (is_array($file)) {
    //         foreach ($file as $singleFile) {
    //             queue_css_file($singleFile, $media, $conditional, $dir);
    //         }
    //         return;
    //     }
    //     queue_css_url(css_src($file, $dir), $media, $conditional);
        if (is_array($file)) {
            foreach ($file as $singleFile) {
                $this->queue_css_file($singleFile, $media, $conditional, $dir);
            }
            return;
        }
        $this->queue_css_url($this->css_src($file, $dir), $media, $conditional);
    }

    /**
     * Add a CSS file to the current page by URL.
     *
     * The stylesheet link will appear in the head element. This needs to be called
     * either before head() or in a plugin_header hook.
     *
     * @package Omeka\Function\View\Asset
     * @see head_css
     * @param string $string URL to stylesheet.
     * @param string $media CSS media declaration, defaults to 'all'.
     * @param string|bool $conditional IE-style conditional comment, used generally
     * to include IE-specific styles. Defaults to false.
     */
    public function queue_css_url($url, $media = 'all', $conditional = false)
    {
    //     get_view()->headLink()->appendStylesheet($url, $media, $conditional);
        $this->getView()->headLink()->appendStylesheet($url, $media, $conditional);
    }

    /**
     * Add a CSS string to the current page.
     *
     * The inline stylesheet will appear in the head element. This needs to be
     * called either before head() or in a plugin_header hook.
     *
     * @package Omeka\Function\View\Asset
     * @see head_css
     * @param string $string CSS string to include.
     * @param string $media CSS media declaration, defaults to 'all'.
     * @param string|bool $conditional IE-style conditional comment, used generally
     * to include IE-specific styles. Defaults to false.
     */
    public function queue_css_string($string, $media = 'all', $conditional = false)
    {
    //     $attrs = array();
    //     if ($media) {
    //         $attrs['media'] = $media;
    //     }
    //     if ($conditional) {
    //         $attrs['conditional'] = $conditional;
    //     }
    //     get_view()->headStyle()->appendStyle($string, $attrs);
        $attrs = array();
        if ($media) {
            $attrs['media'] = $media;
        }
        if ($conditional) {
            $attrs['conditional'] = $conditional;
        }
        $this->getView()->headStyle()->appendStyle($string, $attrs);
    }

    /**
     * Get the JavaScript tags that will be used on the page.
     *
     * This should generally be used with echo to print the scripts in the page
     * head.
     *
     * @package Omeka\Function\View\Asset
     * @see queue_js_file()
     * @param bool $includeDefaults Whether the default javascripts should be
     * included. Defaults to true.
     * @return string
     */
    public function head_js($includeDefaults = true)
    {
    //     $headScript = get_view()->headScript();

    //     if ($includeDefaults) {
    //         $dir = 'javascripts';
    //         $headScript->prependScript('jQuery.noConflict();')
    //                    ->prependScript('window.jQuery.ui || document.write(' . js_escape(js_tag('vendor/jquery-ui')) . ')')
    //                    ->prependFile('//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js')
    //                    ->prependScript('window.jQuery || document.write(' . js_escape(js_tag('vendor/jquery')) . ')')
    //                    ->prependFile('//ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js');
    //     }
    //     return $headScript;
        // According to the upgrade process, it works like a hook.
        // The default scripts are included too.
        $this->fire_plugin_hook('head_js', array('includeDefaults' => $includeDefaults));

        echo $this->getView()->headScript();
    }

    /**
     * Get the CSS link tags that will be used on the page.
     *
     * This should generally be used with echo to print the scripts in the page
     * head.
     *
     * @package Omeka\Function\View\Asset
     * @see queue_css_file()
     * @return string
     */
    public function head_css()
    {
    //     return get_view()->headLink() . get_view()->headStyle();
        // According to the upgrade process, it works like a hook.
        $this->fire_plugin_hook('head_css');

        $view = $this->getView();
        echo $view->headLink();
        echo $view->headStyle();
    }

    /**
     * Get the URL to a local css file.
     *
     * @package Omeka\Function\View\Asset
     * @uses src()
     * @param string $file Should not include the .css extension
     * @param string $dir Defaults to 'css'
     * @return string
     */
    public function css_src($file, $dir = 'css')
    {
    //     return src($file, $dir, 'css');
        return $this->src($file, $dir, 'css');
    }

    /**
     * Get the URL to a local image file.
     *
     * @package Omeka\Function\View\Asset
     * @uses src()
     * @param string $file Filename, including the extension.
     * @param string $dir Directory within the theme to look for image files.
     * Defaults to 'images'.
     * @return string
     */
    public function img($file, $dir = 'images')
    {
    //     return src($file, $dir);
        return $this->src($file, $dir);
    }

    /**
     * Get a tag for including a local JavaScript file.
     *
     * @package Omeka\Function\View\Asset
     * @uses src()
     * @param string $file The name of the file, without .js extension.
     * @param string $dir The directory in which to look for javascript files.
     * Recommended to leave the default value.
     * @return string
     */
    public function js_tag($file, $dir = 'javascripts')
    {
    //     $href = src($file, $dir, 'js');
    //     return '<script type="text/javascript" src="' . html_escape($href) . '" charset="utf-8"></script>';
        $href = $this->src($file, $dir, 'js');
        return '<script type="text/javascript" src="'
            . $this->html_escape($href) . '" charset="utf-8"></script>';
    }

    /**
     * Get a URL for a given local file.
     *
     * @package Omeka\Function\View\Asset
     * @uses web_path_to()
     * @param string $file The filename.
     * @param string|null $dir The file's directory.
     * @param string $ext The file's extension.
     * @return string
     */
    public function src($file, $dir = null, $ext = null)
    {
    //     if ($ext !== null) {
    //         $file .= '.' . $ext;
    //     }
    //     if ($dir !== null) {
    //         $file = $dir . '/' . $file;
    //     }
    //     return web_path_to($file);
        if ($ext !== null) {
            $file .= '.' . $ext;
        }
        if ($dir !== null) {
            $file = $dir . '/' . $file;
        }
        return $this->web_path_to($file);
    }

    /**
     * Get the filesystem path for a local asset.
     *
     * @package Omeka\Function\View\Asset
     * @throws InvalidArgumentException
     * @param string $file The filename.
     * @return string
     */
    public function physical_path_to($file)
    {
    //     $paths = get_view()->getAssetPaths();
    //     foreach ($paths as $path) {
    //         list($physical, $web) = $path;
    //         if (file_exists($physical . '/' . $file)) {
    //             return $physical . '/' . $file;
    //         }
    //     }
    //     throw new InvalidArgumentException(__("Could not find file %s!", $file));
        static $paths;

        $view = $this->getView();
        if (is_null($paths)) {
            $config = $this->services
                ->get('Config');
            $paths = empty($config['view_manager']['template_path_stack'])
                ? []
                : $config['view_manager']['template_path_stack'];

            // Check in theme before modules.
            $currentTheme = $this->services
                ->get('Omeka\Site\ThemeManager')
                ->getCurrentTheme();
            if ($currentTheme) {
                $physical = OMEKA_PATH . '/themes/' . $currentTheme->getId() . '/asset';
                array_unshift($paths, $physical);
            }

            // Replace by "asset', because this method is designed for assets.
            $paths = preg_replace(
                [
                    '~/view$~',
                    '~/view-shared$~',
                    '~/view-admin$~'
                ],
                '/asset',
                $paths
                );
        }

        // NOTE Useless in public theme of Omeka S.
        // Adapt the path if needed.
        $replace = [
            // '~^css/~' => 'css/',
            '~^images/~' => 'img/',
            '~^javascripts/~' => 'js/',
        ];
        $file = preg_replace(array_keys($replace), array_values($replace), $file);

        foreach ($paths as $physical) {
            if (file_exists($physical . '/' . $file)) {
                return $physical . '/' . $file;
            }
        }

        throw new InvalidArgumentException(
            $this->stranslate("Could not find file %s!", $file));
    }

    /**
     * Get the URL for a local asset.
     *
     * @package Omeka\Function\View\Asset
     * @throws InvalidArgumentException
     * @param string $file The filename.
     * @return string
     */
    public function web_path_to($file)
    {
    //     $view = get_view();
    //     $paths = $view->getAssetPaths();
    //     foreach ($paths as $path) {
    //         list($physical, $web) = $path;
    //         if (file_exists($physical . '/' . $file)) {
    //             return $web . '/' . $file;
    //         }
    //     }
    //     throw new InvalidArgumentException( __("Could not find file %s!",$file) );
        static $paths;

        $view = $this->getView();
        if (is_null($paths)) {
            $config = $this->services
                ->get('Config');
            $paths = empty($config['view_manager']['template_path_stack'])
                ? []
                : $config['view_manager']['template_path_stack'];

            // Check in theme before modules.
            $currentTheme = $this->services
                ->get('Omeka\Site\ThemeManager')
                ->getCurrentTheme();
            if ($currentTheme) {
                $physical = OMEKA_PATH . '/themes/' . $currentTheme->getId() . '/asset';
                array_unshift($paths, $physical);
            }

            // Replace by "asset', because this method is designed for assets.
            $paths = preg_replace(
                [
                    '~/view$~',
                    '~/view-shared$~',
                    '~/view-admin$~'
                ],
                '/asset',
                $paths
                );
        }

        // Adapt the path if needed.
        $replace = [
            // '~^css/~' => 'css/',
            '~^images/~' => 'img/',
            '~^javascripts/~' => 'js/',
        ];
        $file = preg_replace(array_keys($replace), array_values($replace), $file);

        foreach ($paths as $physical) {
            if (file_exists($physical . '/' . $file)) {
                if (strpos($physical, '/themes/') !== false) {
                    $module = null;
                }
                // This is in a module or in Omeka itself.
                else {
                    $module = basename(dirname($physical));
                    if ($module == 'application') {
                        $module = 'Omeka';
                    }
                }
                return $view->assetUrl($file, $module);
            }
        }

        throw new InvalidArgumentException(
            $this->stranslate("Could not find file %s!", $file));
    }

    /**
     * Get HTML for displaying a random featured collection.
     *
     * @package Omeka\Function\View
     * @return string
     */
    public function random_featured_collection()
    {
    //     $collection = get_random_featured_collection();
    //     if ($collection) {
    //         $html = get_view()->partial('collections/single.php', array('collection' => $collection));
    //         release_object($collection);
    //     } else {
    //         $html = '<p>' . __('No featured collections are available.') . '</p>';
    //     }
    //     return $html;
        $collection = $this->get_random_featured_collection();
        if ($collection) {
            $html = $this->getView()->partial(
                'omeka/site/item-set/single.phtml',
                [
                    'itemSet' => $collection,
                ]
            );
        } else {
            $html = '<p>' . $this->stranslate('No featured collections are available.') . '</p>';
        }
        return $html;
    }

    /**
     * Get the Collection object for the current item.
     *
     * @package Omeka\Function\Db\Item
     * @param Item|null $item Check for this specific item record (current item if null).
     * @return Collection
     */
    public function get_collection_for_item($item = null)
    {
    //     if (!$item) {
    //         $item = get_current_record('item');
    //     }
    //     return $item->Collection;
        if (!$item) {
            $item = $this->get_current_record('item');
        }
        $itemSets = $item->itemSets();
        return reset($itemSets);
    }

    /**
     * Get the most recently added collections.
     *
     * @package Omeka\Function\Db\Collection
     * @uses get_records()
     * @param integer $num The maximum number of recent collections to return
     * @return array
     */
    public function get_recent_collections($num = 10)
    {
    //     return get_records('Collection', array('sort_field' => 'added', 'sort_dir' => 'd'), $num);
        $query = [
            'sort_by' => 'created',
            'sort_order' => 'desc',
            'limit' => $num,
        ];
        $site = $this->currentSite();
        if ($site) {
            $query['site_id'] = $site->id();
        }

        $response= $this->getView()->api()->search('item_sets', $query);
        return $response->getContent();
    }

    /**
     * Get a random featured collection.
     *
     * @package Omeka\Function\Db\Collection
     * @uses Collection::findRandomFeatured()
     * @return Collection
     */
    public function get_random_featured_collection()
    {
    //     return get_db()->getTable('Collection')->findRandomFeatured();
        // NOTE Featured is not managed by Omeka S, so return random item set.
        // TODO Manage random_featured with site items pool. No issue with a single site.
        // $site = $this->currentSite();
        // if ($site) {
        // }
        $sql = "SELECT resource.id
        FROM resource
        WHERE resource.is_public = 1
        AND resource.resource_type = 'Omeka\\\\Entity\\\\ItemSet'
        ORDER BY RAND()
        LIMIT 1
        ;";

        $conn = $this->get_db();
        $stmt = $conn->query($sql);
        $result = $stmt->fetchColumn();
        if (!$result) {
            return;
        }

        return $this->getView()->api()->read('item_sets', $result)->getContent();
    }

    /**
     * Get the latest available version of Omeka.
     *
     * @package Omeka\Function\Utility
     * @return string|false The latest available version of Omeka, or false if the
     * request failed for some reason.
     */
    public function latest_omeka_version()
    {
        // NOTE Useless in public theme of Omeka S.
    //     $omekaApiUri = 'http://api.omeka.org/latest-version';
    //     $omekaApiVersion = '0.1';

    //     // Determine if we have already checked for the version lately.
    //     $check = unserialize(get_option('omeka_update')) or $check = array();
    //     // This a timestamp corresponding to the last time we checked for
    //     // a new version.  86400 is the number of seconds in a day, so check
    //     // once a day for a new version.
    //     if (array_key_exists('last_updated', $check)
    //         and ($check['last_updated'] + 86400) > time()) {
    //         // Return the value we got the last time we checked.
    //         return $check['latest_version'];
    //     }

    //     try {
    //         $client = new Zend_Http_Client($omekaApiUri);
    //         $client->setParameterGet('version', $omekaApiVersion);
    //         $client->setMethod('GET');
    //         $result = $client->request();
    //         if ($result->getStatus() == '200') {
    //             $latestVersion = $result->getBody();
    //             // Store the newer values
    //             $check['latest_version'] = $latestVersion;
    //             $check['last_updated'] = time();
    //             set_option('omeka_update', serialize($check));
    //            return $result->getBody();
    //         } else {
    //            debug("Attempt to GET $omekaApiUri with version=$omekaApiVersion "
    //                  . "returned with status=" . $result->getStatus() . " and "
    //                  . "response body=" . $result->getBody());
    //         }
    //     } catch (Zend_Http_Client_Exception $e) {
    //         debug('Error in retrieving latest Omeka version: ' . $e->getMessage());
    //     }
    //     return false;
    }

    /**
     * Get the maximum file upload size.
     *
     * @package Omeka\Function\Utility
     * @return string
     */
    public function max_file_size()
    {
    //     $helper = new Omeka_View_Helper_MaxFileSize;
    //     return $helper->maxFileSize();
        return $this->getView()->uploadLimit();
    }

    /**
     * Get HTML for a set of files.
     *
     * @package Omeka\Function\View\File
     * @uses Omeka_View_Helper_FileMarkup::fileMarkup()
     * @param File $files A file record or an array of File records to display.
     * @param array $props Properties to customize display for different file types.
     * @param array $wrapperAttributes Attributes HTML attributes for the div that
     * wraps each displayed file. If empty or null, this will not wrap the displayed
     * file in a div.
     * @return string HTML
     */
    public function file_markup($files, array $props = array(), $wrapperAttributes = array('class' => 'item-file'))
    {
        // Replaced by $media->render() and various renderers.
    //     if (!is_array($files)) {
    //         $files = array($files);
    //     }
    //     $helper = new Omeka_View_Helper_FileMarkup;
    //     $output = '';
    //     foreach ($files as $file) {
    //         $output .= $helper->fileMarkup($file, $props, $wrapperAttributes);
    //     }
    //     return $output;
        if (!is_array($files)) {
            $files = array($files);
        }
        $view = $this->getView();
        $helper = $this->getView()->plugin('fileMarkup');
        $output = '';
        foreach ($files as $file) {
            $output .= $helper->fileMarkup($file, $props, $wrapperAttributes);
        }
        return $output;
    }

    /**
     * Return HTML for a file's ID3 metadata.
     *
     * @package Omeka\Function\View\File
     * @uses Omeka_View_Helper_FileId3Metadata::fileId3Metadata()
     * @param array $options Options for varying the display. Currently ignored.
     * @param File|null $file File to get the metadata for. If omitted, the current
     *  file is used.
     * @return string|array
     */
    public function file_id3_metadata(array $options = array(), $file = null)
    {
        // NOTE Useless in public theme of Omeka S.
    //     if (!$file) {
    //         $file = get_current_record('file');
    //     }
    //     return get_view()->fileId3Metadata($file, $options);
    }

    /**
     * Get the most recent files.
     *
     * @package Omeka\Function\Db
     * @uses get_records()
     * @param integer $num The maximum number of recent files to return
     * @return array
     */
    public function get_recent_files($num = 10)
    {
    //     return get_records('File', array('sort_field' => 'added', 'sort_dir' => 'd'), $num);
        $query = [
            'sort_by' => 'created',
            'sort_order' => 'desc',
            'limit' => $num,
        ];
        $site = $this->currentSite();
        if ($site) {
            $query['site_id'] = $site->id();
        }

        $response= $this->getView()->api()->search('media', $query);
        return $response->getContent();
    }

    /**
     * Generate attributes for an HTML tag.
     *
     * @package Omeka\Function\View
     * @param array|string $attributes Attributes for the tag.  If this is a string,
     * it will assign both 'name' and 'id' attributes that value for the tag.
     * @return string
     */
    public function tag_attributes($attributes)
    {
        if (is_string($attributes)) {
            $toProcess['name'] = $attributes;
            $toProcess['id'] = $attributes;
        } else {
            //don't allow 'value' to be set specifically as an attribute (why = consistency)
            unset($attributes['value']);
            $toProcess = $attributes;
        }

        $attr = array();
        foreach ($toProcess as $key => $attribute) {
            // Reject weird attribute names (a little more restrictively than necessary)
            if (preg_match('/[^A-Za-z0-9_:.-]/', $key)) {
                continue;
            }
            if (is_string($attribute)) {
    //             $attr[$key] = $key . '="' . html_escape( $attribute ) . '"';
                $attr[$key] = $key . '="' . $this->html_escape($attribute) . '"';
            }  else if ($attribute === true) {
                $attr[$key] = $key;
            }
        }
        return join(' ',$attr);
    }

    /**
     * Get the site-wide search form.
     *
     * @package Omeka\Function\Search
     * @param array $options Valid options are as follows:
     * - show_advanced (bool): whether to show the advanced search; default is false.
     * - submit_value (string): the value of the submit button; default "Submit".
     * - form_attributes (array): an array containing form tag attributes.
     * @return string The search form markup.
     */
    public function search_form(array $options = array())
    {
    //     return get_view()->searchForm($options);
        // Only "show_advanced" is managed (and may be set in theme).
        return $this->getView()
            ->partial(
                'common/search-main.phtml',
                $options);
    }

    /**
     * Get a list of current site-wide search filters in use.
     *
     * @package Omeka\Function\Search
     * @uses Omeka_View_Helper_SearchFilters::searchFilters()
     * @param array $options Valid options are as follows:
     * - id (string): the value of the div wrapping the filters.
     * @return string
     */
    public function search_filters(array $options = array())
    {
    //     return get_view()->searchFilters($options);
        // NOTE the helper is different between Omeka C and S.
        // Nevertheless, it is used, because it uses a partial where diffs can
        // be managed if it's really needed.
        return $this->getView()->searchFilters();
    }

    /**
     * Get the HTML for a form input for a given Element.
     *
     * Assume that the given element has access to all of its values (for example,
     * all values of a Title element for a given Item).
     *
     * This will output as many form inputs as there are values for a given element.
     * In addition to that, it will give each set of inputs a label and a span with
     * class="tooltip" containing the description for the element. This span can
     * either be displayed, hidden with CSS or converted into a tooltip with
     * javascript.
     *
     * All sets of form inputs for elements will be wrapped in a div with
     * class="field".
     *
     * @package Omeka\Function\View\Form
     * @uses Omeka_View_Helper_ElementForm::elementForm()
     * @param Element|array $element
     * @param Omeka_Record_AbstractRecord $record
     * @param array $options
     * @return string HTML
     */
    public function element_form($element, $record, $options = array())
    {
        // NOTE Useless in public theme of Omeka S.
    //     $html = '';
    //     // If we have an array of Elements, loop through the form to display them.
    //     if (is_array($element)) {
    //         foreach ($element as $key => $e) {
    //             $html .= get_view()->elementForm($e, $record, $options);
    //         }
    //     } else {
    //         $html = get_view()->elementForm($element, $record, $options);
    //     }
    //     return $html;
    }

    /**
     * Return a element set form for a record.
     *
     * @package Omeka\Function\View\Form
     * @uses element_form()
     * @param Omeka_Record_AbstractRecord $record
     * @param string $elementSetName The name of the element set or 'Item Type
     * Metadata' for an item's item type data.
     * @return string
     */
    public function element_set_form($record, $elementSetName)
    {
        // NOTE Useless in public theme of Omeka S.
    //     $recordType = get_class($record);
    //     if ($recordType == 'Item' && $elementSetName == 'Item Type Metadata') {
    //         $elements = $record->getItemTypeElements();
    //     } else {
    //         $elements = get_db()->getTable('Element')->findBySet($elementSetName);
    //     }
    //     $filterName = array('ElementSetForm', $recordType, $elementSetName);
    //     $elements = apply_filters(
    //         $filterName,
    //         $elements,
    //         array('record_type' => $recordType, 'record' => $record, 'element_set_name' => $elementSetName)
    //     );
    //     $html = element_form($elements, $record);
    //     return $html;
    }

    /**
     * Add a "Select Below" or other label option to a set of select options.
     *
     * @package Omeka\Function\View\Form
     * @param array $options
     * @param string|null $labelOption
     * @return array
     */
    public function label_table_options($options, $labelOption = null)
    {
        if ($labelOption === null) {
    //         $labelOption = __('Select Below ');
            $labelOption = $this->stranslate('Select Below ');
        }
        return array('' => $labelOption) + $options;
    }

    /**
     * Get the options array for a given table.
     *
     * @package Omeka\Function\View\Form
     * @uses Omeka_Db_Table::findPairsForSelectForm()
     * @param string $tableClass
     * @param string $labelOption
     * @param array $searchParams search parameters on table.
     */
    public function get_table_options($tableClass, $labelOption = null, $searchParams = array())
    {
        // NOTE get_table_options() is used only in advanced search, but use itemSetSelect, etc.
    //     $options = get_db()->getTable($tableClass)->findPairsForSelectForm($searchParams);
    //     $options = apply_filters(Inflector::tableize($tableClass) . '_select_options', $options);
    //     return label_table_options($options, $labelOption);
    }

    /**
     * Get the view object.
     *
     * Should be used only to avoid function scope issues within other theme helper
     * functions.
     *
     * @package Omeka\Function\View
     * @return Omeka_View
     */
    public function get_view()
    {
    //     return Zend_Registry::get('view');
        // In theme, "$this" is generally enough.
        return $this->getView();
    }

    /**
     * Get link tags for the RSS and Atom feeds.
     *
     * @package Omeka\Function\View\Layout
     * @return string HTML
     */
    public function auto_discovery_link_tags()
    {
    //     $html = '<link rel="alternate" type="application/rss+xml" title="'. __('Omeka RSS Feed') . '" href="'. html_escape(items_output_url('rss2')) .'" />';
    //     $html .= '<link rel="alternate" type="application/atom+xml" title="'. __('Omeka Atom Feed') .'" href="'. html_escape(items_output_url('atom')) .'" />';
    //     return $html;
        // Currently not supported by Omeka S and no module.
        $html = '<link rel="alternate" type="application/rss+xml" title="'
            . $this->stranslate('Omeka RSS Feed')
            . '" href="'. $this->html_escape($this->items_output_url('rss2')) .'" />';
        $html .= '<link rel="alternate" type="application/atom+xml" title="'
            . $this->stranslate('Omeka Atom Feed')
            . '" href="'. $this->html_escape($this->items_output_url('atom')) .'" />';
        return $html;
    }

    /**
     * Get HTML from a view file in the common/ directory.
     *
     * Optionally, parameters can be passed to the view, and the view can be loaded
     * from a different directory.
     *
     * @package Omeka\Function\View\Layout
     * @uses Zend_View_Helper_Partial::partial()
     * @param string $file Filename
     * @param array $vars A keyed array of variables to be extracted into the script
     * @param string $dir Defaults to 'common'
     * @return string
     */
    public function common($file, $vars = array(), $dir = 'common')
    {
    //     return get_view()->partial($dir . '/' . $file . '.php', $vars);
        $filepath = $dir . '/' . $file . '.phtml';
        if (!$this->isInTheme($filepath)) {
            return;
        }
        return $this->getView()->partial($filepath, $vars);
    }

    /**
     * Get the view's header HTML.
     *
     * @package Omeka\Function\View\Layout
     * @uses common
     * @param array $vars Keyed array of variables
     * @param string $file Filename of header script (defaults to 'header')
     * @return string
     */
    public function head($vars = array(), $file = 'header')
    {
    //     return common($file, $vars);
        return $this->common($file, $vars, 'layout');
    }

    /**
     * Get the view's footer HTML.
     *
     * @package Omeka\Function\View\Layout
     * @uses common()
     * @param array $vars Keyed array of variables
     * @param string $file Filename of footer script (defaults to 'footer')
     * @return string
     */
    public function foot($vars = array(), $file = 'footer')
    {
    //     return common($file, $vars);
        return $this->common($file, $vars, 'layout');
    }

    /**
     * Return a flashed message from the controller.
     *
     * @package Omeka\Function\View
     * @uses Omeka_View_Helper_Flash::flash()
     * @return string
     */
    public function flash()
    {
    //     return get_view()->flash();
        return $this->getView()->messages();
    }

    /**
     * Get the value of a particular site setting for display.
     *
     * Content for any specific option can be filtered by using a filter named
     * 'display_option_(option)' where (option) is the name of the option, e.g.
     * 'display_option_site_title'.
     *
     * @package Omeka\Function\View
     * @uses get_option()
     * @param string $name The name of the option
     * @return string
     */
    public function option($name)
    {
    //     $name = apply_filters("display_option_$name", get_option($name));
    //     $name = html_escape($name);
    //     return $name;
        $value = $this->get_option($name);
        $value = $this->apply_filters("display_option_$name", $value);
        $value = $this->getView()->escapeHtml($value);
        return $value;
    }

    /**
     * Get a set of records from the database.
     *
     * @package Omeka\Function\Db
     * @uses Omeka_Db_Table::findBy
     * @param string $recordType Type of records to get.
     * @param array $params Array of search parameters for records.
     * @param integer $limit Maximum number of records to return.
     *
     * @return array An array of result records (of $recordType).
     */
    public function get_records($recordType, $params = array(), $limit = 10)
    {
    //     return get_db()->getTable($recordType)->findBy($params, $limit);
        $resourceType = $this->mapModel($recordType);

        $query = $this->mapQuery($params);
        if ($limit) {
            $query['limit'] = $limit;
        }
        $site = $this->currentSite();
        if ($site) {
            $query['site_id'] = $site->id();
        }

        $response= $this->getView()->api()->search($resourceType, $query);
        return $response->getContent();
    }

    /**
     * Get a single record from the database.
     *
     * @since 2.1
     * @package Omeka\Function\Db
     * @uses Omeka_Db_Table::findBy
     * @param string $recordType Type of records to get.
     * @param array $params Array of search parameters for records.
     * @return object An object of result records (of $recordType).
     */
    public function get_record($recordType, $params = array())
    {
    //     $record = get_records($recordType, $params, 1);
    //     return reset($record);
        $record = $this->get_records($recordType, $params, 1);
        return reset($record);
    }

    /**
     * Get the total number of a given type of record in the database.
     *
     * @package Omeka\Function\Db
     * @uses Omeka_Db_Table::count()
     * @param string $recordType Type of record to count.
     * @return integer Number of records of $recordType in the database.
     */
    public function total_records($recordType)
    {
    //     return get_db()->getTable($recordType)->count();
        $resourceType = $this->mapModel($recordType);
        return $this->getView()->api()->search($resourceType, [])->getTotalResults();
    }

    /**
     * Get an iterator for looping over an array of records.
     *
     * @package Omeka\Function\View\Loop
     * @uses Omeka_View_Helper_Loop::loop()
     * @param string $recordsVar The name of the variable the records are stored in.
     * @param array|null $records
     * @return Omeka_Record_Iterator
     */
    public function loop($recordsVar, $records = null)
    {
    //     return get_view()->loop($recordsVar, $records);
        if ($records) {
            return $records;
        }

        $recordsVar = $this->mapModel($recordsVar, 'camelize');

        $view = $this->getView();
        if (isset($view->vars()->$recordsVar) && is_array($view->vars()->$recordsVar)) {
            return $view->vars()->$recordsVar;
        }
        return array();
    }

    /**
     * Set records to the view for iteration with loop().
     *
     * @package Omeka\Function\View\Loop
     * @uses Omeka_View_Helper_SetLoopRecords::setLoopRecords()
     * @param string $recordsVar The name of the variable to store the records in.
     * @param array $records The records to store for later looping.
     */
    public function set_loop_records($recordsVar, array $records)
    {
    //     get_view()->setLoopRecords($recordsVar, $records);
        $recordsVar = $this->mapModel($recordsVar, 'camelize');

        $this->getView()->vars()->assign([$recordsVar => $records]);
    }

    /**
     * Get records from the view for iteration.
     *
     * Note that this function will return an empty array if it is set to the
     * records variable. Use has_loop_records() to check if records exist.
     *
     * @package Omeka\Function\View\Loop
     * @uses Omeka_View_Helper_GetLoopRecords::getLoopRecords()
     * @throws Omeka_View_Exception
     * @param string $recordsVar The name of the variable the records are stored in.
     * @param boolean $throwException Whether to throw an exception if the
     *  $recordsVar is unset. Default is to throw.
     * @return array|bool
     */
    public function get_loop_records($recordsVar, $throwException = true)
    {
    //     return get_view()->getLoopRecords($recordsVar, $throwException);
        $recordsVar = $this->mapModel($recordsVar, 'camelize');

        $view = $this->getView();
        if (!isset($view->vars()->$recordsVar) || !is_array($view->vars()->$recordsVar)) {
            if ($throwException) {
                throw new InvalidArgumentException($this->stranslate('A loop %s variable has not been set to this view.', $recordsVar));
            }
            return false;
        }

        return $view->vars()->$recordsVar;
    }

    /**
     * Check if records have been set to the view for iteration.
     *
     * Note that this function will return false if the records variable is set but
     * is an empty array, unlike get_loop_records(), which will return the empty
     * array.
     *
     * @package Omeka\Function\View\Loop
     * @uses Omeka_View_Helper_HasLoopRecords::hasLoopRecords()
     * @param string $recordsVar View variable to check.
     * @return bool
     */
    public function has_loop_records($recordsVar)
    {
    //     return get_view()->hasLoopRecords($recordsVar);
        $recordsVar = $this->mapModel($recordsVar, 'camelize');

        $view = $this->getView();
        return isset($view->vars()->$recordsVar)
            && is_array($view->vars()->$recordsVar);
    }

    /**
     * Set a record to the view as the current record.
     *
     * @package Omeka\Function\View\Loop
     * @uses Omeka_View_Helper_SetCurrentRecord::setCurrentRecord()
     * @param string $recordVar View variable to store the current record in.
     * @param Omeka_Record_AbstractRecord $record
     * @param bool $setPreviousRecord Whether to store the previous "current" record,
     *  if any. The default is to not store the previous record.
     */
    public function set_current_record($recordVar, /* Omeka_Record_AbstractRecord */ $record, $setPreviousRecord = false)
    {
    //     get_view()->setCurrentRecord($recordVar, $record, $setPreviousRecord);
        $recordVar = $this->mapModel($recordVar, 'camelize', false);

        $view = $this->getView();

        if ($setPreviousRecord) {
            $previousRecordVar = "previous_$recordVar";
            $view->vars()->assign([$previousRecordVar => $view->vars()->$recordVar]);
        }

        $view->vars()->assign([$recordVar => $record]);
    }

    /**
     * Get the current record from the view.
     *
     * @package Omeka\Function\View\Loop
     * @uses Omeka_View_Helper_GetCurrentRecord::getCurrentRecord()
     * @throws Omeka_View-Exception
     * @param string $recordVar View variable the current record is stored in.
     * @param bool $throwException Whether to throw an exception if no current
     *  record was set. The default is to throw.
     * @return Omeka_Record_AbstractRecord|false
     */
    public function get_current_record($recordVar, $throwException = true)
    {
    //     return get_view()->getCurrentRecord($recordVar, $throwException);
        $recordVar = $this->mapModel($recordVar, 'camelize', false);

        $view = $this->getView();
        if (!isset($view->vars()->$recordVar)) {
            // TODO Try to find the collection if item is set.
            if ($throwException) {
                throw new InvalidArgumentException($this->stranslate('A current %s variable has not been set to this view.', $recordVar));
            }
            return false;
        }

        return $view->vars()->$recordVar;
    }

    /**
     * Get a record by its ID.
     *
     * @package Omeka\Function\Db
     * @uses Omeka_Db_Table::find()
     * @param string $modelName Name of the Record model being looked up
     *  (e.g., 'Item')
     * @param int $recordId The ID of the specific record to find.
     * @return Omeka_Record_AbstractRecord|null The record, or null if it cannot
     *  be found.
     */
    public function get_record_by_id($modelName, $recordId)
    {
    //     return get_db()->getTable(Inflector::camelize($modelName))->find($recordId);
        $modelName = $this->mapModel($modelName);
        try {
            $record = $this->getView()->api()->read($modelName, $recordId);
        } catch (\Omeka\Api\Exception\NotFoundException $e) {
            return;
        }
        return empty($record) ? null : $record->getContent();
    }

    /**
     * Get all output formats available in the current action.
     *
     * @package Omeka\Function\View\OutputFormat
     * @return array A sorted list of contexts.
     */
    public function get_current_action_contexts()
    {
    //     $actionName = Zend_Controller_Front::getInstance()->getRequest()->getActionName();
    //     $contexts = Zend_Controller_Action_HelperBroker::getStaticHelper('contextSwitch')->getActionContexts($actionName);
    //     sort($contexts);
    //     return $contexts;
        // TODO get_current_action_contexts() (json-ld only currently).
        $contexts = ['json-ld'];
        sort($contexts);
        return $contexts;
    }

    /**
     * Get an HTML list of output formats for the current action.
     *
     * @package Omeka\Function\View\OutputFormat
     * @uses get_current_action_contexts()
     * @param bool $list If true or omitted, return an unordered list, if false,
     *  return a simple string list using the delimiter.
     * @param string $delimiter If the first argument is false, use this as the
     *  delimiter for the list.
     * @return string|bool HTML
     */
    public function output_format_list($list = true, $delimiter = ', ')
    {
    //     return get_view()->partial(
    //         'common/output-format-list.php',
    //         array('output_formats' => get_current_action_contexts(), 'query' => $_GET,
    //               'list' => $list, 'delimiter' => $delimiter)
    //     );
        $view = $this->getView();
        $query = $view->params()->fromQuery();
        $site = $this->currentSite();
        if ($site) {
            $query['site_id'] = $site->id();
        }
        return $view->partial(
            'common/output-format-list.phtml',
            array(
                'output_formats' => $this->get_current_action_contexts(),
                'query' => $query,
                'list' => $list,
                'delimiter' => $delimiter,
            ));
    }

    /**
     * Get the list of links for sorting displayed records.
     *
     * @package Omeka\Function\View
     * @param array $links The links to sort the headings. Should correspond to
     *  the metadata displayed.
     * @param array $wrapperTags The tags and attributes to use for the browse headings
     * - 'list_tag' The HTML tag to use for the containing list
     * - 'link_tag' The HTML tag to use for each list item (the browse headings)
     * - 'list_attr' Attributes to apply to the containing list tag
     * - 'link_attr' Attributes to apply to the list item tag
     *
     * @return string
     */
    public function browse_sort_links($links, $wrapperTags = array())
    {
    //     $sortParam = Omeka_Db_Table::SORT_PARAM;
    //     $sortDirParam = Omeka_Db_Table::SORT_DIR_PARAM;
    //     $req = Zend_Controller_Front::getInstance()->getRequest();
    //     $currentSort = trim($req->getParam($sortParam));
    //     $currentDir = trim($req->getParam($sortDirParam));
        $view = $this->getView();
        $sortParam = 'sort_by';
        $sortDirParam = 'sort_order';
        $params = $view->params()->fromQuery();
        $currentSort = isset($params[$sortParam]) ? $params[$sortParam] : null;
        $currentDir = isset($params[$sortDirParam]) ? $params[$sortDirParam] : null;
        unset($params['page']);

        $defaults = array(
            'link_tag' => 'li',
            'list_tag' => 'ul',
            'link_attr' => array(),
            'list_attr' => array( 'id' => 'sort-links-list' )
        );

        $sortlistWrappers = array_merge($defaults, $wrapperTags);

        $linkAttrArray = array();
        foreach ($sortlistWrappers['link_attr'] as $key => $attribute) {
    //         $linkAttrArray[$key] = $key . '="' . html_escape( $attribute ) . '"';
            $linkAttrArray[$key] = $key . '="' . $this->html_escape($attribute) . '"';
        }
        $linkAttr = join(' ', $linkAttrArray);

        $listAttrArray = array();
        foreach ($sortlistWrappers['list_attr'] as $key => $attribute) {
    //         $listAttrArray[$key] = $key . '="' . html_escape( $attribute ) . '"';
            $listAttrArray[$key] = $key . '="' . $this->html_escape($attribute) . '"';
        }
        $listAttr = join(' ', $listAttrArray);

        $sortlist = '';
        if(!empty($sortlistWrappers['list_tag'])) {
            $sortlist .= "<{$sortlistWrappers['list_tag']} $listAttr>";
        }

        foreach ($links as $label => $column) {
            if($column) {
                // Upgrade columns.
                $column = $this->mapColumn($column);
                if ($column === '') {
                    continue;
                }
                $urlParams = $params;
                $urlParams[$sortParam] = $column;
                $class = '';
                if ($currentSort && $currentSort == $column) {
                    if ($currentDir && $currentDir == 'desc') {
                        $class = 'class="sorting desc"';
                        $urlParams[$sortDirParam] = 'asc';
                    } else {
                        $class = 'class="sorting asc"';
                        $urlParams[$sortDirParam] = 'desc';
                    }
                }
    //             $url = html_escape(url(array(), null, $urlParams));
                $url = $this->html_escape($this->url(array(), null, $urlParams));
                if ($sortlistWrappers['link_tag'] !== '') {
                    $sortlist .= "<{$sortlistWrappers['link_tag']} $class $linkAttr><a href=\"$url\">$label</a></{$sortlistWrappers['link_tag']}>";
                } else {
                    $sortlist .= "<a href=\"$url\" $class $linkAttr>$label</a>";
                }
            } else {
                $sortlist .= "<{$sortlistWrappers['link_tag']}>$label</{$sortlistWrappers['link_tag']}>";
            }
        }
        if(!empty($sortlistWrappers['list_tag'])) {
            $sortlist .= "</{$sortlistWrappers['list_tag']}>";
        }
        return $sortlist;
    }

    /**
     * Get a <body> tag with attributes.
     *
     * Attributes can be filtered using the 'body_tag_attributes' filter.
     *
     * @package Omeka\Function\View
     * @uses tag_attributes()
     * @param array $attributes
     * @return string An HTML <body> tag with attributes and their values.
     */
    public function body_tag($attributes = array())
    {
    //     $attributes = apply_filters('body_tag_attributes', $attributes);
    //     if ($attributes = tag_attributes($attributes)) {
    //         return "<body ". $attributes . ">\n";
    //     }
    //     return "<body>\n";
        // NOTE May use htmlElement('body').
        $attributes = $this->apply_filters('body_tag_attributes', $attributes);

        // NOTE Add specific classes for resource type and action.
        $params = $this->getView()->params()->fromRoute();
        $bodyClasses = isset($attributes['class']) ? $attributes['class'] . ' ' : '';
        if (isset($params['__CONTROLLER__'])) {
            $controller = $params['__CONTROLLER__'];
            $controllers = [
                'item' => 'items',
                'items' => 'items',
                'item-set' => 'collections',
                'item-sets' => 'collections',
            ];
            $bodyClasses .= isset($controllers[$controller])
                ? $controllers[$controller]
                : $this->text_to_id($controller);
        }
        $bodyClasses .= ' ' . $this->text_to_id($params['action']);

        $attributes['class'] = $bodyClasses;
        if ($attributes = $this->tag_attributes($attributes)) {
            return '<body '. $attributes . '>' . PHP_EOL;
        }
        return '<body>' . PHP_EOL;
    }

    /**
     * Get a list of the current search item filters in use.
     *
     * @package Omeka\Function\Search
     * @uses Omeka_View_Helper_SearchFilters::searchFilters()
     * @params array $params Params to override the ones read from the request.
     * @return string
     */
    public function item_search_filters(array $params = null)
    {
    //     return get_view()->itemSearchFilters($params);
        // NOTE Params from the request are not overridable.
        return $this->getView()->searchFilters();
    }

    /**
     * Get metadata for a record.
     *
     * @package Omeka\Function\View
     * @uses Omeka_View_Helper_Metadata::metadata()
     * @param Omeka_Record_AbstractRecord|string $record The record to get metadata
     * for. If an Omeka_Record_AbstractRecord, that record is used. If a string,
     * that string is used to look up a record in the current view.
     * @param mixed $metadata The metadata to get. If an array is given, this is
     * Element metadata, identified by array('Element Set', 'Element'). If a string,
     * the metadata is a record-specific "property."
     * @param array $options Options for getting the metadata.
     * @return mixed
     */
    public function metadata($record, $metadata, $options = array())
    {
    //     return get_view()->metadata($record, $metadata, $options);
        // Use $record->value() in most cases.
        return $this->getView()->metadata($record, $metadata, $options);
    }

    /**
     * Get all element text metadata for a record.
     *
     * @package Omeka\Function\View
     * @uses Omeka_View_Helper_AllElementTexts::allElementTexts()
     * @param Omeka_Record_AbstractRecord|string $record The record to get the
     * element text metadata for.
     * @param array $options Options for getting the metadata.
     * @return string|array
     */
    public function all_element_texts($record, $options = array())
    {
    //     return get_view()->allElementTexts($record, $options);
        // Replaced by $resource->values() and ->displayValues() in most cases.
        // The option "partial" can be set to "common/record-metadata.phtml"
        // (default Omeka 2), or to "common/resource-values.phtml".
        return $this->getView()->allElementTexts($record, $options);
    }

    /**
     * Get HTML for all files assigned to an item.
     *
     * @package Omeka\Function\View\Item
     * @uses file_markup()
     * @param array $options
     * @param array $wrapperAttributes
     * @param Item|null $item Check for this specific item record (current item if null).
     * @return string HTML
     */
    public function files_for_item($options = array(), $wrapperAttributes = array('class' => 'item-file'), $item = null)
    {
    //     if (!$item) {
    //         $item = get_current_record('item');
    //     }
    //     return file_markup($item->Files, $options, $wrapperAttributes);
        if (!$item) {
            $item = $this->get_current_record('item');
        }
        return $this->file_markup($item->media(), $options, $wrapperAttributes);
    }

    /**
     * Get the next item in the database.
     *
     * @package Omeka\Function\View\Item
     * @uses Item::next()
     * @param Item|null $item Check for this specific item record (current item if null).
     * @return Item|null
     */
    public function get_next_item($item = null)
    {
    //     if (!$item) {
    //         $item = get_current_record('item');
    //     }
    //     return $item->next();
        if (!$item) {
            $item = $this->get_current_record('item');
        }
        if (empty($item)) {
            return;
        }

        // Api manages only queries with "=", so use the connection. This is not
        // an issue inside public themes as long as the rights are checked.
        $conn = $this->get_db();
        $qb = $conn->createQueryBuilder()
            ->select('resource.id')
            ->from('resource', 'resource')
            ->where('resource.is_public = 1')
            ->andWhere('resource.resource_type = :resourceType')
            ->setParameter(':resourceType', 'Omeka\Entity\Item')
            ->andWhere('resource.id > :resource_id')
            ->setParameter(':resource_id', $item->id())
            ->orderBy('resource.id', 'ASC')
            ->setMaxResults(1);

        // TODO Manage next with site items pool. No issue with a single site.
        // $site = $this->currentSite();
        // if ($site) {
        // }

        $stmt = $conn->executeQuery($qb, $qb->getParameters(), $qb->getParameterTypes());
        $result = $stmt->fetchColumn();
        if (!$result) {
            return;
        }
        return $this->getView()->api()->read('items', $result)->getContent();
    }

    /**
     * Get the previous item in the database.
     *
     * @package Omeka\Function\View\Item
     * @uses Item::previous()
     * @param Item|null $item Check for this specific item record (current item if null).
     * @return Item|null
     */
    public function get_previous_item($item = null)
    {
    //     if (!$item) {
    //         $item = get_current_record('item');
    //     }
    //     return $item->previous();
        if (!$item) {
            $item = $this->get_current_record('item');
        }
        if (empty($item)) {
            return;
        }

        // Api manages only queries with "=", so use the connection. This is not
        // an issue inside public themes as long as the rights are checked.
        $conn = $this->get_db();
        $qb = $conn->createQueryBuilder()
            ->select('resource.id')
            ->from('resource', 'resource')
            ->where('resource.is_public = 1')
            ->andWhere('resource.resource_type = :resourceType')
            ->setParameter(':resourceType', 'Omeka\Entity\Item')
            ->andWhere('resource.id < :resource_id')
            ->setParameter(':resource_id', $item->id())
            ->orderBy('resource.id', 'DESC')
            ->setMaxResults(1);

        // TODO Manage prev with site items pool. No issue with a single site.
        // $site = $this->currentSite();
        // if ($site) {
        // }

        $stmt = $conn->executeQuery($qb, $qb->getParameters(), $qb->getParameterTypes());
        $result = $stmt->fetchColumn();
        if (!$result) {
            return;
        }
        return $this->getView()->api()->read('items', $result)->getContent();
    }

    /**
     * Get an image tag for a record.
     *
     * @package Omeka\Function\View
     * @throws InvalidArgumentException If an invalid record is passed.
     * @uses Omeka_View_Helper_FileMarkup::image_tag()
     * @param Omeka_Record_AbstractRecord|string $record
     * @param string $imageType Image size: thumbnail, square thumbnail, fullsize
     * @param array $props HTML attributes for the img tag
     * @return string
     */
    public function record_image($record, $imageType = null, $props = array())
    {
    //     if (is_string($record)) {
    //         $record = get_current_record($record);
    //     }

    //     if (!($record instanceof Omeka_Record_AbstractRecord)) {
    //         throw new InvalidArgumentException('An Omeka record must be passed to record_image.');
    //     }
    //     $fileMarkup = new Omeka_View_Helper_FileMarkup;
    //     return $fileMarkup->image_tag($record, $props, $imageType);
        if (is_string($record)) {
            $record = $this->get_current_record($record);
        }

        if (!($record instanceof AbstractResourceRepresentation)) {
            throw new InvalidArgumentException($this->stranslate('An Omeka resource must be passed to record_image.'));
        }
        return $this->getView()->fileMarkup()->image_tag($record, $props, $imageType);
    }

    /**
     * Get a customized item image tag.
     *
     * @package Omeka\Function\View\Item
     * @uses Omeka_View_Helper_FileMarkup::image_tag()
     * @param string $imageType Image size: thumbnail, square thumbnail, fullsize
     * @param array $props HTML attributes for the img tag
     * @param integer $index Which file within the item to use, by order. Default
     *  is the first file.
     * @param Item|null Check for this specific item record (current item if null).
     */
    public function item_image($imageType = null, $props = array(), $index = 0, $item = null)
    {
    //     if (!$item) {
    //         $item = get_current_record('item');
    //     }
    //     $imageFile = $item->getFile($index);
    //     $fileMarkup = new Omeka_View_Helper_FileMarkup;
    //     return $fileMarkup->image_tag($imageFile, $props, $imageType);
        if (!$item) {
            $item = $this->get_current_record('item');
        }
        $media = $item->media();
        if (!isset($media[$index])) {
            return;
        }
        $imageFile = $media[$index];
        return $this->getView()->fileMarkup()->image_tag($imageFile, $props, $imageType);
    }

    /**
     * Get a customized file image tag.
     *
     * @since 2.2
     * @package Omeka\Function\View\File
     * @uses Omeka_View_Helper_FileMarkup::image_tag()
     * @param string $imageType Image size: thumbnail, square thumbnail, fullsize
     * @param array $props HTML attributes for the img tag.
     * @param File|null Check for this specific file record (current file if null).
     */
    public function file_image($imageType, $props = array(), $file = null)
    {
    //     if (!$file) {
    //         $file = get_current_record('file');
    //     }
    //     $fileMarkup = new Omeka_View_Helper_FileMarkup;
    //     return $fileMarkup->image_tag($file, $props, $imageType);
        if (!$file) {
            $file = $this->get_current_record('media');
        }
        return $this->getView()->fileMarkup()->image_tag($file, $props, $imageType);
    }

    /**
     * Get a gallery of file thumbnails for an item.
     *
     * @package Omeka\Function\View\Item
     * @param array $attrs HTML attributes for the components of the gallery, in
     *  sub-arrays for 'wrapper', 'linkWrapper', 'link', and 'image'. Set a wrapper
     *  to null to omit it.
     * @param string $imageType The type of derivative image to display.
     * @param boolean $filesShow Whether to link to the files/show. Defaults to
     *  false, links to the original file.
     * @param Item $item The Item to use, the current item if omitted.
     * @return string
     */
    public function item_image_gallery($attrs = array(), $imageType = 'square_thumbnail', $filesShow = false, $item = null)
    {
        if (!$item) {
    //         $item = get_current_record('item');
            $item = $this->get_current_record('item');
        }

    //     $files = $item->Files;
        $files = $item->media();
        if (!$files) {
            return '';
        }

        $defaultAttrs = array(
            'wrapper' => array('id' => 'item-images'),
            'linkWrapper' => array(),
            'link' => array(),
            'image' => array()
        );
        $attrs = array_merge($defaultAttrs, $attrs);

        $html = '';
        if ($attrs['wrapper'] !== null) {
    //         $html .= '<div ' . tag_attributes($attrs['wrapper']) . '>';
            $html .= '<div ' . $this->tag_attributes($attrs['wrapper']) . '>';
        }
        foreach ($files as $file) {
            if ($attrs['linkWrapper'] !== null) {
    //             $html .= '<div ' . tag_attributes($attrs['linkWrapper']) . '>';
                $html .= '<div ' . $this->tag_attributes($attrs['linkWrapper']) . '>';
            }

    //         $image = file_image($imageType, $attrs['image'], $file);
            $image = $this->file_image($imageType, $attrs['image'], $file);
            if ($filesShow) {
    //             $html .= link_to($file, 'show', $image, $attrs['link']);
                $html .= $this->link_to($file, 'show', $image, $attrs['link']);
            } else {
    //             $linkAttrs = $attrs['link'] + array('href' => $file->getWebPath('original'));
    //             $html .= '<a ' . tag_attributes($linkAttrs) . '>' . $image . '</a>';
                $linkAttrs = $attrs['link'] + array('href' => $file->originalUrl());
                $html .= '<a ' . $this->tag_attributes($linkAttrs) . '>' . $image . '</a>';
            }

            if ($attrs['linkWrapper'] !== null) {
                $html .= '</div>';
            }
        }
        if ($attrs['wrapper'] !== null) {
            $html .= '</div>';
        }
        return $html;
    }

    /**
     * Return the HTML for an item search form.
     *
     * @package Omeka\Function\View\Search
     * @uses Zend_View_Helper_Partial::partial()
     * @param array $props Custom HTML attributes for the form element
     * @param string $formActionUri URL the form should submit to. If omitted, the
     *  form submits to the default items/browse page.
     * @param string $buttonText Custom text for the form submit button. If omitted,
     *  the default text 'Search for items' is used.
     * @return string
     */
    public function items_search_form($props = array(), $formActionUri = null, $buttonText = null)
    {
    //     return get_view()->partial(
    //         'items/search-form.php',
    //         array('formAttributes' => $props, 'formActionUri' => $formActionUri, 'buttonText' => $buttonText)
    //     );
        $view = $this->getView();
        $query = $view->params()->fromQuery();
        echo $this->js_tag('advanced-search');
        return $view->partial(
            'common/advanced-search.phtml',
            [
                'query' => $query,
                'formAttributes' => $props,
                'formActionUri' => $formActionUri,
                'buttonText' => $buttonText
            ]
        );
    }

    /**
     * Get the most recently added items.
     *
     * @package Omeka\Function\View\Item
     * @uses Table_Item::findBy()
     * @param integer $num The maximum number of recent items to return
     * @return array
     */
    public function get_recent_items($num = 10)
    {
    //     return get_db()->getTable('Item')->findBy(array('sort_field' => 'added', 'sort_dir' => 'd'), $num);
        $query = [
            'sort_by' => 'created',
            'sort_order' => 'desc',
        ];
        return $this->get_records('items', $query, $num);
    }

    /**
     * Get random featured items.
     *
     * @package Omeka\Function\View\Item
     * @uses get_records()
     * @param integer $num The maximum number of recent items to return
     * @param boolean|null $hasImage
     * @return array|Item
     */
    public function get_random_featured_items($num = 5, $hasImage = null)
    {
    //     return get_records('Item', array('featured' => 1,
    //                                      'sort_field' => 'random',
    //                                      'hasImage' => $hasImage), $num);
        // NOTE Featured is not managed by Omeka S, so return random items.
        // Doctrine or API doesn't allow random queries.
        $num = (integer) $num;
        $sqlImage = $hasImage ? 'AND media.has_thumbnails = 1' : '';
        // TODO Manage random_featured with site items pool. No issue with a single site.
        // $site = $this->currentSite();
        // if ($site) {
        // }
        $sql = "SELECT resource.id
        FROM resource
        WHERE resource.is_public = 1
        AND resource.resource_type = 'Omeka\\\\Entity\\\\Item'
        AND resource.id IN (
            SELECT media.item_id
            FROM media
            INNER JOIN resource ON media.id = resource.id
            INNER JOIN item ON item.id = media.item_id
            WHERE resource.is_public = 1
            $sqlImage
            GROUP BY media.item_id
            ORDER BY media.position
        )
        ORDER BY RAND()
        LIMIT $num
        ;";

        $conn = $this->get_db();
        $stmt = $conn->query($sql);
        $result = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        if (!$result) {
            return;
        }

        // Api doesn't manage multiple ids in the query.
        $api = $this->getView()->api();
        $items = [];
        foreach ($result as $id) {
            $items[] = $api->read('items', $id)->getContent();
        }
        return $items;
    }

    /**
     * Get HTML for recent items.
     *
     * @since 2.2
     * @package Omeka\Function\View\Item
     * @uses get_random_featured_items()
     * @param int $count Maximum number of recent items to show.
     * @return string
     */
    public function recent_items($count = 10)
    {
    //     $items = get_recent_items($count);
    //     if ($items) {
    //         $html = '';
    //         foreach ($items as $item) {
    //             $html .= get_view()->partial('items/single.php', array('item' => $item));
    //             release_object($item);
    //         }
    //     } else {
    //         $html = '<p>' . __('No recent items available.') . '</p>';
    //     }
    //     return $html;
        $view = $this->getView();
        $items = $this->get_recent_items($count);
        if ($items) {
            $template = 'omeka/site/item/single.phtml';
            // Check if the template exists.
            if ($this->isInTheme($template)) {
                $html = '';
                foreach ($items as $item) {
                    $html .= $view->partial($template, ['item' => $item]);
                }
            }
            // Use the default view.
            else {
                $template = 'common/block-layout/browse-preview.phtml';
                $html = $view->partial($template, ['items' => $items]);
            }
        } else {
            $html = '<p>' . $this->stranslate('No recent items available.') . '</p>';
        }
        return $html;
    }

    /**
     * Get HTML for random featured items.
     *
     * @package Omeka\Function\View\Item
     * @uses get_random_featured_items()
     * @param int $count Maximum number of items to show.
     * @param boolean $withImage Whether or not the featured items must have
     * images associated. If null, as default, all featured items can appear,
     * whether or not they have files. If true, only items with files will appear,
     * and if false, only items without files will appear.
     * @return string
     */
    public function random_featured_items($count = 5, $hasImage = null)
    {
    //     $items = get_random_featured_items($count, $hasImage);
    //     if ($items) {
    //         $html = '';
    //         foreach ($items as $item) {
    //             $html .= get_view()->partial('items/single.php', array('item' => $item));
    //             release_object($item);
    //         }
    //     } else {
    //         $html = '<p>' . __('No featured items are available.') . '</p>';
    //     }
    //     return $html;
        $view = $this->getView();
        $items = $this->get_random_featured_items($count, $hasImage);
        if ($items) {
            $template = 'omeka/site/item/single.phtml';
            // Check if the template exists.
            if ($this->isInTheme($template)) {
                $html = '';
                foreach ($items as $item) {
                    $html .= $view->partial($template, ['item' => $item]);
                }
            }
            // Use the default view.
            else {
                $template = 'common/block-layout/browse-preview.phtml';
                $html = $view->partial($template, ['items' => $items]);
            }
        } else {
            $html = '<p>' . $this->stranslate('No featured items are available.') . '</p>';
        }
        return $html;
    }

    /**
     * Get the set of values for item type elements.
     *
     * @package Omeka\Function\View\ItemType
     * @uses Item::getItemTypeElements()
     * @param Item|null $item Check for this specific item record (current item if null).
     * @return array
     */
    public function item_type_elements($item = null)
    {
        // NOTE Used too rarely and anyway, item type elements are not managed.
    //     if (!$item) {
    //         $item = get_current_record('item');
    //     }
    //     $elements = $item->getItemTypeElements();
    //     foreach ($elements as $element) {
    //         $elementText[$element->name] = metadata($item, array(ElementSet::ITEM_TYPE_NAME, $element->name));
    //     }
    //     return $elementText;
    }

    /**
     * Get a link to a page within Omeka.
     *
     * The controller and action can be manually specified, or if a record is passed
     * this function will hand off to record_url to automatically get a link to
     * that record (either its default action or one explicitly chosen).
     *
     * @package Omeka\Function\View\Navigation
     * @uses record_url()
     * @uses url()
     * @param Omeka_Record_AbstractRecord|string $record The name of the controller
     * to use for the link.  If a record instance is passed, then it inflects the
     * name of the controller from the record class.
     * @param string $action The action to use for the link
     * @param string $text The text to put in the link.  Default is 'View'.
     * @param array $props Attributes for the <a> tag
     * @param array $queryParams the parameters in the uri query
     * @return string HTML
     */
    public function link_to($record, $action = null, $text = null, $props = array(), $queryParams = array())
    {
        $queryParams = $this->mapQuery($queryParams);

        // If we're linking directly to a record, use the URI for that record.
    //     if ($record instanceof Omeka_Record_AbstractRecord) {
    //         $url = record_url($record, $action, false, $queryParams);
        if ($record instanceof AbstractResourceRepresentation) {
            $url = $this->record_url($record, $action, false, $queryParams);
        // Otherwise $record is the name of the controller to link to.
        } else {
            $urlOptions = array();
            //Use Zend Framework's built-in 'default' route
    //         $route = 'default';
            $route = $action == 'show' ? 'site/resource-id' : 'site/resource';
    //         $urlOptions['controller'] = (string) $record;
            $urlOptions['controller'] = $this->mapModel($record, 'underscore', false);
            if($action) $urlOptions['action'] = (string) $action;
    //         $url = url($urlOptions, $route, $queryParams, true);
            $url = $this->url($urlOptions, $route, $queryParams, true);
        }
        if ($text === null) {
    //         $text = __('View');
            $text = $this->stranslate('View');
            }
    //     $attr = !empty($props) ? ' ' . tag_attributes($props) : '';
    //     return '<a href="'. html_escape($url) . '"' . $attr . '>' . $text . '</a>';
        $attr = !empty($props) ? ' ' . $this->tag_attributes($props) : '';
        return '<a href="'. $this->html_escape($url) . '"' . $attr . '>' . $text . '</a>';
    }

    /**
     * Get HTML for a link to the item search form.
     *
     * @package Omeka\Function\View\Navigation
     * @param string $text Text of the link. Default is 'Search Items'.
     * @param array $props HTML attributes for the link.
     * @param string $uri Action for the form.  Defaults to 'items/browse'.
     * @return string
     */
    public function link_to_item_search($text = null, $props = array(), $uri = null)
    {
    //     if (!$text) {
    //         $text = __('Search Items');
    //     }
    //     if (!$uri) {
    //         $uri = apply_filters('items_search_default_url', url('items/search'));
    //     }
    //     $props['href'] = $uri . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
    //     return '<a ' . tag_attributes($props) . '>' . $text . '</a>';
        $view = $this->getView();
        if (!$text) {
            $text = $this->stranslate('Advanced search');
        }
        if (!$uri) {
            $query = $view->params()->fromQuery();
            $uri = $view->url(
                'site/resource',
                ['controller' => 'item', 'action' => 'search'],
                ['query' => $query],
                true);
            $uri = $this->apply_filters('items_search_default_url', $uri);
        }
        if (empty($props)) {
            $props = ['class' => 'advanced-search'];
        } else {
            unset($props['href']);
        }
        $link = $view->hyperlink($text, $uri, $props);
        return $link;
    }

    /**
     * Get HTML for a link to the browse page for items.
     *
     * @package Omeka\Function\View\Navigation
     * @uses link_to()
     * @param string $text Text to display in the link.
     * @param array $browseParams Any parameters to use to build the browse page
     * URL, e.g. array('collection' => 1) would build items/browse?collection=1 as
     * the URL.
     * @param array $linkProperties HTML attributes for the link.
     * @return string HTML
     */
    public function link_to_items_browse($text, $browseParams = array(), $linkProperties = array())
    {
    //     return link_to('items', 'browse', $text, $linkProperties, $browseParams);
        return $this->link_to('items', 'browse', $text, $linkProperties, $browseParams);
    }

    /**
     * Get a link to the collection to which the item belongs.
     *
     * The default text displayed for this link will be the name of the collection,
     * but that can be changed by passing a string argument.
     *
     * @package Omeka\Function\View\Navigation
     * @uses link_to_collection()
     * @param string|null $text Text for the link.
     * @param array $props HTML attributes for the <a> tag.
     * @param string $action 'show' by default.
     * @return string
     */
    public function link_to_collection_for_item($text = null, $props = array(), $action = 'show')
    {
    //     if ($collection = get_collection_for_item()) {
    //         return link_to_collection($text, $props, $action, $collection);
    //     }
    //     return __('No Collection');
        if ($collection = $this->get_collection_for_item()) {
            return $this->link_to_collection($text, $props, $action, $collection);
        }
        return $this->stranslate('No Collection');
    }

    /**
     * Get a link to the collection items browse page.
     *
     * @package Omeka\Function\View\Navigation
     * @uses link_to()
     * @param string|null $text
     * @param array $props
     * @param string $action
     * @param Collection $collectionObj
     * @return string
     */
    public function link_to_items_in_collection($text = null, $props = array(),
        $action = 'browse', $collectionObj = null
    ) {
    //     if (!$collectionObj) {
    //         $collectionObj = get_current_record('collection');
    //     }
    //     $queryParams = array();
    //     $queryParams['collection'] = $collectionObj->id;
    //     if ($text === null) {
    //         $text = $collectionObj->totalItems();
    //     }
    //     return link_to('items', $action, $text, $props, $queryParams);
        if (!$collectionObj) {
            $collectionObj = $this->get_current_record('collection');
        }
        $queryParams = array();
        $queryParams['item_set_id[]'] = $collectionObj->id();
        if ($text === null) {
            $text = $collectionObj->itemCount();
        }
        return $this->link_to('items', $action, $text, $props, $queryParams);
    }

    /**
     * Get a link to item type items browse page.
     *
     * @package Omeka\Function\View\Navigation
     * @uses link_to()
     * @param string|null $text
     * @param array $props
     * @param string $action
     * @param Collection $collectionObj
     * @return string
     */
    public function link_to_items_with_item_type($text = null, $props = array(),
        $action = 'browse', $itemTypeObj = null
    ) {
    //     if (!$itemTypeObj) {
    //         $itemTypeObj = get_current_record('item_type');
    //     }
    //     $queryParams = array();
    //     $queryParams['type'] = $itemTypeObj->id;
    //     if ($text === null) {
    //         $text = $itemTypeObj->totalItems();
    //     }
    //     return link_to('items', $action, $text, $props, $queryParams);
        if (!$itemTypeObj) {
            $itemTypeObj = $this->get_current_record('item_type');
        }
        $queryParams = array();
        $queryParams['resource_class_id[]'] = $itemTypeObj->id();
        if ($text === null) {
            $text = $itemTypeObj->itemCount();
        }
        return $this->link_to('items', $action, $text, $props, $queryParams);
    }

    /**
     * Get a link to the file metadata page for a particular file.
     *
     * If no File object is specified, this will determine the file to use through
     * context. The text of the link defaults to the DC:Title of the file record,
     * then to the original filename, unless otherwise specified.
     *
     * @package Omeka\Function\View\Navigation
     * @uses link_to()
     * @param array $attributes
     * @param string $text
     * @param File|null $file
     * @return string
     */
    public function link_to_file_show($attributes = array(), $text = null, $file = null)
    {
    //     if (!$file) {
    //         $file = get_current_record('file');
    //     }
    //     if (!$text) {
    //         $text = metadata($file, 'display_title');
    //     }
    //     return link_to($file, 'show', $text, $attributes);
        if (!$file) {
            $file = $this->get_current_record('media');
        }
        if (!$text) {
            $text = $file->displayTitle();
        }
        return $this->link_to($file, 'show', $text, $attributes);
    }

    /**
     * Get a link to an item.
     *
     * The only differences from link_to are that this function will automatically
     * use the "current" item, and will use the item's title as the link text.
     *
     * @package Omeka\Function\View\Navigation
     * @uses link_to()
     * @param string $text HTML for the text of the link.
     * @param array $props Properties for the <a> tag.
     * @param string $action The page to link to (this will be the 'show' page almost always
     * within the public theme).
     * @param Item $item Used for dependency injection testing or to use this function
     * outside the context of a loop.
     * @return string HTML
     */
    public function link_to_item($text = null, $props = array(), $action = 'show', $item = null)
    {
    //     if (!$item) {
    //         $item = get_current_record('item');
    //     }
    //     if (empty($text)) {
    //         $text = metadata($item, 'display_title');
    //     }
    //     return link_to($item, $action, $text, $props);
        if (!$item) {
            $item = $this->get_current_record('item');
        }
        if (empty($text)) {
            $text = $item->displayTitle();
        }
        return $this->link_to($item, $action, $text, $props);
    }

    /**
     * Get a link the the items RSS feed.
     *
     * @package Omeka\Function\View\Navigation
     * @uses items_output_url()
     * @param string $text The text of the link.
     * @param array $params A set of query string parameters to merge in to the href
     * of the link.  E.g., if this link was clicked on the items/browse?collection=1
     * page, and array('foo'=>'bar') was passed as this argument, the new URI would
     * be items/browse?collection=1&foo=bar.
     */
    public function link_to_items_rss($text = null, $params=array())
    {
    //     if (!$text) {
    //         $text = __('RSS');
    //     }
    //     return '<a href="' . html_escape(items_output_url('rss2', $params)) . '" class="rss">' . $text . '</a>';
        if (!$text) {
            $text = $this->stranslate('RSS');
        }
        $params = $this->mapQuery($params);
        return '<a href="' . $this->html_escape($this->items_output_url('rss2', $params)) . '" class="rss">' . $text . '</a>';
    }

    /**
     * Get a link to the item immediately following the current one.
     *
     * @package Omeka\Function\View\Navigation
     * @uses link_to()
     * @param string $text
     * @param array $props
     * @return string
     */
    public function link_to_next_item_show($text = null, $props = array())
    {
    //     if (!$text) {
    //         $text = __("Next Item &rarr;");
    //     }
    //     $item = get_current_record('item');
    //     if($next = $item->next()) {
    //         return link_to($next, 'show', $text, $props);
    //     }
        if (!$text) {
            $text = $this->stranslate("Next Item &rarr;");
        }
        $next = $this->get_next_item();
        if ($next) {
            return $this->link_to($next, 'show', $text, $props);
        }
    }

    /**
     * Get a link to the item immediately before the current one.
     *
     * @package Omeka\Function\View\Navigation
     * @uses link_to()
     * @param string $text
     * @param array $props
     * @return string
     */
    public function link_to_previous_item_show($text = null, $props = array())
    {
    //     if (!$text) {
    //         $text = __('&larr; Previous Item');
    //     }
    //     $item = get_current_record('item');
    //     if($previous = $item->previous()) {
    //         return link_to($previous, 'show', $text, $props);
    //     }
        if (!$text) {
            $text = $this->stranslate('&larr; Previous Item');
        }
        $previous = $this->get_previous_item();
        if ($previous) {
            return $this->link_to($previous, 'show', $text, $props);
        }
    }

    /**
     * Get a link to a collection.
     *
     * The only differences from link_to() are that this function will automatically
     * use the "current" collection, and will use the collection title as the
     * link text.
     *
     * @package Omeka\Function\View\Navigation
     * @uses link_to()
     * @param string $text text to use for the title of the collection.  Default
     * behavior is to use the name of the collection.
     * @param array $props Set of attributes to use for the link.
     * @param array $action The action to link to for the collection.
     * @param array $collectionObj Collection record can be passed to this to
     * override the collection object retrieved by get_current_record().
     * @return string
     */
    public function link_to_collection($text = null, $props = array(), $action = 'show', $collectionObj = null)
    {
    //     if (!$collectionObj) {
    //         $collectionObj = get_current_record('collection');
    //     }

    //     $collectionTitle = metadata($collectionObj, array('Dublin Core', 'Title'));
    //     $text = !empty($text) ? $text : $collectionTitle;
    //     return link_to($collectionObj, $action, $text, $props);
        if (!$collectionObj) {
            $collectionObj = $this->get_current_record('collection');
        }

        $text = !empty($text) ? $text : $collectionObj->displayTitle();
        return $this->link_to($collectionObj, $action, $text, $props);
    }

    /**
     * Get a link to the public home page.
     *
     * @package Omeka\Function\View\Navigation
     * @param null|string $text
     * @param array $props
     * @return string
     */
    public function link_to_home_page($text = null, $props = array())
    {
    //     if (!$text) {
    //         $text = option('site_title');
    //     }
    //     return '<a href="' . html_escape(WEB_ROOT) . '" '. tag_attributes($props) . '>' . $text . "</a>\n";
        $view = $this->getView();
        if (!$text) {
            $text = $this->option('site_title');
        }
        $site = $this->currentSite();
        $url = $site
            ? $site->siteUrl(null, true)
            : $view->serverUrl() . $view->basePath();
        return '<a href="' . $this->html_escape($url) . '" '. $this->tag_attributes($props) . '>' . $text . '</a>' . PHP_EOL;
    }

    /**
     * Get a link to the admin home page.
     *
     * @package Omeka\Function\View\Navigation
     * @uses admin_url()
     * @see link_to_home_page()
     * @param null|string $text
     * @param array $props
     * @return string
     */
    public function link_to_admin_home_page($text = null, $props = array())
    {
    //     if (!$text) {
    //         $text = option('site_title');
    //     }
    //     return '<a href="' . html_escape(admin_url('')) . '" ' . tag_attributes($props)
    //          . '>' . $text . "</a>\n";
        $view = $this->getView();
        if (!$text) {
            $text = $this->option('site_title');
        }
        $site = $this->currentSite();
        $url = $site
            ? $site->adminUrl(null, true)
            : $view->serverUrl() . $view->basePath() . '/admin';
        return '<a href="' . $this->html_escape($url) . '" '. $this->tag_attributes($props) . '>' . $text . '</a>' . PHP_EOL;
    }

    /**
     * Create a navigation menu of links.
     *
     * @package Omeka\Function\View\Navigation
     * @param array $navLinks The array of links for the navigation.
     * @param string $name Optionally, the name of a filter to pass the links
     *  through before using them.
     * @param array $args Optionally, arguments to pass to the filter
     *
     * @return Zend_View_Helper_Navigation_Menu The navigation menu object. Can
     *  generally be treated simply as a string.
     */
    public function nav(array $navLinks, $name = null, array $args = array())
    {
    //     if ($name !== null) {
    //         $navLinks = apply_filters($name, $navLinks, $args);
    //     }

    //     $menu = get_view()->navigation()->menu(new Omeka_Navigation($navLinks));

    //     if ($acl = get_acl()) {
    //         $menu->setRole(current_user())->setAcl($acl);
    //     }

    //     return $menu;
        // NOTE Use navigation() instead. Just a workaround for the first level.
        $view = $this->getView();

        if ($name !== null) {
            $navLinks = $this->apply_filters($name, $navLinks, $args);
        }

        $html = '<ul class="nav navigation">';
        foreach ($navLinks as $link) {
            if (isset($link['uri']) && isset($link['label'])) {
                $html .= '<li>' . $view->hyperlink($link['label'], $link['uri']) . '</li>';
            }
        }
        $html .= '</ul>';
        return $html;
    }

    /**
     * Get HTML for a pagination control for a browse page.
     *
     * @package Omeka\Function\View\Navigation
     * @uses Zend_View_Helper_PaginationControl::paginationControl()
     * @param array $options Configurable parameters for the pagination links. The
     * following options are available:
     * - 'scrolling_style' (string) See Zend_View_Helper_PaginationControl for
     *   more details.  Default 'Sliding'.
     * - 'partial_file' (string) View script to use to render the pagination
     *   HTML. Default is 'common/pagination_control.php'.
     * - 'page_range' (integer) See Zend_Paginator::setPageRange() for details.
     *   Default is 5.
     * - 'total_results' (integer) Total results to paginate through. Default is
     *   provided by the 'total_results' key of the 'pagination' array that is
     *   typically registered by the controller.
     * - 'page' (integer) Current page of the result set.  Default is the 'page'
     *   key of the 'pagination' array.
     * - 'per_page' (integer) Number of results to display per page. Default is
     * the 'per_page' key of the 'pagination' array.
     *
     * @return string HTML for the pagination links.
     */
    public function pagination_links($options = array())
    {
    //     if (Zend_Registry::isRegistered('pagination')) {
    //         // If the pagination variables are registered, set them for local use.
    //         $p = Zend_Registry::get('pagination');
    //     } else {
    //         // If the pagination variables are not registered, set required defaults
    //         // arbitrarily to avoid errors.
    //         $p = array('total_results' => 1, 'page' => 1, 'per_page' => 1);
    //     }

    //     // Set preferred settings.
    //     $scrollingStyle = isset($options['scrolling_style']) ? $options['scrolling_style'] : 'Sliding';
    //     $partial = isset($options['partial_file']) ? $options['partial_file'] : 'common/pagination_control.php';
    //     $pageRange = isset($options['page_range']) ? (int) $options['page_range'] : 5;
    //     $totalCount = isset($options['total_results']) ? (int) $options['total_results'] : (int) $p['total_results'];
    //     $pageNumber = isset($options['page']) ? (int) $options['page'] : (int) $p['page'];
    //     $itemCountPerPage = isset($options['per_page']) ? (int) $options['per_page'] : (int) $p['per_page'];

    //     // Create an instance of Zend_Paginator.
    //     $paginator = Zend_Paginator::factory($totalCount);

    //     // Configure the instance.
    //     $paginator->setCurrentPageNumber($pageNumber)
    //               ->setItemCountPerPage($itemCountPerPage)
    //               ->setPageRange($pageRange);

    //     return get_view()->paginationControl($paginator, $scrollingStyle, $partial);
        $partialName = isset($options['partial_file']) ? $options['partial_file'] : null;
        $totalCount = isset($options['total_results']) ? (integer) $options['total_results'] : null;;
        $currentPage = isset($options['page']) ? (integer) $options['page'] : null;
        $perPage = isset($options['per_page']) ? (integer) $options['per_page'] : null;

        // NOTE Pagination doesn't manage "scrolling_style" and "page_range"
        // anymore. Use paginator() if needed.
        $scrollingStyle = isset($options['scrolling_style']) ? $options['scrolling_style'] : null;
        $pageRange = isset($options['page_range']) ? (integer) $options['page_range'] : null;

        $pagination = $this->getView()
            ->pagination($partialName, $totalCount, $currentPage, $perPage);
        return $pagination;
    }

    /**
     * Get the main navigation for the public site.
     *
     * @package Omeka\Function\View\Navigation
     * @return Zend_View_Helper_Navigation_Menu Can be echoed like a string or
     * manipulated by the theme.
     */
    public function public_nav_main()
    {
    //     $view = get_view();
    //     $nav = new Omeka_Navigation;
    //     $nav->loadAsOption(Omeka_Navigation::PUBLIC_NAVIGATION_MAIN_OPTION_NAME);
    //     $nav->addPagesFromFilter(Omeka_Navigation::PUBLIC_NAVIGATION_MAIN_FILTER_NAME);
    //     return $view->navigation()->menu($nav);
        $view = $this->getView();
        $site = $this->currentSite();
        if (empty($site)) {
            return;
        }
        return $site->publicNav()->menu()->renderMenu(null, [
            'maxDepth' => $view->themeSetting('nav_depth') - 1,
        ]);
    }

    /**
     * Get the navigation for items.
     *
     * @package Omeka\Function\View\Navigation
     * @uses nav()
     * @param array $navArray
     * @param integer|null $maxDepth
     * @return string
     */
    public function public_nav_items(array $navArray = null, $maxDepth = 0)
    {
    //     if (!$navArray) {
    //         $navArray = array(
    //             array(
    //                 'label' =>__('Browse All'),
    //                 'uri' => url('items/browse'),
    //             ));
    //             if (total_records('Tag')) {
    //                 $navArray[] = array(
    //                     'label' => __('Browse by Tag'),
    //                     'uri' => url('items/tags')
    //                 );
    //             }
    //             $navArray[] = array(
    //                 'label' => __('Search Items'),
    //                 'uri' => url('items/search')
    //             );
    //     }
    //     return nav($navArray, 'public_navigation_items');
        if (!$navArray) {
            $navArray = array(
                array(
                    'label' => $this->stranslate('Browse All'),
                    'uri' => $this->url('item'),
            ));
            // if ($this->total_records('Tag')) {
            //     $navArray[] = array(
            //         'label' => $this->__('Browse by Tag'),
            //         'uri' => $this->url('items/tags')
            //     );
            // }
            $navArray[] = array(
                'label' => $this->stranslate('Search Items'),
                'uri' => $this->url('item/search')
            );
        }
        return $this->nav($navArray, 'public_navigation_items');
    }

    /**
     * Escape a value to display properly as HTML.
     *
     * This uses the 'html_escape' filter for escaping.
     *
     * @package Omeka\Function\Text
     * @param string $value
     * @return string
     */
    public function html_escape($value)
    {
    //     return apply_filters('html_escape', $value);
        // TODO Check if the filters nl2br of html_escape are still needed.
        // In Omeka C, filters are used only in "application/views/scripts/custom.php".
        // When displaying item metadata (or anything else that makes use of the
        // 'html_escape' filter), make sure we escape entities correctly with UTF-8.
        // The second thing that needs to happen for all metadata that needs to be escaped
        // to HTML is that new lines need to be represented as <br /> tags.
        return $this->getView()->escapeHtml($value);
        // return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Escape a value for use in javascript.
     *
     * This is a convenience function for encoding a value using JSON notation.
     * Must be used when interpolating PHP output in javascript. Note on usage: do
     * not wrap the resulting output of this function in quotes, as proper JSON
     * encoding will take care of that.
     *
     * @package Omeka\Function\Text
     * @uses Zend_Json::encode()
     * @param string $value
     * @return string
     */
    public function js_escape($value)
    {
    //     return Zend_Json::encode($value);
        // TODO May need to choose between escapeJs() and Json::encode().
        return $this->getView()->escapeJs($value);
        // return \Zend\Json\Json::encode($value, false, ['enableJsonExprFinder' => true]);
    }

    /**
     * Escape a value for use in XML.
     *
     * @package Omeka\Function\Text
     * @param string $value
     * @return string
     */
    public function xml_escape($value)
    {
    //     return htmlspecialchars(preg_replace('#[\x00-\x08\x0B\x0C\x0E-\x1F]+#', '',
    //         $value), ENT_QUOTES);
        return htmlspecialchars(preg_replace('#[\x00-\x08\x0B\x0C\x0E-\x1F]+#', '',
            $value), ENT_QUOTES | ENT_XML1);
    }

    /**
     * Replace newlines in a block of text with paragraph tags.
     *
     * Looks for 2 consecutive line breaks resembling a paragraph break and wraps
     * each of the paragraphs with a <p> tag.  If no paragraphs are found, then the
     * original text will be wrapped with line breaks.
     *
     * @package Omeka\Function\Text
     * @link http://us.php.net/manual/en/function.nl2br.php#73479
     * @param string $str
     * @return string
     */
    public function text_to_paragraphs($str)
    {
        return str_replace('<p></p>', '', '<p>'
            . preg_replace('#([\r\n]\s*?[\r\n]){2,}#', '</p>$0<p>', $str) . '</p>');
    }

    /**
     * Return a substring of a given piece of text.
     *
     * Note: this will only split strings on the space character.
     * this will also strip html tags from the text before getting a snippet
     *
     * @package Omeka\Function\Text
     * @param string $text Text to take snippet of
     * @param int $startPos Starting position of snippet in string
     * @param int $endPos Maximum length of snippet
     * @param string $append String to append to snippet if truncated
     * @return string Snippet of given text
     */
    public function snippet($text, $startPos, $endPos, $append = '…')
    {
        // strip html tags from the text
    //     $text = strip_formatting($text);
        $text = $this->strip_formatting($text);

        $textLength = strlen($text);

        // Calculate the start position. Set to zero if the start position is
        // null or 0, OR if the start offset is greater than the length of the
        // original text.
        $startPosOffset = $startPos - $textLength;
        $startPos = !$startPos || $startPosOffset > $textLength
                    ? 0
                    : strrpos($text, ' ', $startPosOffset);

        // Calculate the end position. Set to the length of the text if the
        // end position is greater than or equal to the length of the original
        // text, OR if the end offset is greater than the length of the
        // original text.
        $endPosOffset = $endPos - $textLength;
        $endPos = $endPos >= $textLength || $endPosOffset > $textLength
                  ? $textLength
                  : strrpos($text, ' ', $endPosOffset);

        // Set the snippet by getting its substring.
        $snippet = substr($text, $startPos, $endPos - $startPos);

        // Return the snippet without the append string if the text's original
        // length equals to 1) the length of the snippet, i.e. when the return
        // string is identical to the passed string; OR 2) the calculated
        // end position, i.e. when the return string ends at the same point as
        // the passed string.
        return strlen($snippet) == $textLength || $endPos == $textLength
             ? $snippet
             : $snippet . $append;
    }

    /**
     * Return a substring of the text by limiting the word count.
     *
     * Note: it strips the HTML tags from the text before getting the snippet
     *
     * @package Omeka\Function\Text
     * @param string $text
     * @param integer $maxWords
     * @param string $ellipsis
     * @return string
     */
    public function snippet_by_word_count($text, $maxWords = 20, $ellipsis = '...')
    {
        // strip html tags from the text
    //     $text = strip_formatting($text);
        $text = $this->strip_formatting($text);
        if ($maxWords > 0) {
            $textArray = explode(' ', $text);
            if (count($textArray) > $maxWords) {
                $text = implode(' ', array_slice($textArray, 0, $maxWords)) . $ellipsis;
            }
        } else {
            return '';
        }
        return $text;
    }

    /**
     * Strip HTML tags from a string.
     *
     * This is essentially a wrapper around PHP's strip_tags() function, with the
     * added benefit of returning a fallback string in case the resulting stripped
     * string is empty or contains only whitespace.
     *
     * @package Omeka\Function\Text
     * @uses strip_tags()
     * @param string $str The string to be stripped of HTML formatting.
     * @param string $allowableTags The string of tags to allow when stripping tags.
     * @param string $fallbackStr The string to be used as a fallback.
     * @return The stripped string.
     */
    public function strip_formatting($str, $allowableTags = '', $fallbackStr = '')
    {
        // Strip the tags.
        $str = strip_tags($str, $allowableTags);
        // Remove non-breaking space html entities.
        $str = str_replace('&nbsp;', '', $str);
        // If only whitepace remains, return the fallback string.
        if (preg_match('/^\s*$/', $str)) {
            return $fallbackStr;
        }
        // Return the deformatted string.
        return $str;
    }

    /**
     * Convert a word or phrase to a valid HTML ID.
     *
     * For example: 'Foo Bar' becomes 'foo-bar'.
     *
     * This function converts to lowercase, replaces whitespace with hyphens,
     * removes all non-alphanumerics, removes leading or trailing delimiters,
     * and optionally prepends a piece of text.
     *
     * @package Omeka\Function\Text
     * @param string $text The text to convert
     * @param string $prepend Another string to prepend to the ID
     * @param string $delimiter The delimiter to use (- by default)
     * @return string
     */
    public function text_to_id($text, $prepend = null, $delimiter = '-')
    {
        $text = strtolower($text);
        $id = preg_replace('/\s/', $delimiter, $text);
        $id = preg_replace('/[^\w\-]/', '', $id);
        $id = trim($id, $delimiter);
        $prepend = (string) $prepend;
        return !empty($prepend) ? join($delimiter, array($prepend, $id)) : $id;
    }

    /**
     * Convert any URLs in a given string to links.
     *
     * @package Omeka\Function\Text
     * @param string $str The string to be searched for URLs to convert to links.
     * @return string
     */
    public function url_to_link($str)
    {
    //      return preg_replace_callback($pattern, 'url_to_link_callback', $str);
        $pattern = "#(\bhttps?://\S+\b)#";
        return preg_replace_callback($pattern, [$this, 'url_to_link_callback'], $str);
    }

    /**
     * Callback for converting URLs with url_to_link.
     *
     * @package Omeka\Function\Text
     * @see url_to_link
     * @param array $matches preg_replace_callback matches array
     * @return string
     */
    public function url_to_link_callback($matches)
    {
    //     return '<a href="' . htmlspecialchars($matches[1]) . '">' . $matches[1] . '</a>';
        return '<a href="' . htmlspecialchars($matches[1], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
           . '">' . $matches[1] . '</a>';
    }

    /**
     * Get the most recent tags.
     *
     * @package Omeka\Function\View
     * @uses get_records()
     * @param integer $limit The maximum number of recent tags to return
     * @return array
     */
    public function get_recent_tags($limit = 10)
    {
    //     return get_records('Tag', array('sort_field' => 'time', 'sort_dir' => 'd'), $limit);
        // NOTE Useless: Tags are currently not managed by Omeka S.
        return array();
        $query = [
            'sort_by' => 'created',
            'sort_order' => 'desc',
            'limit' => $limit,
        ];
        $site = $this->currentSite();
        if ($site) {
            $query['site_id'] = $site->id();
        }

        $response= $this->getView()->api()->search('tags', $query);
        return $response->getContent();
    }

    /**
     * Create a tag cloud made of divs that follow the hTagcloud microformat
     *
     * @package Omeka\Function\View\Tag
     * @param Omeka_Record_AbstractRecord|array $recordOrTags The record to retrieve
     * tags from, or the actual array of tags
     * @param string|null $link The URI to use in the link for each tag. If none
     * given, tags in the cloud will not be given links.
     * @param int $maxClasses
     * @param bool $tagNumber
     * @param string $tagNumberOrder
     * @return string HTML for the tag cloud
     */
    public function tag_cloud($recordOrTags = null, $link = null, $maxClasses = 9, $tagNumber = false, $tagNumberOrder = null)
    {
        // NOTE Useless: Tags are currently not managed by Omeka S.
        if (!$recordOrTags) {
    //         $tags = array();
            $tags = [];
        } else if (is_string($recordOrTags)) {
    //         $tags = get_current_record($recordOrTags)->Tags;
            // $tags = $this->get_current_record($recordOrTags)->tags();
            $tags = [];
    //     } else if ($recordOrTags instanceof Omeka_Record_AbstractRecord) {
        } elseif ($recordOrTags instanceof AbstractResourceRepresentation) {
    //         $tags = $recordOrTags->Tags;
            // $tags = $recordOrTags->tags();
            $tags = [];
        } else {
            $tags = $recordOrTags;
        }

        if (empty($tags)) {
            return '<p>' . $this->stranslate('No tags are available.') . '</p>';
        }

        //Get the largest value in the tags array
        $largest = 0;
        foreach ($tags as $tag) {
            if($tag["tagCount"] > $largest) {
                $largest = $tag['tagCount'];
            }
        }
        $html = '<div class="hTagcloud">';
        $html .= '<ul class="popularity">';

        if ($largest < $maxClasses) {
            $maxClasses = $largest;
        }

        foreach( $tags as $tag ) {
            $size = (int)(($tag['tagCount'] * $maxClasses) / $largest - 1);
            $class = str_repeat('v', $size) . ($size ? '-' : '') . 'popular';
            $html .= '<li class="' . $class . '">';
            if ($link) {
    //             $html .= '<a href="' . html_escape(url($link, array('tags' => $tag['name']))) . '">';
                $html .= '<a href="' . $this->html_escape($this->url($link, array('tags' => $tag['name']))) . '">';
            }
            if($tagNumber && $tagNumberOrder == 'before') {
                $html .= ' <span class="count">'.$tag['tagCount'].'</span> ';
            }
    //         $html .= html_escape($tag['name']);
            $html .= $this->html_escape($tag['name']);
            if($tagNumber && $tagNumberOrder == 'after') {
                $html .= ' <span class="count">'.$tag['tagCount'].'</span> ';
            }
            if ($link) {
                $html .= '</a>';
            }
            $html .= '</li>' . "\n";
        }
        $html .= '</ul></div>';

        return $html;
    }

    /**
     * Return a tag string given an Item, Exhibit, or a set of tags.
     *
     * @package Omeka\Function\View\Tag
     * @param Omeka_Record_AbstractRecord|array $recordOrTags The record to retrieve
     * tags from, or the actual array of tags
     * @param string|null $link The URL to use for links to the tags (if null, tags
     * aren't linked)
     * @param string $delimiter ', ' (comma and whitespace) is the default tag_delimiter option. Configurable in Settings
     * @return string HTML
     */
    public function tag_string($recordOrTags = null, $link = 'items/browse', $delimiter = null)
    {
        // NOTE Useless: Tags are currently not managed by Omeka S.
        // Set the tag_delimiter option if no delimiter was passed.
        if (is_null($delimiter)) {
    //         $delimiter = get_option('tag_delimiter') . ' ';
            $delimiter = $this->get_option('tag_delimiter') . ' ';
        }

        if (!$recordOrTags) {
            $tags = array();
        } else if (is_string($recordOrTags)) {
    //         $tags = get_current_record($recordOrTags)->Tags;
            // $tags = $this->get_current_record($recordOrTags)->tags();
            $tags = [];
    //     } else if ($recordOrTags instanceof Omeka_Record_AbstractRecord) {
        } elseif ($recordOrTags instanceof AbstractResourceRepresentation) {
    //         $tags = $recordOrTags->Tags;
            // $tags = $recordOrTags->tags();
            $tags = [];
        } else {
            $tags = $recordOrTags;
        }

        if (empty($tags)) {
            return '';
        }

        $tagStrings = array();
        foreach ($tags as $tag) {
            $name = $tag['name'];
            if (!$link) {
    //             $tagStrings[] = html_escape($name);
                $tagStrings[] = $this->html_escape($name);
            } else {
    //             $tagStrings[] = '<a href="' . html_escape(url($link, array('tags' => $name))) . '" rel="tag">' . html_escape($name) . '</a>';
                $tagStrings[] = '<a href="' . $this->html_escape($this->url($link, array('tags' => $name))) . '" rel="tag">' . $this->html_escape($name) . '</a>';
            }
        }
    //     return join(html_escape($delimiter), $tagStrings);
        return join($this->html_escape($delimiter), $tagStrings);
    }

    /**
     * Get a URL given the provided arguments.
     *
     * Instantiates view helpers directly because a view may not be registered.
     *
     * @package Omeka\Function\View\Navigation
     * @uses Omeka_View_Helper_Url::url() See for details on usage.
     * @param mixed $options If a string is passed it is treated as an
     *  Omeka-relative link. So, passing 'items' would create a link to the items
     *  page. If an array is passed (or no argument given), it is treated as options
     *  to be passed to Omeka's routing system.
     *  Note that in the Url Helper, if the first argument is a string, the second
     *  argument, not the third, is the queryParams
     * @param string $route The route to use if an array is passed in the first argument.
     * @param mixed $queryParams A set of query string parameters to append to the URL
     * @param bool $reset Whether Omeka should discard the current route when generating the URL.
     * @param bool $encode Whether the URL should be URL-encoded
     * @return string HTML
     */
    public function url($options = array(), $route = null, $queryParams = array(),
        $reset = false, $encode = true
    ) {
    //     $helper = new Omeka_View_Helper_Url;
    //     return $helper->url($options, $route, $queryParams, $reset, $encode);
        // NOTE This is the view helper from Omeka 2, integrated here because
        // it is short and there is already the default helper url().
        // NOTE In Zend 3, the query params is now a sub option 'query'. This
        // method uses the old params, unlike the new $view->url().
        // NOTE The parameter encode is managed automatically by Zend.
        $view = $this->getView();

        // Upgrade some query params.
        if (!empty($queryParams)) {
            $queryParams = $this->mapQuery($queryParams);
        }

        /**
         * Generate a URL for use in one of Omeka's view templates.
         *
         * There are two ways to use this method.  The first way is for backwards
         * compatibility with older versions of Omeka as well as ease of use for
         * theme writers.
         *
         * Here is an example of what URLs are generated by calling the function in
         * different ways. The output from these examples assume that Omeka is
         * running on the root of a particular domain, though that is of no
         * importance to how the function works.
         *
         * <code>
         * echo $this->url('items/browse');
         * // outputs "/items/browse"
         *
         * echo $this->url('items/browse', array('tags'=>'foo'));
         * // outputs "/items/browse?tags=foo"
         *
         * echo $this->url(array('controller'=>'items', 'action'=>'browse'));
         * // outputs "/items/browse"
         *
         * echo $this->url(
         *     array('controller'=>'items', 'action'=>'browse'),
         *     'otherRoute',
         *     array('tags'=>'foo'),
         * );
         * // outputs "/miscellaneous?tags=foo"
         * </code>
         *
         * The first example takes a URL string exactly as one would expect it to
         * be. This primarily exists for ease of use by theme writers. The second
         * example appends a query string to the URL by passing it as an array. Note
         * that in both examples, the first string must be properly URL-encoded in
         * order to work. url('foo bar') would not work because of the space.
         *
         * In the third example, the URL is being built directly from parameters
         * passed to it. For more details on this, please see the Zend Framework's
         * documentation.
         *
         * In the last example, 'otherRoute' is the name of the route being used, as
         * defined either in the routes.ini file or via a plugin. For examples of
         * how to add routes via a plugin, please see Omeka's documentation.
         *
         * @internal Note that the argument list for this function is almost exactly
         * the same as Zend_View_Helper_Url::url(), except that a $queryParams
         * argument has been inserted as the 3rd argument, in between $name and
         * $reset. This allows the theme writer to pass in an array of parameters
         * that will be appended to the query string and properly encoded.
         *
         * @param string|array $options
         * @param string|null|array $name Optional If $options is an array, $name
         * should be the route name (string) or null. If $options is a string, $name
         * should be the set of query string parameters (array) or null.
         * @param array $queryParams Optional Set of query string parameters.
         * @param boolean $reset Optional Whether or not to reset the route
         * parameters implied by the current request, e.g. if the current controller
         * is 'items', then 'controller'=>'items' will be inferred when assembling
         * the route.
         * @param boolean $encode
         * @return string
         */
        $url = '';

        // If it's a string, just append it.
        if (is_string($options)) {
            $url = ltrim($options, '/');

            // Convert the url if needed.
            $url = $this->mapRoute($url);

            $site = $this->currentSite();
            // Check if the string starts with the base path.
            $base = $site ? 's/' . $site->slug() : 'admin';
            if (strpos($options, $base) !== 0) {
                $url = $base . '/' . $url;
            }
            $url = rtrim($view->basePath(), '/') . '/' . $url;

            // If the first argument is a string, then the second is a set of
            // parameters to append to the query string. If the first argument
            // is an array, then the query parameters are the 3rd argument.
            $queryParams = $route;
            if ($queryParams) {
                $url .= '?' . http_build_query($queryParams);
            }
        }
        // If it's an array, assemble the URL with the Zend view helper url().
        elseif (is_array($options)) {
            if (!isset($options['site-slug'])) {
                $site = $this->currentSite();
                if (empty($site)) {
                    return;
                }
                $options['site-slug'] = $site->slug();
            }
            $url = $view->url($route, $options, ['query' => $queryParams], !$reset);
        }

        return $url;
    }

    /**
     * Get an absolute URL.
     *
     * This is necessary because Zend_View_Helper_Url returns relative URLs, though
     * absolute URLs are required in some contexts. Instantiates view helpers
     * directly because a view may not be registered.
     *
     * @package Omeka\Function\View\Navigation
     * @uses Zend_View_Helper_ServerUrl::serverUrl()
     * @uses Omeka_View_Helper_Url::url()
     * @param mixed $options If a string is passed it is treated as an
     *  Omeka-relative link. So, passing 'items' would create a link to the items
     *  page. If an array is passed (or no argument given), it is treated as options
     *  to be passed to Omeka's routing system.
     * @param string $route The route to use if an array is passed in the first argument.
     * @param mixed $queryParams A set of query string parameters to append to the URL
     * @param bool $reset Whether Omeka should discard the current route when generating the URL.
     * @param bool $encode Whether the URL should be URL-encoded
     * @return string HTML
     */
    public function absolute_url($options = array(), $route = null, $queryParams = array(),
        $reset = false, $encode = true
    ) {
    //     $serverUrlHelper = new Zend_View_Helper_ServerUrl;
    //     $urlHelper = new Omeka_View_Helper_Url;
    //     return $serverUrlHelper->serverUrl()
    //          . $urlHelper->url($options, $route, $queryParams, $reset, $encode);
        return $this->getView()->serverUrl()
            . $this->url($options, $route, $queryParams, $reset, $encode);
    }

    /**
     * Get the current URL with query parameters appended.
     *
     * Instantiates view helpers directly because a view may not be registered.
     *
     * @package Omeka\Function\View\Navigation
     * @param array $params
     * @return string
     */
    public function current_url(array $params = array())
    {
    //     // Get the URL before the ?.
    //     $request = Zend_Controller_Front::getInstance()->getRequest();
    //     $urlParts = explode('?', $request->getRequestUri());
    //     $url = $urlParts[0];
    //     if ($params) {
    //         // Merge $_GET and passed parameters to build the complete query.
    //         $query = array_merge($_GET, $params);
    //         $queryString = http_build_query($query);
    //         $url .= "?$queryString";
    //     }
    //     return $url;
        $view = $this->getView();
        if ($params) {
            $params = $this->mapQuery($params);
            $query = $view->params()->fromQuery();
            $query = array_merge($query, $params);
            $url = $view->url(null, [], ['query' => $query], true);
        } else {
            $url = $view->url(null, [], true);
        }
        return $url;
    }

    /**
     * Check if the given URL matches the current request URL.
     *
     * Instantiates view helpers directly because a view may not be registered.
     *
     * @package Omeka\Function\View\Navigation
     * @param string $url
     * @return boolean
     */
    public function is_current_url($url)
    {
    //     $request = Zend_Controller_Front::getInstance()->getRequest();
    //     $currentUrl = $request->getRequestUri();
    //     $baseUrl = $request->getBaseUrl();
        $view = $this->getView();
        $currentUrl = $this->current_url();
        $serverUrl = $view->serverUrl();
        $baseUrl = $view->basePath();

        // Strip out the protocol, host, base URL, and rightmost slash before
        // comparing the URL to the current one
    //     $stripOut = array(WEB_DIR, @$_SERVER['HTTP_HOST'], $baseUrl);
        $stripOut = array($serverUrl . $baseUrl, @$_SERVER['HTTP_HOST'], $baseUrl);
        $currentUrl = rtrim(str_replace($stripOut, '', $currentUrl), '/');
        $url = rtrim(str_replace($stripOut, '', $url), '/');

        if (strlen($url) == 0) {
            return (strlen($currentUrl) == 0);
        }
        // NOTE This assertion is strange and not compatible with Omeka S.
    //     return ($url == $currentUrl) or (strpos($currentUrl, $url) === 0);
        return $url == $currentUrl;
    }

    /**
     * Get a URL to a record.
     *
     * @package Omeka\Function\View\Navigation
     * @uses Omeka_View_Helper_RecordUrl::recordUrl()
     * @param Omeka_Record_AbstractRecord|string $record
     * @param string|null $action
     * @param bool $getAbsoluteUrl
     * @param array $queryParams
     * @return string
     */
    public function record_url($record, $action = null, $getAbsoluteUrl = false, $queryParams = array())
    {
    //     return get_view()->recordUrl($record, $action, $getAbsoluteUrl, $queryParams);
        // Adapted from the view helper Omeka 2 (Omeka_View_Helper_RecordUrl).

        // Get the current record from the view if passed as a string.
        if (is_string($record)) {
            $record = $this->get_current_record($record);
        }
        if (!($record instanceof AbstractResourceRepresentation)) {
            throw new InvalidArgumentException($this->stranslate('Invalid resource passed while getting resource URL.'));
        }

        // If no action is passed, use the default action set in the signature
        // of Omeka_Record_AbstractRecord::getRecordUrl().
        if (is_null($action)) {
            $url = $record->url(null, $getAbsoluteUrl);
        } elseif (is_string($action)) {
            $url = $record->url($action, $getAbsoluteUrl);
        } else {
            throw new InvalidArgumentException($this->stranslate('Invalid action passed while getting resource URL.'));
        }

        // Assume a well-formed URL if getRecordUrl() returns a string.
        if (is_string($url)) {
            if ($queryParams) {
                $queryParams = $this->mapQuery($queryParams);
                $query = http_build_query($queryParams);
                // Append params if query is already part of the URL.
                if (strpos($url, '?') === false) {
                    $url .= '?' . $query;
                } else {
                    $url .= '&' . $query;
                }
            }
            return $url;
        } else {
            throw new InvalidArgumentException($this->stranslate('Invalid return value while getting resource URL.'));
        }
    }

    /**
     * Get a URL to an output format page.
     *
     * @package Omeka\Function\View\Navigation
     * @uses url()
     * @param string $output
     * @param array $otherParams
     * @return string
     */
    public function items_output_url($output, $otherParams = array())
    {
        $queryParams = array();

        // Provide additional query parameters if the current page is items/browse.
    //     $request = Zend_Controller_Front::getInstance()->getRequest();
    //     if ('items' == $request->getControllerName() && 'browse' == $request->getActionName()) {
    //         $queryParams = $_GET;
        $view = $this->getView();
        $request = $view->params()->fromRoute();
        if ('item' == $request['__CONTROLLER__'] && 'browse' == $request['action']) {
            $queryParams = $view->params()->fromQuery();;
            unset($queryParams['submit_search']);
            unset($queryParams['page']);
            unset($queryParams['submit']);
        }
        $otherParams = $this->mapQuery($otherParams);
        $queryParams = array_merge($queryParams, $otherParams);
        $queryParams['output'] = $output;

    //     return url(array('controller'=>'items', 'action'=>'browse'), 'default', $queryParams);
        return $this->url(
            ['controller'=>'item', 'action'=>'browse'],
            'site/resource',
            $queryParams
        );
    }

    /**
     * Get the provided file's URL.
     *
     * @package Omeka\Function\View\Navigation
     * @uses File::getWebPath()
     * @param File $file
     * @param string $format
     * @return string
     */
    public function file_display_url(/* File */ $file, $format = 'fullsize')
    {
    //     if (!$file->exists()) {
    //         return false;
    //     }
    //     return $file->getWebPath($format);
        if (!($file instanceof MediaRepresentation)) {
            return false;
        }
        $format = $this->mapDerivative($format);
        return $format == 'original'
            ? $file->originalUrl()
            : $file->thumbnailUrl($format);
    }

    /**
     * Get a URL to the public theme.
     *
     * @package Omeka\Function\View\Navigation
     * @uses set_theme_base_url()
     * @uses revert_theme_base_url()
     * @param mixed $args
     * @return string
     */
    // public function public_url()
    public function public_url(...$params)
    {
        // NOTE Useless in public theme of Omeka S.
    //     set_theme_base_url('public');
    //     $args = func_get_args();
    //     $url = call_user_func_array('url', $args);
    //     revert_theme_base_url();
    //     return $url;
        return $this->url(...$params);
    }

    /**
     * Get a URL to the admin theme.
     *
     * @package Omeka\Function\View\Navigation
     * @uses set_theme_base_url()
     * @uses revert_theme_base_url()
     * @param mixed $args
     * @return string
     */
    // public function admin_url()
    public function admin_url(...$params)
    {
    //     set_theme_base_url('admin');
    //     $args = func_get_args();
    //     $url = call_user_func_array('url', $args);
    //     revert_theme_base_url();
    //     return $url;
        // Used in the admin bar only.
        $url =  $this->url(...$params);
        $basePath = $this->getView()->basePath();
        if (empty($basePath) || $basePath === '/') {
            return '/admin' . $url;
        }
        $url = preg_replace('~^(' . preg_quote($basePath) . ')~', '\1/admin', $url);
        return $url;
    }

    /**
     * Set the base URL for the specified theme.
     *
     * @package Omeka\Function\View\Navigation
     * @uses Zend_Controller_Front::setBaseUrl()
     * @param string $theme
     */
    public function set_theme_base_url($theme = null)
    {
        // NOTE Useless in public theme of Omeka S.
    //     switch ($theme) {
    //         case 'public':
    //             $baseUrl = PUBLIC_BASE_URL;
    //             break;
    //         case 'admin':
    //             $baseUrl = ADMIN_BASE_URL;
    //             break;
    //         case 'install':
    //             $baseUrl = INSTALL_BASE_URL;
    //         default:
    //             $baseUrl = CURRENT_BASE_URL;
    //             break;
    //     }
    //     return $front->setBaseUrl($baseUrl);
    //     $front = Zend_Controller_Front::getInstance();
    //     $previousBases = $front->getParam('previousBaseUrls');
    //     $previousBases[] = $front->getBaseUrl();
    //     $front->setParam('previousBaseUrls', $previousBases);
    }

    /**
     * Revert the base URL to its previous state.
     *
     * @package Omeka\Function\View\Navigation
     * @uses Zend_Controller_Front::setBaseUrl()
     */
    public function revert_theme_base_url()
    {
        // NOTE Useless in public theme of Omeka S.
    //     $front = Zend_Controller_Front::getInstance();
    //     if (($previous = $front->getParam('previousBaseUrls'))) {
    //         $front->setBaseUrl(array_pop($previous));
    //         $front->setParam('previousBaseUrls', $previous);
    //     }
    }

    /**
     * Get the theme's logo image tag.
     *
     * @package Omeka\Function\View\Head
     * @uses get_theme_option()
     * @return string|null
     */
    public function theme_logo()
    {
    //     $logo = get_theme_option('Logo');
    //     if ($logo) {
    //         $storage = Zend_Registry::get('storage');
    //         $uri = $storage->getUri($storage->getPathByType($logo, 'theme_uploads'));
    //         return '<img src="' . $uri . '" alt="' . option('site_title') . '" />';
    //     }
        $logo = $this->getView()->themeSettingAssetUrl('logo');
        if ($logo) {
            return '<img src="' . $logo . '" alt="' . $this->option('site_title') . '" />';
        }
    }

    /**
     * Get the theme's header image tag.
     *
     * @package Omeka\Function\View\Head
     * @uses get_theme_option()
     * @return string|null
     */
    public function theme_header_image()
    {
    //     $headerImage = get_theme_option('Header Image');
    //     if ($headerImage) {
    //         $storage = Zend_Registry::get('storage');
    //         $headerImage = $storage->getUri($storage->getPathByType($headerImage, 'theme_uploads'));
    //         return '<div id="header-image"><img src="' . $headerImage . '" /></div>';
    //     }
        $headerImage = $this->getView()->themeSettingAssetUrl('header_image');
        if ($headerImage) {
            return '<div id="header-image"><img src="' . $headerImage . '" /></div>';
        }
    }

    /**
     * Get the theme's header background image style.
     *
     * @package Omeka\Function\View\Head
     * @uses get_theme_option()
     * @return string|null
     */
    public function theme_header_background()
    {
    //     $headerBg = get_theme_option('Header Background');
    //     if ($headerBg) {
    //         $storage = Zend_Registry::get('storage');
    //         $headerBg = $storage->getUri($storage->getPathByType($headerBg, 'theme_uploads'));
    //         return '<style type="text/css" media="screen">header {'
    //            . 'background:transparent url("' . $headerBg . '") center left no-repeat;'
    //            . '}</style>';
    //     }
        $headerBg = $this->getView()->themeSettingAssetUrl('header_background');
        if ($headerBg) {
            return '<style type="text/css" media="screen">header {'
                . 'background:transparent url("' . $headerBg . '") center left no-repeat;'
                    . '}</style>';
        }
    }

    /**
     * Check whether the current user has a give permission.
     *
     * @package Omeka\Function\User
     * @uses Zend_Acl::is_allowed()
     * @param string|Zend_Acl_Resource_Interface $resource The name of a resource,
     *  or a record implementing Zend_Acl_Resource_Interface
     * @param string|null $privilege The privilege to check for the resource.
     * @return boolean
     */
    public function is_allowed($resource, $privilege)
    {
    //     $acl = Zend_Controller_Front::getInstance()->getParam('bootstrap')->acl;
    //     $user = current_user();

    //     if (is_string($resource)) {
    //        $resource = ucwords($resource);
    //     }

    //     // User implements Zend_Acl_Role_Interface, so it can be checked directly by the ACL.
    //     return $acl->isAllowed($user, $resource, $privilege);
        return $this->getView()->userIsAllowed($resource, $privilege);
    }

    /**
     * Add a shortcode.
     *
     * @since 2.2
     * @package Omeka\Function\View
     * @uses Omeka_View_Helper_Shortcodes::shortcodeCallbacks()
     * @param string $shortcodeName Name of the new shortcode.
     * @param callback $function Callback to execute for this shortcode.
     */
    public function add_shortcode($shortcodeName, $function)
    {
        // NOTE Useless in public theme of Omeka S.
    //     return Omeka_View_Helper_Shortcodes::addShortcode($shortcodeName, $function);
        // TODO Add shortcodes in Omeka S.
    }

    /**
     * Various added functions from Omeka Classic.
     */

    /**
     * Return a valid citation for this item.
     *
     * @see Item::getCitation() in Omeka Classic.
     *
     * Generally follows Chicago Manual of Style note format for webpages.
     * Implementers can use the item_citation filter to return a customized
     * citation.
     *
     * @param I
     * @return string
     */
    public function getCitation(ItemRepresentation $item)
    {
        $citation = '';

        $creators = $item->value('dcterms:creator', ['all' => true]) ?: array();
        // Strip formatting and remove empty creator elements.
        $creators = array_filter(array_map(array($this, 'strip_formatting'), $creators));
        if ($creators) {
            switch (count($creators)) {
                case 1:
                    $creator = $creators[0];
                    break;
                case 2:
                    /// Chicago-style item citation: two authors
                    $creator = $this->stranslate('%1$s and %2$s', $creators[0], $creators[1]);
                    break;
                case 3:
                    /// Chicago-style item citation: three authors
                    $creator = $this->stranslate('%1$s, %2$s, and %3$s', $creators[0], $creators[1], $creators[2]);
                    break;
                default:
                    /// Chicago-style item citation: more than three authors
                    $creator = $this->stranslate('%s et al.', $creators[0]);
            }
            $citation .= "$creator, ";
        }

        $title = $item->displayTitle();
        if ($title) {
            $citation .= "&#8220;$title,&#8221; ";
        }

        $siteTitle = $this->option('site_title');
        if ($siteTitle) {
            $citation .= "<em>$siteTitle</em>, ";
        }

        $accessed = $this->format_date(time(), /* Zend_Date::DATE_LONG */ 'j F Y');
        $url = '<span class="citation-url">' . $this->getView()->escapeHtml($item->url(null, true)) . '</span>';
        /// Chicago-style item citation: access date and URL
        $citation .= $this->stranslate('accessed %1$s, %2$s.', $accessed, $url);

        return $this->apply_filters('item_citation', $citation, array('item' => $item));
    }
}
