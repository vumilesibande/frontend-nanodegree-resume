<?php

namespace Drupal\dsu_engage\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\ConfigTarget;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\RedundantEditableConfigNamesTrait;
use Drupal\Core\Render\Element\Checkboxes;
use Drupal\Core\Routing\RouteBuilderInterface;
use Drupal\dsu_engage\ConstantsInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure form page settings for dsu_engage.
 */
class DsuEngageSettingsPageForm extends ConfigFormBase {
  use RedundantEditableConfigNamesTrait;

  /**
   * Constructs a new DsuEngageSettingsPageForm.
   *
   * @param \Drupal\Core\Routing\RouteBuilderInterface $routeBuilder
   *   The route builder service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param null $typedConfigManager
   *   The typed config manager.
   */
  public function __construct(
    protected RouteBuilderInterface $routeBuilder,
    ConfigFactoryInterface $config_factory,
    protected $typedConfigManager = NULL,
  ) {
    parent::__construct($config_factory, $typedConfigManager);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): DsuEngageSettingsPageForm {
    return new static(
      $container->get('router.builder'),
      $container->get('config.factory'),
      $container->get('config.typed'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'dsu_engage_page_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form[ConstantsInterface::SETTING_ROUTE_ENABLED] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Engage form Page'),
      '#description' => $this->t('Enable the Contact Us page with settings below.'),
      '#config_target' => 'dsu_engage.settings:' . ConstantsInterface::SETTING_ROUTE_ENABLED,
    ];

    $form[ConstantsInterface::SETTING_ROUTE] = [
      '#type' => 'textfield',
      '#pattern' => '^([a-z0-9]+[-_\/])*[a-z0-9]+$',
      '#title' => $this->t('Enter Contact Us page URL'),
      '#description' => $this->t('Note: URL must be internal and unique (Ex : contact-us)'),
      '#states' => [
        'visible' => [
          ':input[name="' . ConstantsInterface::SETTING_ROUTE_ENABLED . '"]' => ['checked' => TRUE],
        ],
        'required' => [
          ':input[name="' . ConstantsInterface::SETTING_ROUTE_ENABLED . '"]' => ['checked' => TRUE],
        ],
      ],
      '#config_target' => 'dsu_engage.settings:' . ConstantsInterface::SETTING_ROUTE,
    ];

    $form[ConstantsInterface::SETTING_ENABLED_REQUEST_TYPES] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Request Type'),
      '#options' => ConstantsInterface::REQUEST_TYPE_OPTIONS,
      '#config_target' => new ConfigTarget(
        'dsu_engage.settings',
        ConstantsInterface::SETTING_ENABLED_REQUEST_TYPES,
        toConfig: fn(array $value) => Checkboxes::getCheckedCheckboxes($value),
      ),
      '#required' => TRUE,
    ];

    $headers = $subheaders = [];
    foreach (ConstantsInterface::REQUEST_TYPE_OPTIONS as $request_type_label) {
      $headers[] = [
        'data' => $request_type_label,
        'colspan' => 2,
        'class' => ['text-align-center'],
      ];

      $subheaders[] = [
        'data' => $this->t('Show'),
        'class' => ['text-align-center'],
      ];
      $subheaders[] = [
        'data' => $this->t('Mandatory'),
        'class' => ['text-align-center'],
      ];
    }
    $form['table'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Field name'),
        ...$headers,
      ],
      '#rows' => [
        [
          '',
          ...$subheaders,
        ],
      ],
      '#attributes' => [
        'class' => ['dsu_engage_fields_settings'],
      ],
      '#attached' => [
        'library' => [
          'dsu_engage/dsu_engage_form_admin',
        ],
      ],
    ];

    foreach (ConstantsInterface::FIELDS as $name => $label) {
      $form['table'][$name]['field_name'] = [
        '#markup' => $label,
      ];
      foreach (ConstantsInterface::REQUEST_TYPE_OPTIONS as $request_type => $request_type_label) {
        $form['table'][$name]["show_{$name}_{$request_type}"] = [
          '#type' => 'checkbox',
          '#title' => $this->t('Show @field in @request_type tab', [
            '@field' => $label,
            '@request_type' => $request_type_label,
          ]),
          '#title_display' => 'attribute',
          '#states' => [
            'visible' => [
              ":input[name=\"" . ConstantsInterface::SETTING_ENABLED_REQUEST_TYPES . "[{$request_type}]\"]" => ['checked' => TRUE],
            ],
          ],
          '#config_target' => "dsu_engage.settings:show_{$name}_{$request_type}",
          '#wrapper_attributes' => [
            'class' => ['text-align-center'],
          ],
        ];

        $form['table'][$name]["mandatory_{$name}_{$request_type}"] = [
          '#type' => 'checkbox',
          '#title' => $this->t('Mandatory @field in @request_type tab', [
            '@field' => $label,
            '@request_type' => $request_type_label,
          ]),
          '#title_display' => 'attribute',
          '#states' => [
            'visible' => [
              ":input[name=\"" . ConstantsInterface::SETTING_ENABLED_REQUEST_TYPES . "[{$request_type}]\"]" => ['checked' => TRUE],
              ":input[name=\"table[$name][show_{$name}_{$request_type}]\"]" => ['checked' => TRUE],
            ],
          ],
          '#config_target' => "dsu_engage.settings:mandatory_{$name}_{$request_type}",
          '#wrapper_attributes' => [
            'class' => ['text-align-center'],
          ],
        ];
      }
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $old_route = $this->config('dsu_engage.settings')->get(ConstantsInterface::SETTING_ROUTE);
    parent::submitForm($form, $form_state);

    if ($form_state->getValue(ConstantsInterface::SETTING_ROUTE) != $old_route) {
      $this->routeBuilder->setRebuildNeeded();
    }
  }

}
