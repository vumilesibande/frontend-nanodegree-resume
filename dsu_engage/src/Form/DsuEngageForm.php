<?php

namespace Drupal\dsu_engage\Form;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\dsu_core\Services\NoticeInterface;
use Drupal\dsu_engage\ConstantsInterface;
use Drupal\dsu_engage\Exception\AccessTokenRequestException;
use Drupal\dsu_engage\Exception\RequestException;
use Drupal\dsu_engage\Services\EngageConnectInterface;
use Drupal\dsu_engage\Services\EngageUtilsInterface;
use Drupal\file\FileStorageInterface;
use Drupal\ln_datalayer\Services\DatalayerEventsInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Engage user form.
 */
class DsuEngageForm extends FormBase {

  /**
   * The dsu_engage.settings config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected ImmutableConfig $settings;

  /**
   * The file storage.
   *
   * @var \Drupal\file\FileStorageInterface
   */
  protected FileStorageInterface $fileStorage;

  /**
   * Constructs a new DsuEngageForm object.
   *
   * @param \Drupal\Core\Extension\ModuleExtensionList $moduleExtensionList
   *   The module extension list.
   * @param \Drupal\dsu_engage\Services\EngageUtilsInterface $engageUtils
   *   The engage utils service.
   * @param \Drupal\dsu_engage\Services\EngageConnectInterface $engageConnect
   *   The engage connect service.
   * @param \Drupal\dsu_core\Services\NoticeInterface $notice
   *   The notice service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\ln_datalayer\Services\DatalayerEventsInterface|null $datalayerEvents
   *   The datalayer events service.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(
    protected ModuleExtensionList $moduleExtensionList,
    protected EngageUtilsInterface $engageUtils,
    protected EngageConnectInterface $engageConnect,
    protected NoticeInterface $notice,
    EntityTypeManagerInterface $entity_type_manager,
    protected ?DatalayerEventsInterface $datalayerEvents = NULL,
  ) {
    $this->fileStorage = $entity_type_manager->getStorage('file');
    $this->settings = $this->config('dsu_engage.settings');
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public static function create(ContainerInterface $container): DsuEngageForm {
    return new static(
      $container->get('extension.list.module'),
      $container->get('dsu_engage.engage_utils'),
      $container->get('dsu_engage.engage_connect'),
      $container->get('dsu_core.notice'),
      $container->get('entity_type.manager'),
      $container->get('ln_datalayer.events', ContainerInterface::NULL_ON_INVALID_REFERENCE),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'dsu_engage_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['#attached']['library'][] = 'dsu_engage/dsu_engage_form';
    $form['#attributes']['novalidate'] = 'novalidate';

    $info = $this->moduleExtensionList->getExtensionInfo('dsu_engage');
    $form['#attached']['drupalSettings']['dsu_engage']['data'] = [
      'module_name' => $info['name'],
      'module_version' => $info['version'],
    ];

    $available_reques_type = $this->settings->get(ConstantsInterface::SETTING_ENABLED_REQUEST_TYPES);
    $form['request_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('What would you like to contact us about?'),
      '#options' => array_intersect_key(ConstantsInterface::REQUEST_TYPE_OPTIONS, array_flip($available_reques_type)),
      '#default_value' => $form_state->getValue('request_type') ?? reset($available_reques_type),
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['request-type-options'],
      ],
    ];

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Your message'),
      '#cols' => 15,
      '#maxlength' => 4000,
      '#required' => TRUE,
    ];

    // Build the generic form for field configuration.
    foreach (ConstantsInterface::GROUP_FIELDS as $group_name => $fields) {
      if ($this->isVisible(...$fields)) {
        $form[$group_name] = [
          '#type' => 'fieldset',
          '#title' => $this->t(ConstantsInterface::GROUP_LABELS[$group_name]),
          '#states' => $this->getStatesField(...$fields),
        ];

        foreach ($fields as $field) {
          if ($this->isVisible($field)) {
            $form[$group_name][$field] = [
              '#type' => 'textfield',
              '#maxlength' => 80,
              '#title' => $this->t(ConstantsInterface::FIELDS[$field]),
              '#states' => $this->getStatesField($field),
            ];
          }
        }
      }
    }

    // Configure specific field settings.
    if (isset($form[ConstantsInterface::GROUP_CONTACT][ConstantsInterface::FIELD_PREFERRED_CHANNEL])) {
      $form[ConstantsInterface::GROUP_CONTACT][ConstantsInterface::FIELD_PREFERRED_CHANNEL] = [
        '#type' => 'fieldset',
        '#title' => $this->t(ConstantsInterface::FIELDS[ConstantsInterface::FIELD_PREFERRED_CHANNEL]),
        '#states' => $form[ConstantsInterface::GROUP_CONTACT][ConstantsInterface::FIELD_PREFERRED_CHANNEL]['#states'],
        'contact_type' => [
          '#type' => 'select',
          '#title' => $this->t('How would you like us to get in touch with you?'),
          '#options' => ConstantsInterface::PREFERRED_CHANNEL_OPTIONS,
        ],
        ConstantsInterface::CHANNEL_EMAIL => [
          '#type' => 'email',
          '#title' => $this->t('Email address'),
          '#maxlength' => 80,
          '#states' => NestedArray::mergeDeep(
            $form[ConstantsInterface::GROUP_CONTACT][ConstantsInterface::FIELD_PREFERRED_CHANNEL]['#states'],
            [
              'visible' => [
                ':input[name="contact_type"]' => ['value' => ConstantsInterface::CHANNEL_EMAIL],
              ],
              'required' => [
                ':input[name="contact_type"]' => ['value' => ConstantsInterface::CHANNEL_EMAIL],
              ],
            ]
          ),
        ],
        ConstantsInterface::CHANNEL_PHONE_PREFIX => [
          '#type' => 'select',
          '#title' => $this->t('Phone prefix'),
          '#options' => $this->engageUtils->getValues(ConstantsInterface::SETTING_PHONE_LIST),
          '#states' => NestedArray::mergeDeep(
            $form[ConstantsInterface::GROUP_CONTACT][ConstantsInterface::FIELD_PREFERRED_CHANNEL]['#states'],
            [
              'visible' => [
                ':input[name="contact_type"]' => ['value' => ConstantsInterface::CHANNEL_PHONE],
              ],
              'required' => [
                ':input[name="contact_type"]' => ['value' => ConstantsInterface::CHANNEL_PHONE],
              ],
            ]
          ),
        ],
        ConstantsInterface::CHANNEL_PHONE => [
          '#type' => 'textfield',
          '#title' => $this->t('Phone'),
          '#maxlength' => 30,
          '#states' => NestedArray::mergeDeep(
            $form[ConstantsInterface::GROUP_CONTACT][ConstantsInterface::FIELD_PREFERRED_CHANNEL]['#states'],
            [
              'visible' => [
                ':input[name="contact_type"]' => ['value' => ConstantsInterface::CHANNEL_PHONE],
              ],
              'required' => [
                ':input[name="contact_type"]' => ['value' => ConstantsInterface::CHANNEL_PHONE],
              ],
            ]
          ),
        ],
      ];
    }

    if (isset($form[ConstantsInterface::GROUP_CONTACT][ConstantsInterface::FIELD_PREFERRED_CHANNEL][ConstantsInterface::CHANNEL_PHONE_PREFIX]['#maxlength'])) {
      unset($form[ConstantsInterface::GROUP_CONTACT][ConstantsInterface::FIELD_PREFERRED_CHANNEL][ConstantsInterface::CHANNEL_PHONE_PREFIX]['#maxlength']);
    }

    if (isset($form[ConstantsInterface::GROUP_CONTACT][ConstantsInterface::FIELD_COUNTRY])) {
      $country_element = [
        '#type' => 'select',
        '#options' => $this->engageUtils->getValues(ConstantsInterface::SETTING_COUNTRY_LIST),
      ];
      if (isset($form[ConstantsInterface::GROUP_CONTACT][ConstantsInterface::FIELD_COUNTRY]['#maxlength'])) {
        unset($form[ConstantsInterface::GROUP_CONTACT][ConstantsInterface::FIELD_COUNTRY]['#maxlength']);
      }
      if (isset($form[ConstantsInterface::GROUP_CONTACT][ConstantsInterface::FIELD_STATE])) {
        $country_element['#ajax'] = [
          'event' => 'change',
          'wrapper' => 'states-wrapper',
          'callback' => '::getStatesOptions',
        ];

        $states_options = NULL;
        if (($country = $form_state->getValue(ConstantsInterface::FIELD_COUNTRY))
          || ($country = $form_state->getUserInput()[ConstantsInterface::FIELD_COUNTRY] ?? NULL)
        ) {
          $states_options = $this->engageUtils->getValueFromKey(
            $country,
            ConstantsInterface::SETTING_STATE_LIST
          );
        }

        $form[ConstantsInterface::GROUP_CONTACT][ConstantsInterface::FIELD_STATE] = [
          '#type' => 'select',
          '#options' => $states_options ?? [],
          '#empty_option' => $states_options ? $this->t('- Select state -') : $this->t('- No states available -'),
          '#wrapper_attributes' => [
            'id' => 'states-wrapper',
          ],
        ] + $form[ConstantsInterface::GROUP_CONTACT][ConstantsInterface::FIELD_STATE];
        if (isset($form[ConstantsInterface::GROUP_CONTACT][ConstantsInterface::FIELD_STATE]['#maxlength'])) {
          unset($form[ConstantsInterface::GROUP_CONTACT][ConstantsInterface::FIELD_STATE]['#maxlength']);
        }
      }
      $form[ConstantsInterface::GROUP_CONTACT][ConstantsInterface::FIELD_COUNTRY] = $country_element
        + $form[ConstantsInterface::GROUP_CONTACT][ConstantsInterface::FIELD_COUNTRY];
    }

    if (isset($form[ConstantsInterface::GROUP_PRODUCT][ConstantsInterface::FIELD_BATCH_CODE])) {
      $form[ConstantsInterface::GROUP_PRODUCT][ConstantsInterface::FIELD_BATCH_CODE] = [
        '#attributes' => [
          'data-bs-toggle' => 'tooltip',
          'title' => $this->settings->get(ConstantsInterface::SETTING_BATCH_CODE_TOOLTIP),
        ],
      ] + $form[ConstantsInterface::GROUP_PRODUCT][ConstantsInterface::FIELD_BATCH_CODE];
    }

    if (isset($form[ConstantsInterface::GROUP_PRODUCT][ConstantsInterface::FIELD_BAR_CODE])) {
      $form[ConstantsInterface::GROUP_PRODUCT][ConstantsInterface::FIELD_BAR_CODE] = [
        '#attributes' => [
          'data-bs-toggle' => 'tooltip',
          'title' => $this->settings->get(ConstantsInterface::SETTING_BAR_CODE_TOOLTIP),
        ],
      ] + $form[ConstantsInterface::GROUP_PRODUCT][ConstantsInterface::FIELD_BAR_CODE];
    }

    if (isset($form[ConstantsInterface::GROUP_PRODUCT][ConstantsInterface::FIELD_BEST_BEFORE_DATE])) {
      $form[ConstantsInterface::GROUP_PRODUCT][ConstantsInterface::FIELD_BEST_BEFORE_DATE] = [
        '#type' => 'date',
      ] + $form[ConstantsInterface::GROUP_PRODUCT][ConstantsInterface::FIELD_BEST_BEFORE_DATE];
    }

    if (isset($form[ConstantsInterface::GROUP_ATTACHMENTS][ConstantsInterface::FIELD_ATTACHMENTS])) {
      $validators = [
        'FileExtension' => ['extensions' => ConstantsInterface::ATTACHMENTS_EXTENSIONS],
        'FileSizeLimit' => ['fileLimit' => ConstantsInterface::ATTACHMENTS_MAX_FILE_SIZE],
      ];
      $form[ConstantsInterface::GROUP_ATTACHMENTS][ConstantsInterface::FIELD_ATTACHMENTS] = [
        '#type' => 'managed_file',
        '#title_display' => 'invisible',
        '#description' => [
          '#theme' => 'file_upload_help',
          '#description' => $this->t('You can upload up to %max files.', [
            '%max' => ConstantsInterface::ATTACHMENTS_MAX,
          ]),
          '#upload_validators' => $validators,
        ],
        '#upload_location' => 'public://engage',
        '#upload_validators' => $validators,
        '#multiple' => TRUE,
      ] + $form[ConstantsInterface::GROUP_ATTACHMENTS][ConstantsInterface::FIELD_ATTACHMENTS];
      if (isset($form[ConstantsInterface::GROUP_ATTACHMENTS][ConstantsInterface::FIELD_ATTACHMENTS]['#maxlength'])) {
        unset($form[ConstantsInterface::GROUP_ATTACHMENTS][ConstantsInterface::FIELD_ATTACHMENTS]['#maxlength']);
      }
    }

    if ($this->settings->get('dsu_engage_additional_footer_enable')) {
      $form['campaign'] = [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $this->settings->get('dsu_engage_additional_footer_notes') . '&nbsp;',
        'link' => [
          '#type' => 'link',
          '#title' => $this->settings->get('dsu_engage_additional_footer_contact'),
          '#url' => Url::fromUri('tel:' . rawurlencode($this->settings->get('dsu_engage_additional_footer_contact'))),
          '#options' => ['external' => TRUE],
        ],
      ];
    }

    $form['privacy_policy'] = [
      '#type' => 'checkbox',
      '#required' => TRUE,
      '#required_error' => $this->t('Please read and accept the Privacy Policy'),
      '#title' => [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $this->t($this->settings->get('dsu_engage_privacy_policy_text'), [
          '@link' => Link::fromTextAndUrl(
            $this->settings->get('dsu_engage_privacy_policy_link_text'),
            Url::fromUri(
              $this->settings->get('dsu_engage_privacy_policy_link_url'),
              [
                'attributes' => ['target' => '_blank'],
              ],
            ),
          )->toString(),
        ]),
      ],
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * Ajax refresh callback for the state field options.
   *
   * @param array $form
   *   The form array to remove elements from.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public static function getStatesOptions(array $form, FormStateInterface $form_state): array {
    return $form[ConstantsInterface::GROUP_CONTACT][ConstantsInterface::FIELD_STATE];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    if ($form_state->getTriggeringElement()['#name'] !== 'op') {
      return;
    }

    $request_type = $form_state->getValue('request_type');
    // Global validations.
    foreach (ConstantsInterface::FIELDS as $field => $label) {
      // The preferred channel not use global validations.
      if ($field == ConstantsInterface::FIELD_PREFERRED_CHANNEL) {
        continue;
      }
      if ($this->isRequired($field, $request_type) && $form_state->isValueEmpty($field)) {
        $form_state->setErrorByName($field, $this->t('@name field is required.', ['@name' => $label]));
      }
    }

    // Custom validation for Preferred channel "composite" field.
    if ($this->isRequired(ConstantsInterface::FIELD_PREFERRED_CHANNEL, $request_type)) {
      $contact_type = $form_state->getValue('contact_type');
      if ($contact_type == ConstantsInterface::CHANNEL_PHONE) {
        if ($form_state->isValueEmpty(ConstantsInterface::CHANNEL_PHONE)) {
          $form_state->setErrorByName(ConstantsInterface::CHANNEL_PHONE, $this->t('Phone field is required.'));
        }
        if ($form_state->isValueEmpty(ConstantsInterface::CHANNEL_PHONE_PREFIX)) {
          $form_state->setErrorByName(ConstantsInterface::CHANNEL_PHONE_PREFIX, $this->t('Phone prefix field is required.'));
        }
      }
      elseif ($form_state->isValueEmpty(ConstantsInterface::CHANNEL_EMAIL)) {
        $form_state->setErrorByName(ConstantsInterface::CHANNEL_EMAIL, $this->t('Email field is required.'));
      }
    }

    // Custom validation for Attachments field.
    if (count($form_state->getValue(ConstantsInterface::FIELD_ATTACHMENTS, [])) > ConstantsInterface::ATTACHMENTS_MAX) {
      $form_state->setErrorByName(ConstantsInterface::FIELD_ATTACHMENTS, $this->t('The number of files uploaded exceed the maximum allowed.'));
    }

    if ($this->datalayerEvents) {
      // Throw a datalayer event for each error.
      foreach ($form_state->getErrors() as $key => $error) {
        $this->datalayerEvents->addEvent("form_validate_{$form['#id']}_{$key}", [
          'event' => 'contact_error',
          'event_name' => 'contactus_submit_error',
          'form_name' => $form['#id'],
          'form_id' => $form['#id'],
          'topic' => $request_type,
          'error_code' => $key,
          'error_name' => $error,
          'module_name' => $form['#attached']['drupalSettings']['dsu_engage']['data']['module_name'],
          'module_version' => $form['#attached']['drupalSettings']['dsu_engage']['data']['module_version'],
        ]);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    // Prepare attachments.
    $attachedFiles = [];
    if ($fids = $form_state->getValue(ConstantsInterface::FIELD_ATTACHMENTS)) {
      foreach ($fids as $fid) {
        /** @var \Drupal\file\FileInterface $file */
        if (($file = $this->fileStorage->load($fid))
          && ($file_contents = file_get_contents($file->getFileUri()))
        ) {
          $attachedFiles[] = [
            'attachmentName' => $file->getFilename(),
            'attachmentBody' => base64_encode($file_contents),
          ];
        }
      }
    }
    $phone_prefix = $this->getOptionValue(
      $form,
      $form_state,
      [
        ConstantsInterface::GROUP_CONTACT,
        ConstantsInterface::FIELD_PREFERRED_CHANNEL,
      ],
      ConstantsInterface::CHANNEL_PHONE_PREFIX,
    );

    $data = [
      'requestType' => $form_state->getValue('request_type'),
      'description' => $form_state->getValue('description'),
      'firstName' => $form_state->getValue(ConstantsInterface::FIELD_FIRST_NAME),
      'lastName' => $form_state->getValue(ConstantsInterface::FIELD_LAST_NAME),
      'email' => $form_state->getValue(ConstantsInterface::CHANNEL_EMAIL),
      'street' => $form_state->getValue(ConstantsInterface::FIELD_STREET),
      'zipCode' => $form_state->getValue(ConstantsInterface::FIELD_ZIP_CODE),
      'city' => $form_state->getValue(ConstantsInterface::FIELD_CITY),
      'country' => $this->getOptionValue($form, $form_state, [ConstantsInterface::GROUP_CONTACT], ConstantsInterface::FIELD_COUNTRY),
      'state' => $this->getOptionValue($form, $form_state, [ConstantsInterface::GROUP_CONTACT], ConstantsInterface::FIELD_STATE),
      'preferredChannel' => $form_state->getValue('contact_type'),
      'phone' => $form_state->getValue(ConstantsInterface::CHANNEL_PHONE) ? $phone_prefix . $form_state->getValue(ConstantsInterface::CHANNEL_PHONE) : NULL,
      'attachments' => $attachedFiles,
      'barCode' => $form_state->getValue(ConstantsInterface::FIELD_BAR_CODE),
      'batchCode' => $form_state->getValue(ConstantsInterface::FIELD_BATCH_CODE),
      'productDescription' => $form_state->getValue(ConstantsInterface::FIELD_PRODUCT_DESCRIPTION),
      'bestBeforeDate' => $form_state->getValue(ConstantsInterface::FIELD_BEST_BEFORE_DATE),
      'JSONRequestId' => $form_state->getValue('form_build_id'),
    ];

    try {
      $response = $this->engageConnect->request($data);

      // Throw a success datalayer event.
      $this->datalayerEvents?->addEvent("form_submit_{$form['#id']}", [
        'event' => 'contact_interaction',
        'event_name' => 'contactus_submit',
        'form_name' => $form['#id'],
        'form_id' => $form['#id'],
        'topic' => $form_state->getValue('request_type'),
        'module_name' => $form['#attached']['drupalSettings']['dsu_engage']['data']['module_name'],
        'module_version' => $form['#attached']['drupalSettings']['dsu_engage']['data']['module_version'],
      ]);

      $this->messenger()->addMessage(
        $this->t('Your ticket is created. Your contact number is: @ticket', ['@ticket' => $response['caseNumber']])
      );
    }
    catch (AccessTokenRequestException $e) {
      $this->messenger()->addMessage(
        $this->t('The access token is wrong. Your request was not sent to our contact system.'),
        'error'
      );
      $this->notice->sendNotice(
        $this->t('An error occurred while submitting the engage contact form: The access token is wrong. Your request was not sent to our contact system.'),
        Url::fromRoute('dsu_engage.admin_settings_form')
      );
      $form_state->setRebuild();
    }
    catch (RequestException $e) {
      $this->messenger()->addMessage($e->getMessage(), 'error');
      $this->notice->sendNotice(
        $this->t('An error occurred while submitting the engage contact form: @error_message', ['@error_message' => $e->getMessage()]),
        Url::fromRoute('dsu_engage.admin_settings_form')
      );
      $form_state->setRebuild();
    }
  }

  /**
   * Get option value from selected key.
   *
   * @param array $form
   *   The form array to remove elements from.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $parents
   *   The fields to check for visibility.
   * @param string $field_name
   *   The current field name.
   *
   * @return string|null
   *   The associated value from key or NULL if not exist.
   */
  protected function getOptionValue(array $form, FormStateInterface $form_state, array $parents, string $field_name): ?string {
    return NestedArray::getValue($form, [
      ...$parents,
      $field_name,
      '#options',
      $form_state->getValue($field_name),
    ]);
  }

  /**
   * Determines if a field is visible.
   *
   * @param string ...$fields
   *   The fields to check for visibility.
   *
   * @return bool
   *   TRUE if the field is visible, otherwise FALSE.
   */
  protected function isVisible(string ...$fields): bool {
    foreach ($fields as $field) {
      foreach (ConstantsInterface::REQUEST_TYPE_OPTIONS as $request_type => $label) {
        if ($this->settings->get("show_{$field}_{$request_type}")) {
          return TRUE;
        }
      }
    }

    return FALSE;
  }

  /**
   * Determines if a field is required for provided request_type.
   *
   * @param string $field
   *   The field to check for mandatory.
   * @param string $request_type
   *   The request type.
   *
   * @return bool
   *   TRUE if the field is visible, otherwise FALSE.
   */
  protected function isRequired(string $field, string $request_type): bool {
    return (bool) $this->settings->get("mandatory_{$field}_{$request_type}");
  }

  /**
   * Get the array of states for the indicated fields.
   *
   * @param string ...$fields
   *   The fields to check.
   *
   * @return array
   *   The states array.
   */
  protected function getStatesField(string ...$fields): array {
    $states = $visible = $required = [];
    foreach ($fields as $field) {
      foreach (ConstantsInterface::REQUEST_TYPE_OPTIONS as $request_type => $label) {
        if ($this->settings->get("show_{$field}_{$request_type}")) {
          $visible[] = ['value' => $request_type];
        }
        if ($this->settings->get("mandatory_{$field}_{$request_type}")) {
          $required[] = ['value' => $request_type];
        }
      }
    }

    if (!empty($visible)) {
      $states['visible'] = [
        ':input[name="request_type"]' => $visible,
      ];
    }

    if (!empty($required)) {
      $states['required'] = [
        ':input[name="request_type"]' => $required,
      ];
    }

    return $states;
  }

}
