<?php

namespace Drupal\ut_url_shortener\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Shortlink redirect controller.
 *
 * @package Drupal\ut_url_shortener\Controller
 */
class RedirectController extends ControllerBase {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Node storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * RedirectController constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity_type.manager service.
   * @param \Drupal\Core\Entity\EntityStorageInterface $nodeStorage
   *   Node storage.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, EntityStorageInterface $nodeStorage) {
    $this->entityTypeManager = $entityTypeManager;
    $this->nodeStorage = $nodeStorage;
  }

  /**
   * Page callback for handling short url redirect.
   *
   * @param string $shortcode
   *   The shortcode to redirect.
   *
   * @return \Drupal\Core\Routing\TrustedRedirectResponse
   *   The redirect.
   */
  public function content(string $shortcode) {
    $properties = ['title' => $shortcode, 'type' => 'shortened_url'];
    $nodes = $this->nodeStorage->loadByProperties($properties);
    if (empty($nodes)) {
      throw new NotFoundHttpException();
    }
    else {
      $node = reset($nodes);
      $url = $node->get('field_original_url')->uri;
      return new TrustedRedirectResponse($url);
    }
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    $entityTypeManager = $container->get('entity_type.manager');
    $nodeStorage = $entityTypeManager->getStorage('node');
    return new static($entityTypeManager, $nodeStorage);
  }

}
