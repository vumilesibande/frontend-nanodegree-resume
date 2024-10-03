<?php

/**
 * @file
 * Lightnest Engage Contact Us Block API documentation.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Perform alterations on the data before sending to the API.
 *
 * @param array $data
 *   Array of data to be sent to the API.
 */
function hook_dsu_engage_data_alter(array &$data): void {
  if (empty($data['market'])) {
    $data['market'] = 'not specified';
  }
}

/**
 * @} End of "addtogroup hooks".
 */
