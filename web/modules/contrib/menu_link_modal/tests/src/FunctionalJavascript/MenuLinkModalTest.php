<?php

namespace Drupal\Tests\menu_link_modal\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests link added in menu opens in modal with this module.
 *
 * @group menu_link_modal
 */
class MenuLinkModalTest extends WebDriverTestBase {

  /**
   * Admin user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'block',
    'node',
    'datetime',
    'menu_link_content',
    'menu_ui',
    'menu_link_modal',
  ];

  /**
   * The installation profile to use with this test.
   *
   * We use 'minimal' because we want the main menu to be available.
   *
   * @var string
   */
  protected $profile = 'minimal';

  /**
   * The default theme.
   *
   * @var string
   */
  protected $defaultTheme = 'stark';

  /**
   * Permissions to grant admin user.
   *
   * @var array
   */
  protected $permissions = [
    'administer nodes',
    'create page content',
    'edit own page content',
    'access administration pages',
    'access content overview',
    'administer menu',
    'link to any page',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->drupalCreateContentType([
      'type' => 'page',
      'name' => 'Basic page',
      'display_submitted' => FALSE,
    ]);

    $this->adminUser = $this->drupalCreateUser($this->permissions);
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Tests if the link added in menu item opens with modal.
   */
  public function testMenuLinkModal() {
    // Create a new page node.
    $this->drupalGet('node/add/page');
    $edit = [
      'title[0][value]' => 'Test node for modal',
      'body[0][value]' => $this->randomString(),
    ];
    $this->submitForm($edit, 'Save');
    $this->assertSession()->pageTextContainsOnce(t('Basic page @title has been created.', ['@title' => $edit['title[0][value]']]));

    // Check that the node exists in the database.
    $node = $this->drupalGetNodeByTitle($edit['title[0][value]']);
    $this->assertNotEmpty($node, 'Node found in database.');

    // Create a menu item with the link of recently created node.
    $this->drupalGet('admin/structure/menu/manage/main/add');
    // Expand the details section.
    $this->click('#edit-modal-config');

    $title = 'title modal';
    $edit = [
      'link[0][uri]' => '/node/' . $node->id(),
      'title[0][value]' => $title,
      'open_modal' => 1,
    ];
    $this->submitForm($edit, 'Save');
    $this->assertSession()->pageTextContainsOnce('The menu link has been saved.');

    $menu_links = \Drupal::entityTypeManager()->getStorage('menu_link_content')->loadByProperties(['title' => $title]);
    $menu_link = reset($menu_links);
    $this->assertNotEmpty($menu_link, 'Menu link was found in database.');

    // Add the main menu block, as provided by the Block module.
    $this->placeBlock('system_menu_block:main');
    // Check if the link opens in modal while we are still login as admin.
    $this->drupalGet('');
    $this->checkModalFunctionality($node, $title);
    $this->drupalLogout();
    // Check if the link opens in modal after logout.
    $this->drupalGet('');
    $this->checkModalFunctionality($node, $title);
  }

  /**
   * Click on the menu link to check if it's opened in modal.
   *
   * @param object $node
   *   The node object.
   * @param string $title
   *   The title fo the Menu link to click.
   *
   */
  protected function checkModalFunctionality($node, $title) {
    // Get Mink stuff.
    $assert = $this->assertSession();

    $this->clickLink($title);
    $this->assertNotEmpty('css', '.ui-dialog');

    // Check that we have a result modal.
    $assert->statusMessageContains($node->label(), 'span.ui-dialog-title');
  }

}
