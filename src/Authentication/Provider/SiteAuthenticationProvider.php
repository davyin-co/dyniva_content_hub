<?php

namespace Drupal\dyniva_content_hub\Authentication\Provider;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Authentication\AuthenticationProviderInterface;

/**
 * @internal
 */
class SiteAuthenticationProvider implements AuthenticationProviderInterface{

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a HTTP basic authentication provider object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(Request $request) {
    // Check for the presence of the token.
    return $this->hasTokenValue($request);
  }

  /**
   * {@inheritdoc}
   */
  public static function hasTokenValue(Request $request) {
    // Check the header. See: http://tools.ietf.org/html/rfc6750#section-2.1
    $auth_header = trim($request->headers->get('Authorization', '', TRUE));
    // $request->headers->set('Authorization-Result', strpos($auth_header, 'Uuid '));
    return strpos($auth_header, 'Uuid ') !== FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function authenticate(Request $request) {

    $auth_header = trim($request->headers->get('Authorization', '', TRUE));
    $parts = explode(' ',$auth_header);
    $uuid = $parts[1];
    
    $site = \Drupal::entityManager()->loadEntityByUuid('node', $uuid);
    if($site){
      $request->headers->set('X-Site-ID', $site->uuid());
      return $site->content_author->entity;
    }

    return NULL;
  }

}
