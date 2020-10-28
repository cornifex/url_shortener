<?php

namespace Drupal\ut_url_shortener;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Shortcode generator.
 */
class Generator {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Drupal\Core\Entity\EntityStorageInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * Generator constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity_type.manager service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
    $this->nodeStorage = $this->entityTypeManager->getStorage('node');
  }

  /**
   * Generates a short code to be used as a short URL.
   *
   * @param int $length
   *   The desired length of the shortcode.
   *
   * @return string
   *   The shortcode.
   */
  public function generate(int $length) : string {
    $chars = array_merge(range(0, 9), range('a', 'z'));
    $shortcode = substr(str_shuffle(implode('', $chars)), 0, $length);
    if ($this->validate($shortcode)) {
      return $shortcode;
    }
    $this->generate($length);
  }

  /**
   * Validates a short code.
   *
   * @param string $shortcode
   *   The shortcode to validate.
   *
   * @return bool
   *   Whether or not the shortcode passes validation.
   */
  public function validate(string $shortcode) : bool {
    $properties = ['type' => 'shortened_url', 'title' => $shortcode];
    $nodes = $this->nodeStorage->loadByProperties($properties);
    return empty($nodes);
  }

}
