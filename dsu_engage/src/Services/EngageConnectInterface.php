<?php

namespace Drupal\dsu_engage\Services;

/**
 * Interface for engage_connect service.
 */
interface EngageConnectInterface {

  /**
   * Send the engage request.
   *
   * @param array $data
   *   The information to send to engage.
   * @param string|null $url
   *   The url to send data.
   *
   * @return array
   *   The response data.
   *
   * @throws \Drupal\dsu_engage\Exception\RequestException
   * @throws \Drupal\dsu_engage\Exception\AccessTokenRequestException
   */
  public function request(array $data, ?string $url): array;

}
