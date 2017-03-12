<?php

namespace UpgradeFromOmekaClassicTest\View\Helper;

use Omeka\Test\TestCase;
use UpgradeFromOmekaClassic\View\Helper\Upgrade;
use OmekaTestHelper\Bootstrap;

class UpgradeTest extends TestCase
{
    protected $upgrade;

    protected $view;
    protected $api;

    protected $item;
    protected $itemSet;

    public function setUp()
    {
        $services = Bootstrap::getApplication()->getServiceManager();
        $this->api = $services->get('Omeka\ApiManager');

        $response = $this->api->create('items');
        $this->item = $response->getContent();

        $response = $this->api->create('item_sets');
        $this->itemSet = $response->getContent();

        $view = $this->getMock('Zend\View\Renderer\PhpRenderer', [
            'api',
            'setting',
            'upgrade',
        ]);

        $this->view = $view;

        $this->upgrade = $view->upgrade();
        $this->upgrade = new Upgrade();
        $this->upgrade->setView($view);
    }

    public function tearDown()
    {
        $this->api->delete('items', $this->item->id());
        $this->api->delete('item_sets', $this->itemSet->id());
    }

    public function testGetOption()
    {
        $services = Bootstrap::getApplication()->getServiceManager();
        $settings = $services->get('Omeka\Settings');

        $settings->set('foo', 'bar');
        $this->assertEquals('bar', $settings->get('foo'));
        $this->markTestIncomplete('TODO Use mocks and stubs.');
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
    }

    public function testCurrentUser()
    {
    }

    public function testGetDb()
    {
    }

    public function testDebug()
    {
    }

    public function testLog()
    {
    }

    public function testAddPluginHook()
    {
        // Not upgraded.
    }

    public function testFirePluginHook()
    {
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
    }

    public function testIsAdminTheme()
    {
        $this->assertFalse($this->upgrade->is_admin_theme());
    }

    public function testGetSearchRecordTypes()
    {
    }

    public function testGetCustomSearchRecordTypes()
    {
    }

    public function testGetSearchQueryTypes()
    {
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
        // Not upgraded.
    }

    public function testPluginIsActive()
    {
    }

    public function test__()
    {
    }

    public function testStranslate()
    {
    }

    public function testPlural()
    {
    }

    public function testAddTranslationSource()
    {
        // Not upgraded.
    }

    public function testGetHtmlLang()
    {
    }

    public function testFormatDate()
    {
    }

    public function testQueueJsFile()
    {
    }

    public function testQueueJsUrl()
    {
    }

    public function testQueueJsString()
    {
    }

    public function testQueueCssFile()
    {
    }

    public function testQueueCssUrl()
    {
    }

    public function testQueueCssString()
    {
    }

    public function testHeadJs()
    {
    }

    public function testHeadCss()
    {
    }

    public function testCssSrc()
    {
    }

    public function testImg()
    {
    }

    public function testJsTag()
    {
    }

    public function testSrc()
    {
    }

    public function testPhysicalPathTo()
    {
    }

    public function testWebPathTo()
    {
    }

    public function testRandomFeaturedCollection()
    {
    }

    public function testGetCollectionForItem()
    {
    }

    public function testGetRecentCollections()
    {
    }

    public function testGetRandomFeaturedCollection()
    {
    }

    public function testLatestOmekaVersion()
    {
        // Not upgraded.
    }

    public function testMaxFileSize()
    {
    }

    public function testFileMarkup()
    {
    }

    public function testFileId3Metadata()
    {
    }

    public function testGetRecentFiles()
    {
    }

    public function testTagAttributes()
    {
    }

    public function testSearchForm()
    {
    }

    public function testSearchFilters()
    {
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
    }

    public function testGetTableOptions()
    {
        // Not upgraded.
    }

    public function testGetView()
    {
    }

    public function testAutoDiscoveryLinkTags()
    {
        // Not upgraded.
    }

    public function testCommon()
    {
    }

    public function testHead()
    {
    }

    public function testFoot()
    {
    }

    public function testFlash()
    {
    }

    public function testOption()
    {
    }

    public function testGetRecords()
    {
    }

    public function testGetRecord()
    {
    }

    public function testTotalRecords()
    {
    }

    public function testLoop()
    {
    }

    public function testSetLoopRecords()
    {
    }

    public function testGetLoopTecords()
    {
    }

    public function testHasLoopRecords()
    {
    }

    public function testSetCurrentRecord()
    {
    }

    public function testGetCurrentRecord()
    {
    }

    public function testGetRecordById()
    {
        $this->markTestIncomplete('TODO Use mocks and stubs.');

        $item = $this->upgrade->get_record_by_id('Item', $this->item->id());
        $this->assertEquals($this->item->id(), $item->id());

        $item = $this->upgrade->get_record_by_id('Item', 9999999);
        $this->assertNull($item);

        $item = $this->upgrade->get_record_by_id('Foo', $item->id());
        $this->assertNull($item);

        $itemSet = $this->upgrade->get_record_by_id('Collection', $itemSet->id());
        $this->assertEquals($this->itemSet->id(), $itemSet->id());

        $itemSet = $this->upgrade->get_record_by_id('ItemSet', $itemSet->id());
        $this->assertEquals($this->itemSet->id(), $itemSet->id());
    }

    public function testGetCurrentActionContexts()
    {
    }

    public function testOutputFormatList()
    {
    }

    public function testBrowseSortLinks()
    {
    }

    public function testBodyTag()
    {
    }

    public function testItemSearchFilters()
    {
    }

    public function testMetadata()
    {
    }

    public function testAllElementTexts()
    {
    }

    public function testFilesForItem()
    {
    }

    public function testGetNextItem()
    {
    }

    public function testGetPreviousItem()
    {
    }

    public function testRecordImage()
    {
    }

    public function testItemImage()
    {
    }

    public function testFileImage()
    {
    }

    public function testItemImageGallery()
    {
    }

    public function testItemsSearchForm()
    {
    }

    public function testGetRecentItems()
    {
    }

    public function testGetRandomFeaturedItems()
    {
    }

    public function testRecentItems()
    {
    }

    public function testRandomFeaturedItems()
    {
    }

    public function testItemTypeElements()
    {
        // Not upgraded.
    }

    public function testLinkTo()
    {
    }

    public function testLinkToItemSearch()
    {
    }

    public function testLinkToItemsBrowse()
    {
    }

    public function testLinkToCollectionForItem()
    {
    }

    public function testLinkToItemsInCollection()
    {
    }

    public function testLinkToItemsWithItemType()
    {
    }

    public function testLinkToFileShow()
    {
    }

    public function testLinkToItem()
    {
    }

    public function testLinkToItemsRss()
    {
    }

    public function testLinkToNextItemShow()
    {
    }

    public function testLinkToPreviousItemShow()
    {
    }

    public function testLinkToCollection()
    {
    }

    public function testLinkToHomePage()
    {
    }

    public function testLinkToAdminHomePage()
    {
    }

    public function testNav()
    {
    }

    public function testPaginationLinks()
    {
    }

    public function testPublicNavMain()
    {
    }

    public function testPublicNavItems()
    {
    }

    public function testHtmlEscape()
    {
    }

    public function testJsEscape()
    {
    }

    public function testXmlEscape()
    {
    }

    public function testTextToParagraphs()
    {
    }

    public function testSnippet()
    {
    }

    public function testSnippetByWordCount()
    {
    }

    public function testStripFormatting()
    {
    }

    public function testTextToId()
    {
    }

    public function testUrlToLink()
    {
    }

    public function testUrlToLinkCallback()
    {
    }

    public function testGetRecentTags()
    {
    }

    public function testTagCloud()
    {
    }

    public function testTagString()
    {
    }

    public function testUrl()
    {
    }

    public function testAbsoluteUrl()
    {
    }

    public function testCurrentUrl()
    {
    }

    public function testIsCurrentUrl()
    {
    }

    public function testRecordUrl()
    {
    }

    public function testItemsOutputUrl()
    {
    }

    public function testFileDisplayUrl()
    {
    }

    public function testPublicUrl()
    {
    }

    public function testAdminUrl()
    {
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
    }

    public function testThemeHeaderImage()
    {
    }

    public function testThemeHeaderBackground()
    {
    }

    public function testIsAllowed()
    {
    }

    public function testAddShortcode()
    {
        // Not upgraded.
    }

    public function testGetCitation()
    {
    }
}
