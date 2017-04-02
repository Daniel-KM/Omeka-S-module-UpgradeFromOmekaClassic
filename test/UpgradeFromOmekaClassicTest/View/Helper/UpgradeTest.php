<?php
namespace UpgradeFromOmekaClassicTest\View\Helper;

use Omeka\Api\Exception\BadRequestException;
use Omeka\Entity\Site;
use Omeka\Test\TestCase;
use OmekaTestHelper\Bootstrap;
use UpgradeFromOmekaClassic\View\Helper\Upgrade;
use Zend\View\Exception\InvalidArgumentException;
use Zend\Mvc\Controller\Plugin\Params;

class UpgradeTest extends TestCase
{

    protected $upgrade;

    protected $application;
    protected $services;
    protected $api;
    protected $view;

    protected $item;
    protected $item2;
    protected $itemSet;
    protected $media;

    public function setUp()
    {
        parent::setup();

        $application = Bootstrap::getApplication();
        $services = $application->getServiceManager();

        $this->application = $application;
        $this->services = $services;
        $this->api = $services->get('Omeka\ApiManager');

        $this->loginAsAdmin();

        // Create two items and an item set.
        $response = $this->api->create('items');
        $this->item = $response->getContent();
        $response = $this->api->create('items');
        $this->item2 = $response->getContent();
        $response = $this->api->create('item_sets');
        $this->itemSet = $response->getContent();

        // Process tests as a anonymous user.
        $this->logout();

        $paramsRoute = [
            'site-slug' => 'current-site',
        ];
        $paramsQuery = [
            'sort_by' => 'id',
            'sort_order' => 'asc',
        ];
        $params = new Params($paramsRoute);
        $params = $this->getMock(
            'Omeka\View\Helper\Params',
            ['fromRoute', 'fromQuery'],
            [$params]
        );
        $params->expects($this->any())
            ->method('fromRoute')
            ->with('site-slug')
            ->willReturn('current-site-foo');
        $params->expects($this->any())
            ->method('fromQuery')
            ->willReturn($paramsQuery);

        $vars = (object) [
            'item' => $this->item,
            'itemSet' => $this->itemSet,
            'items' => [
                $this->item,
                $this->item2,
            ],
            'itemSets' => [
                $this->itemSet,
            ]
        ];

        $view = $this->getMock('Zend\View\Renderer\PhpRenderer', [
            'api',
            'assetUrl',
            'basePath',
            'escapeHtml',
            'escapeJs',
            'getHelperPluginManager',
            'params',
            'searchFilters',
            'serverUrl',
            'setting',
            'themeSettingAssetUrl',
            'translate',
            'userIsAllowed',
            'vars'
        ]
        // 'Zend\\View\\Helper\\ViewModel',
        );
        $view->expects($this->any())
            ->method('api')
            ->willReturn($this->api);
        $view->expects($this->any())
            ->method('assetUrl')
            ->willReturnArgument(0);
        $view->expects($this->any())
            ->method('basePath')
            ->willReturn('');
        $view->expects($this->any())
            ->method('escapeHtml')
            ->willReturnArgument(0);
        $view->expects($this->any())
            ->method('escapeJs')
            ->willReturnArgument(0);
        $view->expects($this->any())
            ->method('getHelperPluginManager')
            ->willReturn($this->services);
        $view->expects($this->any())
            ->method('params')
            ->willReturn($params);
        $view->expects($this->any())
            ->method('searchFilters')
            ->willReturn(['foo']);
        $view->expects($this->any())
            ->method('serverUrl')
            ->willReturn('http://example.com');
        $view->expects($this->any())
            ->method('setting')
            ->with('foo')
            ->willReturn('bar');
        $view->expects($this->any())
            ->method('translate')
            ->willReturnArgument(0);
        $view->expects($this->any())
            ->method('themeSettingAssetUrl')
            ->willReturn('asset/img/asset.jpg');
        $view->expects($this->any())
            ->method('userIsAllowed')
            ->willReturn('user_is_allowed');
        $view->expects($this->any())
            ->method('vars')
            ->willReturn($vars);
        // $view->expects($this->any())
        // ->method('Zend\\View\\Helper\\ViewModel')
        // ->willReturn(new ViewModel());

        $this->view = $view;

        $this->upgrade = new Upgrade($this->services);
        $this->upgrade->setView($view);
    }

    public function tearDown()
    {
        $this->loginAsAdmin();
        $this->api->delete('items', $this->item->id());
        $this->api->delete('items', $this->item2->id());
        $this->api->delete('item_sets', $this->itemSet->id());
        $this->resetApplication();
    }

    protected function login($email, $password)
    {
        $services = $this->application->getServiceManager();
        $auth = $services->get('Omeka\AuthenticationService');
        $adapter = $auth->getAdapter();
        $adapter->setIdentity($email);
        $adapter->setCredential($password);
        return $auth->authenticate();
    }

    protected function loginAsAdmin()
    {
        $this->login('admin@example.com', 'root');
    }


    protected function logout()
    {
        $auth = $this->services->get('Omeka\AuthenticationService');
        $auth->clearIdentity();
    }

    protected function resetApplication()
    {
        $this->application = null;
    }

    public function testCurrentSite()
    {
        $this->assertNull($this->upgrade->currentSite());
        $this->markTestIncomplete('TODO Check a true site.');
    }

    public function testGetOption()
    {
        $this->assertEquals('bar', $this->upgrade->get_option('foo'));
    }

    public function testSetOption()
    {
        // Not upgraded.
    }

    public function testDeleteOption()
    {
        // Not upgraded.
    }

    public function testPluck()
    {
        // Test useless.
    }

    public function testCurrentUser()
    {
        $view = $this->getMock('Zend\View\Renderer\PhpRenderer', ['identity']);
        $view->expects($this->once())
            ->method('identity')
            ->willReturn('foo');
        $this->upgrade->setView($view);
        $this->assertEquals('foo', $this->upgrade->current_user());
    }

    public function testGetDb()
    {
        $this->assertInstanceOf('Doctrine\DBAL\Connection', $this->upgrade->get_db());
    }

    public function testDebug()
    {
        $this->markTestIncomplete('TODO Set logger.');
        $logger = $this->getMock('Omeka\Logger', ['log']);
        $logger->expects($this->once())
            ->method('log')
            ->willReturnArgument(1);
        $view = $this->getMock('Zend\View\Renderer\PhpRenderer', ['logger']);
        $view->expects($this->once())
            ->method('logger')
            ->willReturn($logger);
        $this->upgrade->setView($view);
        $this->upgrade->debug('foo');
    }

    public function testLog()
    {
        $this->markTestIncomplete('TODO Set logger.');
        $logger = $this->getMock('Omeka\Logger', ['log']);
        $logger->expects($this->once())
            ->method('log')
            ->willReturnArgument(1);
        $view = $this->getMock('Zend\View\Renderer\PhpRenderer', ['logger']);
        $view->expects($this->once())
            ->method('logger')
            ->willReturn($logger);
        $this->upgrade->setView($view);
        $this->upgrade->_log('foo');
    }

    public function testAddPluginHook()
    {
        // Not upgraded.
    }

    public function testFirePluginHook()
    {
        $this->markTestIncomplete('TODO Set theme.');
        $view = $this->getMock('Zend\View\Renderer\PhpRenderer', ['partial']);
        $view->expects($this->once())
            ->method('partial')
            ->willReturn('partial');
        $this->upgrade->setView($view);
        $this->assertEquals('partial', $this->upgrade->fire_plugin_hook('foo'));
    }

    public function testGetPluginHookOutput()
    {
        // Not upgraded.
    }

    public function testGetSpecificPluginHookOutput()
    {
        // Not upgraded.
    }

    public function testGetPluginBroker()
    {
        // Not upgraded.
    }

    public function testGetPluginIni()
    {
        // Not upgraded.
    }

    public function testAddFileDisplayCallback()
    {
    }

    public function testAddFileFallbackImage()
    {
    }

    public function testApplyFilters()
    {
        $this->assertEquals('bar', $this->upgrade->apply_filters('foo', 'bar'));
    }

    public function testAddFilter()
    {
        // Not upgraded.
    }

    public function testClearFilters()
    {
        // Not upgraded.
    }

    public function testGetAcl()
    {
        $this->assertInstanceOf('Omeka\Permissions\Acl', $this->upgrade->get_acl());
    }

    public function testIsAdminTheme()
    {
        $this->assertFalse($this->upgrade->is_admin_theme());
    }

    public function testGetSearchRecordTypes()
    {
        $this->assertTrue(is_array($this->upgrade->get_search_record_types()));
    }

    public function testGetCustomSearchRecordTypes()
    {
        // $this->assertTrue(is_array($this->upgrade->get_custom_search_record_types()));
    }

    public function testGetSearchQueryTypes()
    {
        $this->assertTrue(is_array($this->upgrade->get_search_query_types()));
    }

    public function testInsertItem()
    {
        // Not upgraded.
    }

    public function testInsertFilesForItem()
    {
        // Not upgraded.
    }

    public function testUpdateItem()
    {
        // Not upgraded.
    }

    public function testUpdateCollection()
    {
        // Not upgraded.
    }

    public function testInsertItemType()
    {
        // Not upgraded.
    }

    public function testInsertCollection()
    {
        // Not upgraded.
    }

    public function testInsertElementSet()
    {
        // Not upgraded.
    }

    public function testReleaseObject()
    {
        // Not upgraded.
    }

    public function testGetThemeOption()
    {
        $view = $this->getMock('Zend\View\Renderer\PhpRenderer', ['themeSetting']);
        $view->expects($this->once())
            ->method('themeSetting')
            ->with('foo_home_header')
            ->willReturn('bar');
        $this->upgrade->setView($view);

        $this->assertEquals('bar', $this->upgrade->get_theme_option('Foo Home Header'));
    }

    public function testSetThemeOption()
    {
        // Not upgraded.
    }

    public function testGetUserRoles()
    {
        // Not upgraded.
    }

    public function testElementExists()
    {
        $this->assertTrue($this->upgrade->element_exists('Dublin Core', 'Title'));
        $this->assertFalse($this->upgrade->element_exists('Foo', 'Bar'));
    }

    public function testPluginIsActive()
    {
        $this->assertTrue($this->upgrade->plugin_is_active('UpgradeFromOmekaClassic'));
        $this->assertFalse($this->upgrade->plugin_is_active('Foo'));
    }

    public function test__()
    {
        $this->assertEquals('foo 1', $this->upgrade->__('foo %d', 1));
    }

    public function testStranslate()
    {
        $this->assertEquals('foo 1', $this->upgrade->stranslate('foo %d', 1));
    }

    public function testPlural()
    {
        // Test useless.
    }

    public function testAddTranslationSource()
    {
        // Not upgraded.
    }

    public function testGetHtmlLang()
    {
        // $this->assertEquals('en-US', $this->upgrade->get_html_lang());
    }

    public function testFormatDate()
    {
        // Test useless.
    }

    public function testQueueJsFile()
    {
        // Test useless.
    }

    public function testQueueJsUrl()
    {
        // Test useless.
    }

    public function testQueueJsString()
    {
        // Test useless.
    }

    public function testQueueCssFile()
    {
        // Test useless.
    }

    public function testQueueCssUrl()
    {
        // Test useless.
    }

    public function testQueueCssString()
    {
        // Test useless.
    }

    public function testHeadJs()
    {
        // Test useless.
    }

    public function testHeadCss()
    {
        // Test useless.
    }

    public function testCssSrc()
    {
        $this->assertEquals('css' . DIRECTORY_SEPARATOR . 'style.css', $this->upgrade->css_src('style'));
    }

    public function testImg()
    {
        $this->assertEquals('img' . DIRECTORY_SEPARATOR . 'theme.jpg', $this->upgrade->img('theme.jpg'));
    }

    public function testJsTag()
    {
        $this->assertEquals('<script type="text/javascript" src="js/global.js" charset="utf-8"></script>', $this->upgrade->js_tag('global'));
    }

    public function testSrc()
    {
        // $this->expectException(InvalidArgumentException::class);
        $this->setExpectedException(InvalidArgumentException::class);
        $this->upgrade->src('foo', 'bar', 'ext');
    }

    public function testPhysicalPathTo()
    {
        $basepath = OMEKA_PATH
        . DIRECTORY_SEPARATOR . 'application'
        . DIRECTORY_SEPARATOR . 'asset'
        . DIRECTORY_SEPARATOR;
        $this->assertEquals($basepath . 'css'. DIRECTORY_SEPARATOR . 'style.css', $this->upgrade->physical_path_to('css/style.css'));
    }

    public function testWebPathTo()
    {
        $this->assertEquals('css/style.css', $this->upgrade->web_path_to('css/style.css'));
    }

    public function testRandomFeaturedCollection()
    {
        $view = $this->getMock('Zend\View\Renderer\PhpRenderer', [
            'getHelperPluginManager',
            'api',
            'partial',
        ]);
        $view->expects($this->any())
            ->method('getHelperPluginManager')
            ->willReturn($this->services);
        $view->expects($this->any())
            ->method('api')
            ->willReturn($this->api);
        $view->expects($this->once())
            ->method('partial')
            ->willReturn($this->itemSet->id());
        $this->upgrade->setView($view);
        $this->assertEquals($this->itemSet->id(), $this->upgrade->random_featured_collection());
    }

    public function testGetCollectionForItem()
    {
        $this->assertFalse($this->upgrade->get_collection_for_item($this->item));
    }

    public function testGetRecentCollections()
    {
        $this->markTestIncomplete('TODO Set view model.');
        $this->assertEquals(1, $this->upgrade->get_recent_collections());
    }

    public function testGetRandomFeaturedCollection()
    {
        $itemSet = $this->upgrade->get_random_featured_collection();
        $this->assertInstanceOf('Omeka\Api\Representation\ItemSetRepresentation', $itemSet);
        $this->assertEquals($this->itemSet->id(), $itemSet->id());
    }

    public function testLatestOmekaVersion()
    {
        // Not upgraded.
    }

    public function testMaxFileSize()
    {
        $view = $this->getMock('Zend\View\Renderer\PhpRenderer', [
            'uploadLimit',
        ]);
        $view->expects($this->once())
            ->method('uploadLimit')
            ->willReturn(128);
        $this->upgrade->setView($view);

        $this->assertEquals(128, $this->upgrade->max_file_size());
    }

    public function testFileMarkup()
    {
        $this->markTestIncomplete('TODO File markup.');
    }

    public function testFileId3Metadata()
    {
        // Not upgraded.
    }

    public function testGetRecentFiles()
    {
        $this->markTestIncomplete('TODO Set view model.');
        $this->assertEquals(1, $this->upgrade->get_recent_files());
    }

    public function testTagAttributes()
    {
        $this->assertEquals('foo="bar"', $this->upgrade->tag_attributes(['foo' => 'bar']));
    }

    public function testSearchForm()
    {
        $view = $this->getMock('Zend\View\Renderer\PhpRenderer', [
            'partial',
        ]);
        $view->expects($this->once())
            ->method('partial')
            ->willReturn('foo');
        $this->upgrade->setView($view);
        $this->assertEquals('foo', $this->upgrade->search_form());
    }

    public function testSearchFilters()
    {
        $this->assertEquals(['foo'], $this->upgrade->search_filters());
    }

    public function testElementForm()
    {
        // Not upgraded.
    }

    public function testElementSetForm()
    {
        // Not upgraded.
    }

    public function testLabelTableOptions()
    {
        $this->assertEquals(['' => 'foo'], $this->upgrade->label_table_options([], 'foo'));
    }

    public function testGetTableOptions()
    {
        // Not upgraded.
    }

    public function testGetView()
    {
        $this->assertInstanceOf(\Zend\View\Renderer\PhpRenderer::class, $this->upgrade->get_view());
    }

    public function testAutoDiscoveryLinkTags()
    {
        // Not upgraded.
    }

    public function testCommon()
    {
        $this->markTestIncomplete('TODO Set theme.');
        $view = $this->getMock('Zend\View\Renderer\PhpRenderer', ['partial']);
        $view->expects($this->once())
            ->method('partial')
            ->willReturn('partial');
        $this->upgrade->setView($view);
        $this->assertEquals('partial', $this->upgrade->common('foo'));
    }

    public function testHead()
    {
        $this->markTestIncomplete('TODO Set theme.');
        $view = $this->getMock('Zend\View\Renderer\PhpRenderer', ['partial']);
        $view->expects($this->once())
            ->method('partial')
            ->willReturn('partial');
        $this->upgrade->setView($view);
        $this->assertEquals('partial', $this->upgrade->head());
    }

    public function testFoot()
    {
        $this->markTestIncomplete('TODO Set theme.');
        $view = $this->getMock('Zend\View\Renderer\PhpRenderer', ['partial']);
        $view->expects($this->once())
            ->method('partial')
            ->willReturn('partial');
        $this->upgrade->setView($view);
        $this->assertEquals('partial', $this->upgrade->foot());
    }

    public function testFlash()
    {
        $this->markTestIncomplete('TODO Set messages.');
        $view = $this->getMock('Zend\View\Renderer\PhpRenderer', ['messages']);
        $view->expects($this->once())
            ->method('messages')
            ->willReturn('foo');
        $this->upgrade->setView($view);
        $this->assertEquals('foo', $this->upgrade->flash());
    }

    public function testOption()
    {
        $view = $this->getMock('Zend\View\Renderer\PhpRenderer', ['setting']);
        $view->expects($this->any())
        ->method('setting')
        ->with('foo')
        ->willReturn('display option bar');
        $this->upgrade->setView($view);
        $this->assertEquals('display option bar', $this->upgrade->option('foo'));
    }

    public function testGetRecords()
    {
        $records = $this->upgrade->get_records('Item', []);
        $this->assertEquals(2, count($records));
        $record = reset($records);
        $this->assertEquals($this->item->id(), $record->id());
    }

    public function testGetRecord()
    {
        $record = $this->upgrade->get_record('Item', []);
        $this->assertEquals($this->item->id(), $record->id());
    }

    public function testTotalRecords()
    {
        $this->assertEquals(2, $this->upgrade->total_records('Item'));
        $this->assertEquals(1, $this->upgrade->total_records('ItemSet'));
        $this->assertEquals(1, $this->upgrade->total_records('Collection'));
        $this->assertEquals(0, $this->upgrade->total_records('Media'));
        $this->assertEquals(0, $this->upgrade->total_records('File'));
    }

    public function testLoop()
    {
        $records = $this->upgrade->loop('items');
        $this->assertEquals(2, count($records));
        $records = $this->upgrade->loop('collections');
        $this->assertEquals(1, count($records));
        $records = $this->upgrade->loop('foo');
        $this->assertEmpty($records);
    }

    public function testSetLoopRecords()
    {
        $this->markTestIncomplete('TODO Vars assign.');
    }

    public function testGetLoopTecords()
    {
        $records = $this->upgrade->get_loop_records('items');
        $this->assertEquals(2, count($records));
        $records = $this->upgrade->get_loop_records('collections');
        $this->assertEquals(1, count($records));
        // $this->expectException(BadRequestException::class);
        $this->setExpectedException(InvalidArgumentException::class);
        $records = $this->upgrade->get_loop_records('foo');
    }

    public function testHasLoopRecords()
    {
        $this->assertTrue($this->upgrade->has_loop_records('items'));
        $this->assertTrue($this->upgrade->has_loop_records('collections'));
        $this->assertFalse($this->upgrade->has_loop_records('files'));
        $this->assertFalse($this->upgrade->has_loop_records('foo'));
    }

    public function testSetCurrentRecord()
    {
        $this->markTestIncomplete('TODO Vars assign.');
    }

    public function testGetCurrentRecord()
    {
        $this->assertEquals($this->item->id(), $this->upgrade->get_current_record('item')->id());
        $this->assertEquals($this->itemSet->id(), $this->upgrade->get_current_record('collection')->id());
        // $this->expectException(BadRequestException::class);
        $this->setExpectedException(InvalidArgumentException::class);
        $records = $this->upgrade->get_current_record('foo');
    }

    public function testGetRecordById()
    {
        $record = $this->upgrade->get_record_by_id('Item', $this->item->id());
        $this->assertEquals($this->item->id(), $record->id());
        $record = $this->upgrade->get_record_by_id('Item', 9999999);
        $this->assertNull($record);
        $record = $this->upgrade->get_record_by_id('Collection', $this->itemSet->id());
        $this->assertEquals($this->itemSet->id(), $record->id());
        $record = $this->upgrade->get_record_by_id('File', $this->item->id());
        $this->assertNull($record);
        // $this->expectException(BadRequestException::class);
        $this->setExpectedException(BadRequestException::class);
        $item = $this->upgrade->get_record_by_id('Foo', $this->item->id());
    }

    public function testGetCurrentActionContexts()
    {
        $this->assertEquals([
            'json-ld',
        ], $this->upgrade->get_current_action_contexts());
    }

    public function testOutputFormatList()
    {
        $this->markTestIncomplete('TODO Set theme.');
        $view = $this->getMock('Zend\View\Renderer\PhpRenderer', [
            'partial',
        ]);
        $view->expects($this->once())
            ->method('partial')
            ->willReturn('partial');
        $this->upgrade->setView($view);
        $this->assertEquals('partial', $this->upgrade->output_format_list());
    }

    public function testBrowseSortLinks()
    {
        $links = [
            'foo' => 'item',
        ];
        $output = $this->upgrade->browse_sort_links($links);
        $this->assertEquals('<ul id="sort-links-list"><li  ><a href="">foo</a></li></ul>', $output);
    }

    public function testBodyTag()
    {
        $this->markTestIncomplete('TODO Check a true site.');
        $this->assertEquals('partial', $this->upgrade->body_tag());
    }

    public function testItemSearchFilters()
    {
        $this->assertEquals(['foo'], $this->upgrade->item_search_filters());
    }

    public function testMetadata()
    {
        $view = $this->getMock('Zend\View\Renderer\PhpRenderer', [
            'metadata',
        ]);
        $view->expects($this->once())
            ->method('metadata')
            ->willReturn('foo');
        $this->upgrade->setView($view);
        $this->assertEquals('foo', $this->upgrade->metadata($this->item, ['Dublin Core', 'Title']));
    }

    public function testAllElementTexts()
    {
        $view = $this->getMock('Zend\View\Renderer\PhpRenderer', [
            'allElementTexts',
        ]);
        $view->expects($this->once())
            ->method('allElementTexts')
            ->willReturn('foo');
        $this->upgrade->setView($view);
        $this->assertEquals('foo', $this->upgrade->all_element_texts($this->item));
    }

    public function testFilesForItem()
    {
    }

    public function testGetNextItem()
    {
        $record = $this->upgrade->get_next_item($this->item);
        $this->assertEquals($this->item2->id(), $record->id());
        $record = $this->upgrade->get_next_item($this->item2);
        $this->assertNull($record);
    }

    public function testGetPreviousItem()
    {
        $record = $this->upgrade->get_previous_item($this->item2);
        $this->assertEquals($this->item->id(), $record->id());
//        $this->markTestSkipped('TODO Check error.');
        $record = $this->upgrade->get_previous_item($this->item);
        $this->assertNull($record);
    }

    public function testRecordImage()
    {
        $this->markTestIncomplete('TODO Check file markup.');
    }

    public function testItemImage()
    {
        $this->markTestIncomplete('TODO Check file markup.');
    }

    public function testFileImage()
    {
        $this->markTestIncomplete('TODO Check file markup.');
    }

    public function testItemImageGallery()
    {
        $this->markTestIncomplete('TODO Check file markup.');
    }

    public function testItemsSearchForm()
    {
        $this->markTestIncomplete('TODO Set theme.');
        $view = $this->getMock('Zend\View\Renderer\PhpRenderer', ['partial']);
        $view->expects($this->once())
            ->method('partial')
            ->willReturn('partial');
        $this->upgrade->setView($view);
        $this->assertEquals('partial', $this->upgrade->items_search_form());
    }

    public function testGetRecentItems()
    {
        $records = $this->upgrade->get_recent_items();
        $this->assertEquals(2, count($records));
    }

    public function testGetRandomFeaturedItems()
    {
        $this->markTestIncomplete('TODO Get random featured items.');
        $records = $this->upgrade->get_random_featured_items();
        $this->assertEquals(2, count($records));
    }

    public function testRecentItems()
    {
        $records = $this->upgrade->get_recent_items();
        $this->assertEquals(2, count($records));
    }

    public function testRandomFeaturedItems()
    {
        $this->markTestIncomplete('TODO Get random featured items.');
        $this->assertEquals('', $this->upgrade->random_featured_items());
    }

    public function testItemTypeElements()
    {
        // Not upgraded.
    }

    public function testLinkTo()
    {
        $this->markTestIncomplete('TODO Get record url.');
        $this->assertEquals('', $this->upgrade->link_to($this->item));
    }

    public function testLinkToItemSearch()
    {
        $this->markTestIncomplete('TODO Get record url.');
        $this->assertEquals('', $this->upgrade->link_to_item_search());
    }

    public function testLinkToItemsBrowse()
    {
        $this->markTestIncomplete('TODO Get record url.');
        $this->assertEquals('', $this->upgrade->link_to_items_browse('Browse'));
    }

    public function testLinkToCollectionForItem()
    {
        $this->markTestIncomplete('TODO Get record url.');
        $this->assertEquals('', $this->upgrade->link_to_collection_for_item(null, [], 'show'));
    }

    public function testLinkToItemsInCollection()
    {
        $this->markTestIncomplete('TODO Get record url.');
        $this->assertEquals('', $this->upgrade->link_to_items_in_collection(null, [], 'browse', $this->itemSet));
    }

    public function testLinkToItemsWithItemType()
    {
        $this->markTestIncomplete('TODO Get record url.');
        $itemType = null;
        $this->assertEquals('', $this->upgrade->link_to_items_with_item_type(null, [], 'browse', $itemType));
    }

    public function testLinkToFileShow()
    {
        $this->markTestIncomplete('TODO Get record url.');
        $this->assertEquals('', $this->upgrade->link_to_file_show([], null, $this->media));
    }

    public function testLinkToItem()
    {
        $this->markTestIncomplete('TODO Get record url.');
        $this->assertEquals('', $this->upgrade->link_to_item(null, [], 'show', $this->item));
    }

    public function testLinkToItemsRss()
    {
        $this->markTestIncomplete('TODO Get record url.');
        $this->assertEquals('', $this->upgrade->link_to_items_rss());
    }

    public function testLinkToNextItemShow()
    {
        $this->markTestIncomplete('TODO Get record url.');
        $this->assertEquals('', $this->upgrade->link_to_next_item_show());
    }

    public function testLinkToPreviousItemShow()
    {
        $this->markTestIncomplete('TODO Get record url.');
        $this->assertEquals('', $this->upgrade->link_to_previous_item_show());
    }

    public function testLinkToCollection()
    {
        $this->markTestIncomplete('TODO Get record url.');
        $this->assertEquals('', $this->upgrade->link_to_collection(null, [], 'show', $this->itemSet));
    }

    public function testLinkToHomePage()
    {
        $link = $this->upgrade->link_to_home_page();
        $this->assertEquals('<a href="http://example.com" ></a>' . PHP_EOL, $link);
    }

    public function testLinkToAdminHomePage()
    {
        $link = $this->upgrade->link_to_admin_home_page();
        $this->assertEquals('<a href="http://example.com/admin" ></a>' . PHP_EOL, $link);
    }

    public function testNav()
    {
        $this->markTestIncomplete('TODO Nav.');
        $nav = $this->upgrade->nav(['foo linnk' => 'http://bar']);
        $this->assertEquals('<a href="http://example.com/admin" ></a>' . PHP_EOL, $nav);
    }

    public function testPaginationLinks()
    {
        $this->markTestIncomplete('TODO Nav.');
        $this->assertEquals('', $this->upgrade->pagination_links());
    }

    public function testPublicNavMain()
    {
        $this->markTestIncomplete('TODO Nav.');
        $this->assertEquals('', $this->upgrade->public_nav_main());
    }

    public function testPublicNavItems()
    {
        $this->markTestIncomplete('TODO Nav.');
        $this->assertEquals('', $this->upgrade->public_nav_items());
    }

    public function testHtmlEscape()
    {
        $this->assertEquals('foo "bar"', $this->upgrade->html_escape('foo "bar"'));
    }

    public function testJsEscape()
    {
        $this->assertEquals('foo "bar"', $this->upgrade->js_escape('foo "bar"'));
    }

    public function testXmlEscape()
    {
        $this->assertEquals('foo &quot;bar&quot;', $this->upgrade->xml_escape('foo "bar"'));
    }

    public function testTextToParagraphs()
    {
        // Test useless.
    }

    public function testSnippet()
    {
        // Test useless.
    }

    public function testSnippetByWordCount()
    {
        // Test useless.
    }

    public function testStripFormatting()
    {
        // Test useless.
    }

    public function testTextToId()
    {
        // Test useless.
    }

    public function testUrlToLink()
    {
        // Test useless.
    }

    public function testUrlToLinkCallback()
    {
        // Test useless.
    }

    public function testGetRecentTags()
    {
        $records = $this->upgrade->get_recent_tags();
        $this->assertEquals(0, count($records));
    }

    public function testTagCloud()
    {
        $string = $this->upgrade->tag_cloud();
        $this->assertEquals('<p>No tags are available.</p>', $string);
    }

    public function testTagString()
    {
        $string = $this->upgrade->tag_string();
        $this->assertEquals('', $string);
    }

    public function testUrl()
    {
        $string = $this->upgrade->url('foo-option');
        $this->assertEquals('/admin/foo-option', $string);
    }

    public function testAbsoluteUrl()
    {
        $string = $this->upgrade->absolute_url('foo-option');
        $this->assertEquals('http://example.com/admin/foo-option', $string);
    }

    public function testCurrentUrl()
    {
        $this->markTestIncomplete('TODO Get url.');
        $this->assertEquals('/', $this->upgrade->current_url());
    }

    public function testIsCurrentUrl()
    {
        $this->markTestIncomplete('TODO Get url.');
        $url = $this->upgrade->is_current_url('/');
        $this->assertEquals('/', $url);
    }

    public function testRecordUrl()
    {
        $this->markTestIncomplete('TODO Get url.');
        $this->assertEquals('/', $this->upgrade->record_url($this->item));
    }

    public function testItemsOutputUrl()
    {
        $this->markTestIncomplete('TODO Get url.');
        $this->assertEquals('/', $this->upgrade->items_output_url('output'));
    }

    public function testFileDisplayUrl()
    {
        $this->markTestIncomplete('TODO Get url.');
        $this->assertEquals('/', $this->upgrade->file_display_url($this->media));
    }

    public function testPublicUrl()
    {
        $this->markTestIncomplete('TODO Get url.');
        $this->assertEquals('/', $this->upgrade->public_url('/'));
    }

    public function testAdminUrl()
    {
        $this->markTestIncomplete('TODO Get url.');
        $this->assertEquals('/', $this->upgrade->admin_url('/'));
    }

    public function testSetThemeBaseUrl()
    {
        // Not upgraded.
    }

    public function testRevertThemeBaseUrl()
    {
        // Not upgraded.
    }

    public function testThemeLogo()
    {
        $this->assertEquals('<img src="asset/img/asset.jpg" alt="" />', $this->upgrade->theme_logo());
    }

    public function testThemeHeaderImage()
    {
        $this->assertEquals('<div id="header-image"><img src="asset/img/asset.jpg" /></div>', $this->upgrade->theme_header_image());
    }

    public function testThemeHeaderBackground()
    {
        $exp = '<style type="text/css" media="screen">header {background:transparent url("asset/img/asset.jpg") center left no-repeat;}</style>';
        $this->assertEquals($exp, $this->upgrade->theme_header_background());
    }

    public function testIsAllowed()
    {
        $this->assertEquals('user_is_allowed', $this->upgrade->is_allowed('Item', 'delete'));
    }

    public function testAddShortcode()
    {
        // Not upgraded.
    }

    public function testGetCitation()
    {
        $this->markTestIncomplete('TODO Get url.');
        $this->assertEquals('', $this->upgrade->getCitation($this->item));
    }
}
