<?php

namespace Drupal\dsu_engage\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\RedundantEditableConfigNamesTrait;
use Drupal\dsu_engage\ConstantsInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Configure form data settings for dsu_engage.
 */
class DsuEngageSettingsDataForm extends ConfigFormBase {
  use RedundantEditableConfigNamesTrait;

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'dsu_engage_data_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $form[ConstantsInterface::SETTING_COUNTRY_LIST] = [
      '#type' => 'textarea',
      '#title' => $this->t('Country list'),
      '#rows' => 15,
      '#config_target' => 'dsu_engage.datasets:' . ConstantsInterface::SETTING_COUNTRY_LIST,
      '#description' => $this->t('Put each country on a separate line. Must be valid YML format.'),
      '#element_validate' => [[static::class, 'validateYaml']],
    ];

    $form[ConstantsInterface::SETTING_STATE_LIST] = [
      '#type' => 'textarea',
      '#title' => $this->t('State list by country'),
      '#rows' => 15,
      '#config_target' => 'dsu_engage.datasets:' . ConstantsInterface::SETTING_STATE_LIST,
      '#description' => $this->t('Put each country on a separate line. Must be valid YML format.'),
    ];

    $form[ConstantsInterface::SETTING_PHONE_LIST] = [
      '#type' => 'textarea',
      '#title' => $this->t('Phone list'),
      '#rows' => 15,
      '#config_target' => 'dsu_engage.datasets:' . ConstantsInterface::SETTING_PHONE_LIST,
      '#description' => $this->t('Put each phone value on a separate line. Must be valid YML format.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Form element validation handler; Filters the #value property of an element.
   */
  public static function validateYaml(&$element, FormStateInterface $form_state): void {
    if ($value = $element['#value']) {
      try {
        Yaml::parse($value);
      }
      catch (ParseException $e) {
        $form_state->setError($element, t("Is not valid YAML: @error_message", ['@error_message' => $e->getMessage()]));
      }
    }
  }

}
