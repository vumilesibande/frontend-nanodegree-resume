<?php

namespace Drupal\dsu_engage\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\RedundantEditableConfigNamesTrait;
use Drupal\dsu_engage\ConstantsInterface;

/**
 * Configure form connect settings for dsu_engage.
 */
class DsuEngageSettingsForm extends ConfigFormBase {
  use RedundantEditableConfigNamesTrait;

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'dsu_engage_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['general'] = [
      '#type' => 'details',
      '#title' => $this->t('Common Configuration'),
      '#open' => TRUE,
    ];

    $form['general'][ConstantsInterface::API_ENDPOINT_TOKEN_URL] = [
      '#type' => 'textfield',
      '#title' => $this->t('Engage API Endpoint Token URL'),
      '#required' => TRUE,
      '#description' => $this->t('To connect with Engage, REST API requests need to be send to a Endpoint Token URL'),
      '#size' => 200,
      '#config_target' => 'dsu_engage.settings:' . ConstantsInterface::API_ENDPOINT_TOKEN_URL,
    ];

    $form['general'][ConstantsInterface::API_ENDPOINT_URL] = [
      '#type' => 'textfield',
      '#title' => $this->t('Engage API Endpoint URL'),
      '#required' => TRUE,
      '#description' => $this->t('To connect with Engage, REST API requests need to be send to a Endpoint URL'),
      '#size' => 200,
      '#config_target' => 'dsu_engage.settings:' . ConstantsInterface::API_ENDPOINT_URL,
    ];

    $form['general'][ConstantsInterface::API_CLIENT_ID] = [
      '#type' => 'textfield',
      '#title' => $this->t('Engage API Client ID'),
      '#description' => $this->t('Please enter Engage API Client ID'),
      '#config_target' => 'dsu_engage.settings:' . ConstantsInterface::API_CLIENT_ID,
    ];

    $form['general'][ConstantsInterface::API_CLIENT_SECRET] = [
      '#type' => 'textfield',
      '#title' => $this->t('Engage API Client Secret'),
      '#description' => $this->t('Please enter Engage API Client Secret Key'),
      '#config_target' => 'dsu_engage.settings:' . ConstantsInterface::API_CLIENT_SECRET,
    ];

    $form['user_details'] = [
      '#type' => 'details',
      '#title' => $this->t('User Details'),
      '#open' => TRUE,
    ];

    $form['user_details'][ConstantsInterface::API_USERNAME] = [
      '#type' => 'textfield',
      '#title' => $this->t('Engage API User Username'),
      '#description' => $this->t('Please enter Engage API User Username'),
      '#config_target' => 'dsu_engage.settings:' . ConstantsInterface::API_USERNAME,
    ];

    $form['user_details'][ConstantsInterface::API_PASSWORD] = [
      '#type' => 'textfield',
      '#title' => $this->t('Engage API User Password'),
      '#description' => $this->t('Please enter Engage API User Password'),
      '#config_target' => 'dsu_engage.settings:' . ConstantsInterface::API_PASSWORD,
    ];

    $form['user_details'][ConstantsInterface::API_CLIENT_CERTIFICATE] = [
      '#type' => 'textfield',
      '#title' => $this->t('Engage API client certificate path'),
      '#description' => $this->t("Enter the path of the client certificate location in this server. If you don't know, contact WebCMS team."),
      '#attributes' => ['placeholder' => '/var/www/cert.key'],
      '#config_target' => 'dsu_engage.settings:' . ConstantsInterface::API_CLIENT_CERTIFICATE,
    ];

    $form['user_details'][ConstantsInterface::API_AUDIENCE_URL] = [
      '#type' => 'textfield',
      '#title' => $this->t('Engage API Audience URL'),
      '#description' => $this->t("To connect with Engage, REST API requests need to be send to a Audience URL"),
      '#config_target' => 'dsu_engage.settings:' . ConstantsInterface::API_AUDIENCE_URL,
    ];

    $form['market_details'] = [
      '#type' => 'details',
      '#title' => $this->t('Market Details'),
      '#open' => TRUE,
    ];

    $form['market_details'][ConstantsInterface::API_BRAND] = [
      '#type' => 'textfield',
      '#title' => $this->t('Engage API Brand'),
      '#description' => $this->t('Please enter Engage API Brand (Case sentitive)'),
      '#config_target' => 'dsu_engage.settings:' . ConstantsInterface::API_BRAND,
    ];

    $form['market_details'][ConstantsInterface::API_MARKET] = [
      '#type' => 'textfield',
      '#title' => $this->t('Engage API Market'),
      '#description' => $this->t('Please enter Engage API Market (2 Digits)'),
      '#config_target' => 'dsu_engage.settings:' . ConstantsInterface::API_MARKET,
    ];

    $form['market_details'][ConstantsInterface::API_COUNTRY] = [
      '#type' => 'textfield',
      '#title' => $this->t('Engage API Country'),
      '#description' => $this->t('Please enter Engage API Country (2 Digits)'),
      '#config_target' => 'dsu_engage.settings:' . ConstantsInterface::API_COUNTRY,
    ];

    $form['market_details'][ConstantsInterface::API_CONTACT_ORIGIN] = [
      '#type' => 'textfield',
      '#title' => $this->t('Engage API Contact Origin'),
      '#description' => $this->t('Website url without http(s)'),
      '#config_target' => 'dsu_engage.settings:' . ConstantsInterface::API_CONTACT_ORIGIN,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    parent::validateForm($form, $form_state);

    if (($certificate = $form_state->getValue(ConstantsInterface::API_CLIENT_CERTIFICATE))
      && !file_exists($certificate)
    ) {
      $form_state->setErrorByName(ConstantsInterface::API_CLIENT_CERTIFICATE, $this->t('The certificate does not exist in the indicated path'));
    }
  }

}
