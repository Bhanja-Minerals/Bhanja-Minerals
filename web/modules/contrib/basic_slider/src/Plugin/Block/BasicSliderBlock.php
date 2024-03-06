<?php

namespace Drupal\basic_slider\Plugin\Block;

use Drupal\Component\Utility\Environment;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block with slider images.
 *
 * @Block(
 *  id = "basic_slider_block",
 *  admin_label = @Translation("Basic Slider Block"),
 * )
 */
class BasicSliderBlock extends BlockBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * The storage handler class for files.
   *
   * @var \Drupal\file\FileStorage
   */
  private $fileStorage;

  /**
   * Construct an file storage object.
   *
   * @param array $configuration
   *   This is configuration.
   * @param string $plugin_id
   *   This is plugin.
   * @param mixed $plugin_definition
   *   This is plugin defination.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity
   *   Entity type manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->fileStorage = $entity->getStorage('file');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['#theme'] = 'basic_slider';
    $build['#attached']['library'][] = 'basic_slider/basic-slider';

    $images = $this->configuration['images'];
    if (count($images)) {
      foreach ($images as $image) {
        if (!empty($image)) {
          if ($file = $this->fileStorage->load($image)) {
            $build['image'][] = [
              '#theme' => 'image',
              '#uri' => $file->getFileUri(),
            ];
          }
        }
      }
    }
    $build['#images'] = $build['image'];
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $validators = [
      'FileExtension' => ['extensions' => 'gif png jpg jpeg'],
      'FileSizeLimit' => ['fileLimit' => Environment::getUploadMaxSize()],
    ];

    $form['images'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Slider Image'),
      '#upload_validators' => $validators,
      '#theme' => 'image_widget',
      '#preview_image_style' => 'medium',
      '#upload_location' => 'public://basic_slider',
      '#required' => TRUE,
      '#default_value' => $this->configuration['images'] ?? '',
      '#multiple' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    /* Fetch the array of the file stored temporarily in database */
    $images = $form_state->getValue('images');
    if (count($images)) {
      foreach ($images as $image) {
        if (!empty($image)) {
          if ($file = $this->fileStorage->load($image)) {
            /* Set the status flag permanent of the file object */
            $file->setPermanent();
            /* Save the file in database */
            $file->save();
          }
        }
      }
    }
    $this->configuration['images'] = $values['images'];
  }

  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

}
