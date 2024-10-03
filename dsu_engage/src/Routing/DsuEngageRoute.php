<?php

namespace Drupal\dsu_engage\Routing;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\dsu_engage\ConstantsInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;

/**
 * Defines a route subscriber to generate dynamic route for engage form.
 */
class DsuEngageRoute implements ContainerInjectionInterface {

  /**
   * The dsu_engage.settings config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected ImmutableConfig $config;

  /**
   * Notice constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory to read configuration items.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->config = $config_factory->get('dsu_engage.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): DsuEngageRoute {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * Returns an array of route objects.
   *
   * @return \Symfony\Component\Routing\Route[]
   *   An array of route objects.
   */
  public function routes(): array {
    $routes = [];

    if ($this->config->get(ConstantsInterface::SETTING_ROUTE_ENABLED)) {
      $routes['engage_page'] = new Route(
        $this->config->get(ConstantsInterface::SETTING_ROUTE),
        [
          '_title' => 'Contact us',
          '_form' => '\Drupal\dsu_engage\Form\DsuEngageForm',
        ],
        [
          '_permission' => 'access content',
        ],
      );
    }

    return $routes;
  }

}
