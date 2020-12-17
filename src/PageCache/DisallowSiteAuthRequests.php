<?php

namespace Drupal\dyniva_content_hub\PageCache;

use Drupal\Core\PageCache\RequestPolicyInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\dyniva_content_hub\Authentication\Provider\SiteAuthenticationProvider;

/**
 * @internal
 */
class DisallowSiteAuthRequests implements RequestPolicyInterface {

  /**
   * {@inheritdoc}
   */
  public function check(Request $request) {
    return SiteAuthenticationProvider::hasTokenValue($request) ? self::DENY : NULL;
  }

}
