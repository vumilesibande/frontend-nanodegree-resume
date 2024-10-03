<?php

namespace Drupal\dsu_engage_rr\Services;

use Drupal\comment\Entity\Comment;

/**
 * Interface for engage_connect service.
 */
interface EngageRRConnectInterface {

  /**
   * Send review.
   *
   * @param \Drupal\comment\Entity\Comment $entity
   *   The entity this comment.
   */
  public function dsuEngageSendReview(Comment $entity): void;

  /**
   * Publish reviews by parent.
   *
   * @param array $reviews
   *   The information to create reviews.
   *
   * @return array
   *   Decoded response.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function dsuEngagePublishReviews(array $reviews):array;

}
