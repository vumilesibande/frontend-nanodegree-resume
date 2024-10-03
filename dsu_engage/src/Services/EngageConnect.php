<?php

namespace Drupal\dsu_engage\Services;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\dsu_engage\ConstantsInterface;
use Drupal\dsu_engage\Exception\AccessTokenRequestException;
use Drupal\dsu_engage\Exception\RequestException;
use Firebase\JWT\JWT;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class for engage_connect service.
 */
class EngageConnect implements EngageConnectInterface {
  /**
   * The dsu_engage.settings config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected ImmutableConfig $config;

  /**
   * Constructs a new EngageConnect object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Parameter $config_factory.
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   The http client.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   Parameter $module_handler.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   The logger service.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    protected readonly ClientInterface $httpClient,
    protected readonly ModuleHandlerInterface $moduleHandler,
    protected LanguageManagerInterface $languageManager,
    protected LoggerChannelInterface $logger,
  ) {
    $this->config = $config_factory->get('dsu_engage.settings');
  }

  /**
   * {@inheritdoc}
   */
  public function request(array $data, $url = NULL):array {
    $token = $this->dsuEngageGetToken();
    if (!$token) {
      throw new AccessTokenRequestException();
    }

    $data['brand'] = $data['brand'] ?? $this->config->get(ConstantsInterface::API_BRAND);
    $data['market'] = strtoupper($data['market'] ?? $this->config->get(ConstantsInterface::API_MARKET));
    $data['country'] = $data['country'] ?? $this->config->get(ConstantsInterface::API_COUNTRY);
    $data['consumerContactOrigin'] = $data['consumerContactOrigin'] ?? $this->config->get(ConstantsInterface::API_CONTACT_ORIGIN);
    $data['language'] = $data['language'] ?? $this->languageManager->getCurrentLanguage()->getName();

    $this->moduleHandler->alter('dsu_engage_data', $data);

    $url = $url ?? $this->config->get(ConstantsInterface::API_ENDPOINT_URL);

    try {
      $response = $this->dsuEngagePost($url, $data, $token);

      if ($response['status'] == 'failure' || $response['status'] === 'error') {
        $this->logger->error('Response: @response', ['@response' => print_r($response, TRUE)]);
        throw new RequestException($response['errorMessage'] ?? 'The website encountered an unexpected error. Try again later');
      }
    }
    catch (GuzzleException $e) {
      throw new RequestException($e->getMessage());
    }

    return $response;
  }

  /**
   * Get Engage API access token.
   *
   * @return string|null
   *   The access token.
   */
  protected function dsuEngageGetToken(): ?string {
    if (extension_loaded('openssl')
      && $certificate = $this->config->get(ConstantsInterface::API_CLIENT_CERTIFICATE)
    ) {
      $options = $this->dsuEngageGetTokenFromJwt($certificate);
    }
    else {
      $options = [
        'form_params' => [
          'grant_type' => 'password',
          'client_id' => $this->config->get(ConstantsInterface::API_CLIENT_ID),
          'client_secret' => $this->config->get(ConstantsInterface::API_CLIENT_SECRET),
          'username' => $this->config->get(ConstantsInterface::API_USERNAME),
          'password' => $this->config->get(ConstantsInterface::API_PASSWORD),
        ],
        'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
      ];
    }

    // Get token ID from engage API.
    try {
      $response = $this->httpClient->request('POST', $this->config->get(ConstantsInterface::API_ENDPOINT_TOKEN_URL), $options);

      if ($response->getStatusCode() === 200) {
        $data = Json::decode($response->getBody());
        return $data['access_token'] ?? NULL;
      }
    }
    catch (GuzzleException $e) {
    }

    return NULL;
  }

  /**
   * Get Engage API token from JWT.
   *
   * @param string $certificate
   *   The certificate file path.
   *
   * @return array
   *   Formatted array for request.
   */
  protected function dsuEngageGetTokenFromJwt(string $certificate):array {
    $claim_set = [
      'iss' => $this->config->get(ConstantsInterface::API_CLIENT_ID),
      'sub' => $this->config->get(ConstantsInterface::API_USERNAME),
      'aud' => $this->config->get(ConstantsInterface::API_AUDIENCE_URL),
      // JWT valid for 60 seconds from the issued time.
      'exp' => time() + 600,
    ];

    // Get a private key.
    $key = openssl_pkey_get_private("file://{$certificate}");
    $jwt = JWT::encode($claim_set, $key, 'RS256');

    return [
      'form_params' => [
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwt,
      ],
      'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
    ];
  }

  /**
   * Send request to engage.
   *
   * @param string $url
   *   The url to send data.
   * @param array $data
   *   The information to send to engage.
   * @param mixed $token
   *   The access token.
   *
   * @return array
   *   Decoded response.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  protected function dsuEngagePost(string $url, array $data, mixed $token):array {
    $options = [
      'method' => 'POST',
      'json' => $data,
      'headers' => [
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer ' . $token,
      ],
    ];

    $response = $this->httpClient->request('POST', $url, $options);

    return $response->getStatusCode() === 200 ? Json::decode(Json::decode($response->getBody())) : [];
  }

}
