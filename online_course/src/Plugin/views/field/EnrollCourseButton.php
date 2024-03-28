<?php

namespace Drupal\online_course\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Provides a field handler to add an "Enroll Course" button.
 *
 * @ViewsField("enroll_course_button")
 */
class EnrollCourseButton extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $url = \Drupal\Core\Url::fromRoute('entity.node.canonical', ['node' => $values->_entity->id()]);
    $button = [
      '#type' => 'link',
      '#title' => $this->t('Enroll Course'),
      '#url' => $url,
      '#attributes' => [
        'class' => ['button', 'button--primary'],
      ],
    ];

    return render($button);
  }

}
