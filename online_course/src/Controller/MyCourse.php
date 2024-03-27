<?php

namespace Drupal\online_course\Controller;

use Drupal\Core\Url;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;

class MyCourse extends ControllerBase {

  public function courseEnquiryList() {
    // Query the custom table to fetch data.
    if (!$this->currentUser()->isAuthenticated()) {
      // Redirect to the login page.
      $login_url = Url::fromRoute('user.login')->toString();
      return new RedirectResponse($login_url);
    }

    $current_user = \Drupal::currentUser();
    $query = \Drupal::database()->select('user_course_enrollments', 'be');
    $query->fields('be', ['id', 'user_id', 'course_id','enrollment_date']);
    $query->condition('user_id',  $current_user->id(), '=');
    $query->orderBy('enrollment_date', 'DESC');
    $results = $query->execute()->fetchAll();
    $this->courseNodeById($results);
//var_dump($results);die();
    // Return render array.
    return [
      '#theme' => 'online_course_list',
      '#results' => $results,
      '#attached' => [
        'library' => [
          'online_course/custom',
        ],
      ]
    ];
  }

  private function courseNodeById($results)
  {
    foreach ($results as &$result) {
      $course_node = \Drupal\node\Entity\Node::load($result->course_id);
      if ($course_node) {
        $result->course_name = $course_node->getTitle();
      } else {
        $result->course_name = 'Course Not Found';
      }
    }
  }

}
?>
