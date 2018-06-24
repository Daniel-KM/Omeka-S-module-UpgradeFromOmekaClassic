<?php

namespace UpgradeFromOmekaClassic;

use Omeka\Module\AbstractModule;
use Omeka\Module\Exception\ModuleCannotInstallException;
use Omeka\Mvc\Controller\Plugin\Messenger;
use Omeka\Stdlib\Message;
use UpgradeFromOmekaClassic\Form\ConfigForm;
use Zend\EventManager\Event;
use Zend\EventManager\SharedEventManagerInterface;
use Zend\Form\Fieldset;
use Zend\Form\Element\Text;
use Zend\Form\Element\Checkbox;
use Zend\Form\Element\MultiCheckbox;
use Zend\Mvc\Controller\AbstractController;
use Zend\Mvc\MvcEvent;
use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Renderer\PhpRenderer;

class Module extends AbstractModule
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function onBootstrap(MvcEvent $event)
    {
        parent::onBootstrap($event);

        // TODO Find a better way to disable a module when dependencies are unavailable.
        $services = $event->getApplication()->getServiceManager();
        if (!$this->checkDependencies($services)) {
            $this->disableModule($services);
            $translator = $services->get('MvcTranslator');
            $message = new Message($translator->translate('The module "%s" was automatically deactivated because the dependencies are unavailable.'), // @translate
                __NAMESPACE__
            );
            $messenger = new Messenger();
            $messenger->addWarning($message);
        }

        $this->addRoutes();
    }

    public function install(ServiceLocatorInterface $serviceLocator)
    {
        $api = $serviceLocator->get('Omeka\ApiManager');
        $translator = $serviceLocator->get('MvcTranslator');

        if (!$this->checkDependencies($serviceLocator)) {
            $message = new Message($translator->translate('This module requires the module "%s".'), // @translate
                'Next'
            );
            throw new ModuleCannotInstallException($message);
        }

        $this->manageSettings($serviceLocator->get('Omeka\Settings'), 'install');
    }

    public function uninstall(ServiceLocatorInterface $serviceLocator)
    {
        $this->manageSettings($serviceLocator->get('Omeka\Settings'), 'uninstall');
    }

    protected function manageSettings($settings, $process, $key = 'config')
    {
        $config = require __DIR__ . '/config/module.config.php';
        $defaultSettings = $config[strtolower(__NAMESPACE__)][$key];
        foreach ($defaultSettings as $name => $value) {
            switch ($process) {
                case 'install':
                    $settings->set($name, $value);
                    break;
                case 'uninstall':
                    $settings->delete($name);
                    break;
            }
        }
    }

    /**
     * Check if all dependencies are enabled.
     *
     * @param ServiceLocatorInterface $services
     * @return bool
     */
    protected function checkDependencies(ServiceLocatorInterface $services)
    {
        $moduleManager = $services->get('Omeka\ModuleManager');
        $config = require __DIR__ . '/config/module.config.php';
        $dependencies = $config[strtolower(__NAMESPACE__)]['dependencies'];
        foreach ($dependencies as $moduleClass) {
            $module = $moduleManager->getModule($moduleClass);
            if (empty($module) || $module->getState() !== \Omeka\Module\Manager::STATE_ACTIVE) {
                return false;
            }
        }
        return true;
    }

    /**
     * Disable the module.
     *
     * @param ServiceLocatorInterface $services
     */
    protected function disableModule(ServiceLocatorInterface $services)
    {
        $moduleManager = $services->get('Omeka\ModuleManager');
        $module = $moduleManager->getModule(__NAMESPACE__);
        $moduleManager->deactivate($module);
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager)
    {
        $sharedEventManager->attach(
            \Omeka\Form\SiteSettingsForm::class,
            'form.add_elements',
            [$this, 'addFormElementsSiteSettings']
        );

        $sharedEventManager->attach(
            \Omeka\Form\SiteSettingsForm::class,
            'form.add_input_filters',
            [$this, 'addSiteSettingsFormFilters']
        );
    }

    public function getConfigForm(PhpRenderer $renderer)
    {
        $services = $this->getServiceLocator();
        $config = $services->get('Config');
        $settings = $services->get('Omeka\Settings');
        $form = $services->get('FormElementManager')->get(ConfigForm::class);

        $data = [];
        $defaultSettings = $config[strtolower(__NAMESPACE__)]['config'];
        foreach ($defaultSettings as $name => $value) {
            $data[$name] = $settings->get($name);
        }

        $form->init();
        $form->setData($data);
        $html = $renderer->formCollection($form);
        return $html;
    }

    public function handleConfigForm(AbstractController $controller)
    {
        $services = $this->getServiceLocator();
        $config = $services->get('Config');
        $settings = $services->get('Omeka\Settings');

        $params = $controller->getRequest()->getPost();

        $form = $services->get('FormElementManager')->get(ConfigForm::class);
        $form->init();
        $form->setData($params);
        if (!$form->isValid()) {
            $controller->messenger()->addErrors($form->getMessages());
            return false;
        }

        $defaultSettings = $config[strtolower(__NAMESPACE__)]['config'];
        foreach ($params as $name => $value) {
            if (array_key_exists($name, $defaultSettings)) {
                $settings->set($name, $value);
            }
        }
    }

    public function addFormElementsSiteSettings(Event $event)
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

    protected function addRoutes()
    {
        $serviceLocator = $this->getServiceLocator();
        $router = $serviceLocator->get('Router');
        if (!$router instanceof \Zend\Router\Http\TreeRouteStack) {
            return;
        }

        $settings = $serviceLocator->get('Omeka\Settings');

        $defaultSite = $settings->get('default_site');
        if (empty($defaultSite)) {
            return;
        }

        if (!$settings->get('upgrade_add_old_routes')) {
            return;
        }

        $api = $this->getServiceLocator()->get('Omeka\ApiManager');
        $site = $api->read('sites', $defaultSite)->getContent();
        if (empty($site)) {
            return;
        }
        $siteSlug = $site->slug();

        $router->addRoute('upgrade_collections', [
            'type' => Segment::class,
            'options' => [
                'route' => '/collections[/:action][/:id]',
                'constraints' => [
                    'id' => '\d+',
                    'action' => 'show|browse',
                ],
                'defaults' => [
                    '__NAMESPACE__' => 'Omeka\Controller\Site',
                    '__SITE__' => true,
                    'controller' => 'item-set',
                    'action' => 'browse',
                    'site-slug' => $siteSlug,
                ],
            ],
        ]);

        $router->addRoute('upgrade_items', [
            'type' => Segment::class,
            'options' => [
                'route' => '/items[/:action][/:id]',
                'constraints' => [
                    'id' => '\d+',
                    'action' => 'show|browse',
                ],
                'defaults' => [
                    '__NAMESPACE__' => 'Omeka\Controller\Site',
                    '__SITE__' => true,
                    'controller' => 'item',
                    'action' => 'browse',
                    'site-slug' => $siteSlug,
                ],
            ],
        ]);

        $router->addRoute('upgrade_files', [
            'type' => Segment::class,
            'options' => [
                'route' => '/files/show/:id',
                'constraints' => [
                    'id' => '\d+',
                    'action' => 'show',
                ],
                'defaults' => [
                    '__NAMESPACE__' => 'Omeka\Controller\Site',
                    '__SITE__' => true,
                    'controller' => 'media',
                    'action' => 'show',
                    'site-slug' => $siteSlug,
                ],
            ],
        ]);

        $pages = $site->pages();
        if (empty($pages)) {
            return;
        }
        $page = reset($pages);

        $router->addRoute('upgrade_homepage', [
            'type' => Literal::class,
            'options' => [
                'route' => '/',
                'defaults' => [
                    '__NAMESPACE__' => 'Omeka\Controller\Site',
                    '__SITE__' => true,
                    'controller' => 'page',
                    'action' => 'show',
                    'site-slug' => $siteSlug,
                    'page-slug' => $page->slug(),
                ],
            ],
        ]);
    }
}
