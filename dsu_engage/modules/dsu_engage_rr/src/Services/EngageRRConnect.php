<?php

namespace Drupal\dsu_engage_rr\Services;

use Drupal\comment\CommentStorageInterface;
use Drupal\comment\Entity\Comment;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\dsu_core\Services\NoticeInterface;
use Drupal\dsu_engage\Exception\AccessTokenRequestException;
use Drupal\dsu_engage\Exception\RequestException;
use Drupal\dsu_engage\Services\EngageConnectInterface;
use Drupal\dsu_ratings_reviews\DsuRatingsReviewsConstants;
use Drupal\user\UserStorageInterface;
use GuzzleHttp\ClientInterface;

/**
 * Class for engage_connect service.
 */
class EngageRRConnect implements EngageRRConnectInterface {
  use StringTranslationTrait;

  /**
   * The dsu_engage_rr.settings config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected ImmutableConfig $configRR;

  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  protected UserStorageInterface $userStorage;

  /**
   * The commment storage.
   *
   * @var \Drupal\comment\CommentStorageInterface
   */
  protected CommentStorageInterface $commentStorage;

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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\dsu_engage\Services\EngageConnectInterface $engageConnect
   *   The engage connect service.
   * @param \Drupal\dsu_core\Services\NoticeInterface $notice
   *   The notice service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    protected readonly ClientInterface $httpClient,
    protected readonly ModuleHandlerInterface $moduleHandler,
    protected LanguageManagerInterface $languageManager,
    protected LoggerChannelInterface $logger,
    EntityTypeManagerInterface $entityTypeManager,
    protected EngageConnectInterface $engageConnect,
    protected NoticeInterface $notice,
    protected TimeInterface $time,
  ) {
    $this->configRR = $config_factory->get('dsu_engage_rr.settings');
    $this->userStorage = $entityTypeManager->getStorage('user');
    $this->commentStorage = $entityTypeManager->getStorage('comment');
  }

  /**
   * Send review.
   *
   * @param \Drupal\comment\Entity\Comment $entity
   *   The entity this comment.
   */
  public function dsuEngageSendReview(Comment $entity): void {

    // Prepare attachments.
    $attachedFiles = [];
    /** @var \Drupal\comment\CommentInterface $entity */
    if (!$entity->get('field_dsu_images')->isEmpty()) {
      foreach ($entity->get('field_dsu_images') as $field) {
        /** @var \Drupal\file\FileInterface $file */
        if (($file = $field->entity?->get('field_media_image')?->entity)
          && ($file_contents = file_get_contents($file->getFileUri()))) {
          $attachedFiles[] = [
            'attachmentName' => $file->getFilename(),
            'attachmentBody' => base64_encode($file_contents),
          ];
        }
      }
    }
    // Prepare and Send data for API.
    $data = [
      'JSONRequestId' => "ln-rr-{$entity->uuid()}",
      'subject' => $entity->get('subject')->value,
      'description' => $entity->get('field_dsu_comment')->value,
      'productDescription' => $entity->get('entity_id')->entity?->label(),
      'consumerLocation' => $entity->get('entity_id')->entity?->bundle(),
      'email' => $entity->get('uid')->entity?->get('mail')->value,
      'uniqueIdentifier' => $entity->uuid(),
      'rrOrigin' => "Drupal",
      'rating' => $entity->get('field_dsu_ratings')->rating / 20,
      'recommendation' => (boolean) $entity->get('field_dsu_recommend')->value,
      'createdDate' => date('Y-m-d H:m:s', $this->time->getCurrentTime()),
      'attachments' => $attachedFiles,
    ];

    try {
      $response = $this->engageConnect->request($data);
      $this->logger->notice($this->t('Your ticket is created. Your contact number is: @ticket', ['@ticket' => $response['caseNumber']]));
    }
    catch (AccessTokenRequestException $e) {
      $this->notice->sendNotice(
        $this->t('An error occurred while submitting the R&R integration with Engage Contact: The access token is wrong. Your request was not sent to our contact system.'),
        Url::fromRoute('dsu_engage.admin_settings_form'),
      );
    }
    catch (RequestException $e) {
      $this->notice->sendNotice(
        $this->t('An error occurred while submitting the R&R integration with Engage Contact: @error_message', [
          '@error_message' => $e->getMessage(),
        ]),
        Url::fromRoute('<current>'),
      );
    }
  }

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
  public function dsuEngagePublishReviews(array $reviews):array {
    $errors = [];
    foreach ($reviews as $review) {
      $foundParent = FALSE;

      $allComments = $this->commentStorage
        ->loadByProperties(['comment_type' => DsuRatingsReviewsConstants::COMMENT_TYPE]);
      foreach ($allComments as $parentReview) {

        if ($parentReview->uuid() == $review['parentUniqueIdentifier']) {
          $foundParent = TRUE;

          $this->createChildComment($review, $parentReview);

          if (!$parentReview->isPublished()) {
            $parentReview->setPublished();
            $parentReview->save();
          }
          break;
        }
      }
      if (!$foundParent) {
        $errors[] = $review['uniqueIdentifier'];
      }
    }

    return $errors;
  }

  /**
   * Create a child comment.
   *
   * @param array $review
   *   The review data.
   * @param \Drupal\comment\Entity\Comment $parentReview
   *   The parent review object.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createChildComment(array $review, Comment $parentReview): void {

    $comment = $this->commentStorage->create([
      'entity_type' => $parentReview->get("entity_type")->value,
      'entity_id' => $parentReview->get('entity_id')?->entity->id(),
      'field_name' => $parentReview->get("field_name")->value,
      'uid' => $this->userStorage->load($this->configRR->get('drupal_user'))?->id(),
      'mail' => $this->userStorage->load($this->configRR->get('drupal_user'))?->getEmail(),
      'langcode' => $this->userStorage->load($this->configRR->get('drupal_user'))?->language()->getId(),
      'cid' => NULL,
      'pid' => $parentReview->id(),
      'comment_type' => DsuRatingsReviewsConstants::COMMENT_TYPE,
      'subject' => $this->configRR->get('subject_default') ?? $this->t('Agent response'),
      'field_display_name' => $this->configRR->get('subject_default') ?? $this->t('Agent response'),
      'field_dsu_comment' => $review['description'] ?? '',
      'created' => \DateTime::createFromFormat('Y-m-d H:i:s', $review['createdDate'])->getTimestamp(),
      'status' => 1,
    ]);

    $comment->save();
  }

}
