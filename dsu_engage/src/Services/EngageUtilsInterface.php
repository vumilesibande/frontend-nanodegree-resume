<?php

namespace Drupal\dsu_engage\Services;

/**
 * Interface for engage_utils service.
 */
interface EngageUtilsInterface {

  /**
   * Get values for given dataset name.
   *
   * @param string $dataset
   *   The dataset name.
   *
   * @return array
   *   The array values.
   */
  public function getValues(string $dataset): array;

  /**
   * Get value from key.
   *
   * @param string $key
   *   The key for search.
   * @param string $dataset
   *   The dataset name.
   *
   * @return mixed
   *   The value.
   */
  public function getValueFromKey(string $key, string $dataset): mixed;

}
