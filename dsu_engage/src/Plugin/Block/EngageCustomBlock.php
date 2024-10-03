<?php

namespace Drupal\dsu_engage\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\dsu_engage\Form\DsuEngageForm;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block for engage form.
 *
 * @Block(
 *   id = "dsu_engage_block_form",
 *   admin_label = @Translation("Engage Block Form"),
 * )
 */
class EngageCustomBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Constructs an AutologoutWarningBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Form\FormBuilder $builder
   *   The FormBuilder service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected readonly FormBuilder $builder,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition,
  ): EngageCustomBlock {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return $this->builder->getForm(DsuEngageForm::class);
  }

}
