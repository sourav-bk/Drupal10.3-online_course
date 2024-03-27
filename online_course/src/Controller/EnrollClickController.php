<?php

namespace Drupal\online_course\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

/**
 * Controller for handling enroll button clicks.
 */
/**
 * Controller for handling enroll button clicks.
 */
class EnrollClickController extends ControllerBase
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
   * Increment the total count of enrollments for the course and enroll the user.
   *
   * @param int $node
   *   The course node ID.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response to the course page.
   */
  public function incrementCount($node)
  {
    // Load the node object from the provided node ID.
    $node = Node::load($node);

    if ($node instanceof NodeInterface) {
      // Check if the node has the 'field_total_count' field.
      if ($node->hasField('field_total_count')) {
        // Check if the current user is already enrolled in the course.
        $user = \Drupal::currentUser();
        $enrolled = $this->isUserEnrolled($user->id(), $node->id());

        if (!$enrolled) {
          // Increment the value of the 'field_total_count' field.
          $field = $node->get('field_total_count');
          $value = $field->value + 1;
          $node->set('field_total_count', $value);
          //$node->set('field_total_users', $user->id());
          $node->field_total_users->appendItem($user->id());

          $node->save();

          // Enroll the current user in the course.
          $connection = \Drupal::database();
          $connection->insert('user_course_enrollments')
            ->fields([
              'user_id' => $user->id(),
              'course_id' => $node->id(),
            ])
            ->execute();
        }

        // Redirect the user back to the course page.
        $url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()]);
        return new RedirectResponse($url->toString());
      } else {
        return new JsonResponse(['error' => 'Field "field_total_count" not found on the node.']);
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
    $query = \Drupal::database()->select('user_course_enrollments', 'uce')
      ->fields('uce', ['id'])
      ->condition('user_id', $user_id)
      ->condition('course_id', $course_id)
      ->execute()
      ->fetchField();

    return !empty($query);
  }


}
