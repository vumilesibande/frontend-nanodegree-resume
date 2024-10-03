<?php

namespace Drupal\dsu_engage\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\RedundantEditableConfigNamesTrait;
use Drupal\dsu_engage\ConstantsInterface;

/**
 * Configure tooltip settings for dsu_engage.
 */
class DsuEngageToolTipSettingsForm extends ConfigFormBase {
  use RedundantEditableConfigNamesTrait;

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'dsu_engage_tooltip_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form[ConstantsInterface::SETTING_BATCH_CODE_TOOLTIP] = [
      '#type' => 'textfield',
      '#title' => $this->t('Batch Code tooltip'),
      '#description' => $this->t('Tooltip Text for Batch Code'),
      '#config_target' => 'dsu_engage.settings:' . ConstantsInterface::SETTING_BATCH_CODE_TOOLTIP,
    ];

    $form[ConstantsInterface::SETTING_BAR_CODE_TOOLTIP] = [
      '#type' => 'textfield',
      '#title' => $this->t('Bar code tooltip'),
      '#description' => $this->t('Tooltip Text for Bar Code'),
      '#config_target' => 'dsu_engage.settings:' . ConstantsInterface::SETTING_BAR_CODE_TOOLTIP,
    ];

    return parent::buildForm($form, $form_state);
  }

}
