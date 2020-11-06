<?php

namespace Drupal\url_shortener\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Url shortener form.
 */
class UrlShortenerForm extends FormBase {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * GuzzleHttp\Client definition.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * Drupal\url_shortener\Generator definition.
   *
   * @var \Drupal\url_shortener\Generator
   */
  protected $shortCodeGenerator;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->httpClient = $container->get('http_client');
    $instance->shortCodeGenerator = $container->get('url_shortener.generator');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'url_shortener_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['url'] = [
      '#type' => 'url',
      '#title' => $this->t('URL'),
      '#description' => $this->t('Enter an external URL to shorten.'),
      '#weight' => '0',
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $url = $form_state->getValue('url');
    try {
      $request = $this->httpClient->get($url);
      if ($request->getStatusCode() !== 200) {
        $form_state->setErrorByName('url', $this->t('The entered URL can not be reached.'));
      }
    }
    catch (RequestException $e) {
      $form_state->setErrorByName('url', $this->t('The entered URL can not be reached.'));
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $url = $form_state->getValue('url');
    $shortcode = $this->shortCodeGenerator->generate(9);
    $short_url = Url::fromUserInput('/r/' . $shortcode, ['absolute' => TRUE]);
    $node_storage = $this->entityTypeManager->getStorage('node');
    $node = $node_storage->create(['type' => 'shortened_url']);
    $node->set('title', $shortcode);
    $node->set('field_original_url', $url);
    $node->set('field_short_url', $short_url->toString());
    $node->save();
    $form_state->setRedirect('entity.node.canonical', ['node' => $node->id()]);
  }

}
