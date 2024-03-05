<?php

namespace Drupal\Tests\menu_child_item\Functional;

use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;

/**
 * Simple test to ensure that main page loads with module enabled.
 *
 * @group menu_child_item
 */
class LoadTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['menu_child_item'];

  /**
   * A user with permission to administer site configuration.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
      parent::setUp();
      $this->adminUser = $this->drupalCreateUser(['administer menu', 'link to any page']);
      $this->basicUser = $this->drupalCreateUser(['administer menu']);
      $this->authUser = $this->drupalCreateUser([]);
      $this->drupalLogin($this->adminUser);
  }

  /**
   * Tests that the home page loads with a 200 response.
   */
  public function testLoad() {
    $this->drupalGet(Url::fromRoute('<front>'));
    $this->assertSession()->statusCodeEquals(200);
  }

    /**
     * Tests for adding Child Menu with various users .
     */
    public function testAddingChildMenu() {
        $menu_link = MenuLinkContent::create([
            'title' => 'Menu link test',
            'provider' => 'menu_link_content',
            'menu_name' => 'admin',
            'link' => ['uri' => 'internal:/user/login'],
        ]);
        $menu_link->save();

        $this->drupalGet('/admin/structure/menu/item/' . $menu_link->id() . '/add-child');
        $this->assertSession()->statusCodeEquals(200);

        $this->drupalLogin($this->basicUser);

        $this->drupalGet('/admin/structure/menu/item/' . $menu_link->id() . '/add-child');
        $this->assertSession()->statusCodeEquals(200);

        $this->drupalLogin($this->authUser);

        $this->drupalGet('/admin/structure/menu/item/' . $menu_link->id() . '/add-child');
        $this->assertSession()->statusCodeEquals(403);
    }

}
