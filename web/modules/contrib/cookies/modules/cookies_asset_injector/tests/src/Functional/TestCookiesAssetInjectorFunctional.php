<?php

namespace Drupal\Tests\cookies_asset_injector\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\cookies_asset_injector\Traits\CookiesAssetInjectorTestHelperTrait;

/**
 * This class provides methods specifically for testing something.
 *
 * @group cookies_asset_injector
 */
class TestCookiesAssetInjectorFunctional extends BrowserTestBase {
  use CookiesAssetInjectorTestHelperTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'test_page_test',
    'asset_injector',
    'cookies',
    'cookies_asset_injector',
  ];

  /**
   * A user with authenticated permissions.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $user;

  /**
   * A user with admin permissions.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->config('system.site')->set('page.front', '/test-page')->save();
    $this->user = $this->drupalCreateUser([]);
    $this->adminUser = $this->drupalCreateUser([]);
    $this->adminUser->addRole($this->createAdminRole('admin', 'admin'));
    $this->adminUser->save();
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Tests if the module installation, won't break the site.
   */
  public function testInstallation() {
    $session = $this->assertSession();
    $this->drupalGet('<front>');
    $session->statusCodeEquals(200);
  }

  /**
   * Tests if uninstalling the module, won't break the site.
   */
  public function testUninstallation() {
    // Go to uninstallation page an uninstall cookies_ga:
    $session = $this->assertSession();
    $page = $this->getSession()->getPage();
    $this->drupalGet('/admin/modules/uninstall');
    $session->statusCodeEquals(200);
    $page->checkField('edit-uninstall-cookies-asset-injector');
    $page->pressButton('edit-submit');
    $session->statusCodeEquals(200);
    // Confirm deinstall:
    $page->pressButton('edit-submit');
    $session->statusCodeEquals(200);
    $session->pageTextContains('The selected modules have been uninstalled.');
  }

  /**
   * Tests if the third party settings are being rendered.
   */
  public function testUiExists() {
    $session = $this->assertSession();
    $this->createAssetInjector('test', 'test', 'console.log("test");');
    $this->drupalGet('/admin/config/development/asset-injector/js/test');
    $session->statusCodeEquals(200);
    $session->elementExists('css', '#edit-third-party-settings-cookies-asset-injector');
    $session->elementExists('css', '#edit-third-party-settings-cookies-asset-injector-cookies-service');
    $session->elementExists('css', '#edit-third-party-settings-cookies-asset-injector-cookies-service--description');
  }

}
