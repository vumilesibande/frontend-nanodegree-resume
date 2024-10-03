<?php

namespace Drupal\dsu_engage_rr\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\RedundantEditableConfigNamesTrait;
use Drupal\user\UserStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DsuEngageRRSettingsForm.
 *
 * Represents a form for configuring settings related to the DsuEngageRR module.
 */
class DsuEngageRRSettingsForm extends ConfigFormBase {
  use RedundantEditableConfigNamesTrait;


  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  protected UserStorageInterface $userStorage;

  /**
   * Constructs a \Drupal\system\ConfigFormBase object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date
   *   The date formatter service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param null $typedConfigManager
   *   The typed config manager.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    protected DateFormatterInterface $date,
    ConfigFactoryInterface $config_factory,
    protected $typedConfigManager = NULL,
  ) {
    $this->userStorage = $entity_type_manager->getStorage('user');
    parent::__construct($config_factory, $typedConfigManager);
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public static function create(ContainerInterface $container): DsuEngageRRSettingsForm {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('date.formatter'),
      $container->get('config.factory'),
      $container->get('config.typed')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'dsu_engage_rr_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('dsu_engage_rr.settings');

    $form['endpoint'] = [
      '#type' => 'url',
      '#title' => $this->t('Endpoint'),
      '#required' => TRUE,
      '#size' => 200,
      '#config_target' => 'dsu_engage_rr.settings:endpoint',
      '#description' => $this->t('Enter the endpoint URL to bring response with cron.<br> The url must not contain the <b>Engage API Contact Origin</b> because it will be automatically concatenated.<br> Example: https://nestlecesomni--preprod.sandbox.my.salesforce.com/services/apexrest/RR/v1/comments'),
    ];

    $form['endpoint_error'] = [
      '#type' => 'url',
      '#title' => $this->t('Endpoint Error'),
      '#required' => TRUE,
      '#size' => 200,
      '#config_target' => 'dsu_engage_rr.settings:endpoint_error',
      '#description' => $this->t('Enter the endpoint URL to report error.'),
    ];

    $form['subject_default'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject for default'),
      '#size' => 200,
      '#config_target' => 'dsu_engage_rr.settings:subject_default',
      '#default_value' => $config->get('subject_default') ? $config->get('subject_default') : 'Agent response',
      '#description' => $this->t('Enter subject for default.'),
    ];

    $form['drupal_user'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'user',
      '#title' => $this->t('Drupal User'),
      '#required' => TRUE,
      '#config_target' => 'dsu_engage_rr.settings:drupal_user',
      '#default_value' => $config->get('drupal_user') ? $this->userStorage->load($config->get('drupal_user')) : '',
      '#description' => $this->t('Select a Drupal user for create replies.'),
    ];

    $options = [60, 3600, 10800, 21600, 43200, 86400, 604800];
    $form['cron_interval'] = [
      '#type' => 'select',
      '#title' => $this->t('Run cron every'),
      '#description' => $this->t('Enter the interval for the cron job.'),
      '#config_target' => 'dsu_engage_rr.settings:cron_interval',
      '#options' => [0 => $this->t('Never')] + array_map([$this->date, 'formatInterval'], array_combine($options, $options)),
    ];

    return parent::buildForm($form, $form_state);
  }

}
