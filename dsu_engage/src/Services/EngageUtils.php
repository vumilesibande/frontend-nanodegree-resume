<?php

namespace Drupal\dsu_engage\Services;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\Yaml\Yaml;

/**
 * Class for engage_utils service.
 */
class EngageUtils implements EngageUtilsInterface {
  use StringTranslationTrait;

  /**
   * The dsu_engage.datasets config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected ImmutableConfig $config;

  /**
   * Constructs a new EngageUtils object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory to read configuration items.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->config = $config_factory->get('dsu_engage.datasets');
  }

  /**
   * {@inheritdoc}
   */
  public function getValues(string $dataset): array {
    return Yaml::parse($this->config->get($dataset));
  }

  /**
   * {@inheritdoc}
   */
  public function getValueFromKey(string $key, string $dataset): mixed {
    $values = $this->getValues($dataset);
    return $values[$key] ?? NULL;
  }

}
