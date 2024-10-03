<?php

namespace Drupal\dsu_engage;

/**
 * Constants file for dsu_engage module.
 */
interface ConstantsInterface {

  public const REQUEST_TYPE_QUESTION = 'question';
  public const REQUEST_TYPE_COMPLAINT = 'complaint';
  public const REQUEST_TYPE_PRAISE = 'praise';

  public const REQUEST_TYPE_OPTIONS = [
    self::REQUEST_TYPE_QUESTION => 'I have a question/suggestion',
    self::REQUEST_TYPE_COMPLAINT => 'I have a complaint',
    self::REQUEST_TYPE_PRAISE => 'I have a praise',
  ];

  public const FIELD_FIRST_NAME = 'field_first_name';
  public const FIELD_LAST_NAME = 'field_last_name';
  public const FIELD_PREFERRED_CHANNEL = 'field_preferred_channel';
  public const FIELD_PRODUCT_DESCRIPTION = 'field_product_description';
  public const FIELD_BATCH_CODE = 'field_batch_code';
  public const FIELD_BAR_CODE = 'field_bar_code';
  public const FIELD_BEST_BEFORE_DATE = 'field_best_before_date';
  public const FIELD_STREET = 'field_street';
  public const FIELD_CITY = 'field_city';
  public const FIELD_ZIP_CODE = 'field_zip_code';
  public const FIELD_STATE = 'field_state';
  public const FIELD_COUNTRY = 'field_country';
  public const FIELD_ATTACHMENTS = 'field_attachments';

  public const FIELDS = [
    self::FIELD_FIRST_NAME => 'First Name',
    self::FIELD_LAST_NAME => 'Last Name',
    self::FIELD_PREFERRED_CHANNEL => 'Preferred Channel',
    self::FIELD_STREET => 'Street',
    self::FIELD_CITY => 'City',
    self::FIELD_ZIP_CODE => 'Zip Code',
    self::FIELD_COUNTRY => 'Country',
    self::FIELD_STATE => 'State',
    self::FIELD_PRODUCT_DESCRIPTION => 'Product Description',
    self::FIELD_BATCH_CODE => 'Batch Code',
    self::FIELD_BAR_CODE => 'Bar Code',
    self::FIELD_BEST_BEFORE_DATE => 'Best before date',
    self::FIELD_ATTACHMENTS => 'Attachments',
  ];

  public const GROUP_PRODUCT = 'product';
  public const GROUP_CONTACT = 'contact';
  public const GROUP_ATTACHMENTS = 'attachment';

  public const GROUP_LABELS = [
    self::GROUP_CONTACT => 'Your contact information',
    self::GROUP_PRODUCT => 'Product information',
    self::GROUP_ATTACHMENTS => 'Attachments',
  ];

  public const GROUP_FIELDS = [
    self::GROUP_CONTACT => [
      self::FIELD_FIRST_NAME,
      self::FIELD_LAST_NAME,
      self::FIELD_PREFERRED_CHANNEL,
      self::FIELD_STREET,
      self::FIELD_CITY,
      self::FIELD_ZIP_CODE,
      self::FIELD_COUNTRY,
      self::FIELD_STATE,
    ],
    self::GROUP_PRODUCT => [
      self::FIELD_PRODUCT_DESCRIPTION,
      self::FIELD_BATCH_CODE,
      self::FIELD_BAR_CODE,
      self::FIELD_BEST_BEFORE_DATE,
    ],
    self::GROUP_ATTACHMENTS => [
      self::FIELD_ATTACHMENTS,
    ],
  ];

  public const CHANNEL_EMAIL = 'email';
  public const CHANNEL_PHONE = 'phone';
  public const CHANNEL_PHONE_PREFIX = 'phone_prefix';

  public const PREFERRED_CHANNEL_OPTIONS = [
    self::CHANNEL_EMAIL => 'Email',
    self::CHANNEL_PHONE => 'Phone',
  ];

  public const ATTACHMENTS_MAX = 9;
  public const ATTACHMENTS_MAX_FILE_SIZE = 4.3 * 1024 * 1024;
  public const ATTACHMENTS_EXTENSIONS = 'xps pdf doc docx rtf txt xls xlsx csv bmp png jpeg jpg';


  // Setting names.
  public const API_ENDPOINT_TOKEN_URL = 'dsu_engage_api_token_url';
  public const API_ENDPOINT_URL = 'dsu_engage_api_endpoint_url';
  public const API_CLIENT_ID = 'dsu_engage_api_client_id';
  public const API_CLIENT_SECRET = 'dsu_engage_api_client_secret';
  public const API_USERNAME = 'dsu_engage_api_user_username';
  public const API_PASSWORD = 'dsu_engage_api_user_password';
  public const API_CLIENT_CERTIFICATE = 'dsu_engage_api_client_certificate';
  public const API_AUDIENCE_URL = 'dsu_engage_api_audience_url';

  public const API_BRAND = 'dsu_engage_api_brand';
  public const API_MARKET = 'dsu_engage_api_market';
  public const API_COUNTRY = 'dsu_engage_api_country';
  public const API_CONTACT_ORIGIN = 'dsu_engage_api_contact_origin';

  public const SETTING_ROUTE_ENABLED = 'enable_engage_form';
  public const SETTING_ROUTE = 'engage_form_path';
  public const SETTING_ENABLED_REQUEST_TYPES = 'show_field_request_type_options';

  public const SETTING_BATCH_CODE_TOOLTIP = 'dsu_engage_tooltip_text_batch_code';
  public const SETTING_BAR_CODE_TOOLTIP = 'dsu_engage_tooltip_text_bar_code';

  public const SETTING_COUNTRY_LIST = 'countries';
  public const SETTING_PHONE_LIST = 'phone_prefixes';
  public const SETTING_STATE_LIST = 'states';

}
