<?php

namespace Drupal\dsu_engage\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\RedundantEditableConfigNamesTrait;

/**
 * Configure form additional settings for dsu_engage.
 */
class DsuEngageSettingsAdditionalForm extends ConfigFormBase {
  use RedundantEditableConfigNamesTrait;

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'dsu_engage_additional_settings';
  }

  /**
   * Builds the form for the DsuEngageSettingsAdditionalForm.
   *
   * @param array $form
   *   An associative array containing the current state of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   An associative array containing the form elements.
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['dsu_engage_privacy_policy_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Privacy policy text'),
      '#description' => $this->t('Use @link to display the link: For example: <em>I accept the @link.</em>'),
      '#required' => TRUE,
      '#config_target' => 'dsu_engage.settings:dsu_engage_privacy_policy_text',
    ];

    $form['dsu_engage_privacy_policy_link_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Privacy policy link text'),
      '#required' => TRUE,
      '#config_target' => 'dsu_engage.settings:dsu_engage_privacy_policy_link_text',
    ];

    $form['dsu_engage_privacy_policy_link_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Privacy policy url'),
      '#required' => TRUE,
      '#config_target' => 'dsu_engage.settings:dsu_engage_privacy_policy_link_url',
    ];

    $form['dsu_engage_additional_footer_enable'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Additional Notes'),
      '#config_target' => 'dsu_engage.settings:dsu_engage_additional_footer_enable',
    ];

    $form['dsu_engage_additional_footer_notes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Footer notes'),
      '#config_target' => 'dsu_engage.settings:dsu_engage_additional_footer_notes',
      '#states' => [
        'visible' => [
          ':input[name="dsu_engage_additional_footer_enable"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['dsu_engage_additional_footer_contact'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Contact No'),
      '#config_target' => 'dsu_engage.settings:dsu_engage_additional_footer_contact',
      '#states' => [
        'visible' => [
          ':input[name="dsu_engage_additional_footer_enable"]' => ['checked' => TRUE],
        ],
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

}
