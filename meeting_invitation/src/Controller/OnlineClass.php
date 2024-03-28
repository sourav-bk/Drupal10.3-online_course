<?php

namespace Drupal\meeting_invitation\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;


/**
 * Controller for handling enroll button clicks.
 */
/**
 * Controller for handling enroll button clicks.
 */
class OnlineClass extends ControllerBase
{

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs an EnrollClickController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager)
  {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Redirect the user to the meeting URL.
   *
   * @param int $node
   *   The course node ID.
   *
   * @return \Drupal\Core\Routing\TrustedRedirectResponse
   *   A redirect response to the meeting URL.
   */
  public function onlineCourse($node) {
    // Load the node object from the provided node ID.
    $node = Node::load($node);

    if ($node instanceof NodeInterface) {
      // Check if the current user is already enrolled in the course.
      $user = \Drupal::currentUser();
      $enrolled = $this->isUserEnrolled($user->id(), $node->id());

      if (!$enrolled) {
        $node->field_users->appendItem($user->id());
        $node->save();

        // Enroll the current user in the course.
        $connection = \Drupal::database();
        $connection->insert('meeting_invitation')
          ->fields([
            'user_id' => $user->id(),
            'course_id' => $node->id(),
          ])
          ->execute();
      }

      // Redirect the user to the meeting URL.
      $meeting_url = $node->get('field_meeting_url')->uri;
      if ($meeting_url) {
        return new TrustedRedirectResponse($meeting_url);
      }
      else {
        return new JsonResponse(['error' => 'Meeting URL not found.']);
      }
    } else {
      return new JsonResponse(['error' => 'Node not found.']);
    }
  }

  /**
   * Check if the user is enrolled in a course.
   *
   * @param int $user_id
   *   The ID of the user.
   * @param int $course_id
   *   The ID of the course.
   *
   * @return bool
   *   TRUE if the user is enrolled in the course, FALSE otherwise.
   */
  private function isUserEnrolled($user_id, $course_id) {
    // Query the database to check if the user is enrolled in the course.
    $query = \Drupal::database()->select('meeting_invitation', 'uce')
      ->fields('uce', ['id'])
      ->condition('user_id', $user_id)
      ->condition('course_id', $course_id)
      ->execute()
      ->fetchField();

    return !empty($query);
  }


}
