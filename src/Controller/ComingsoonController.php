<?php

namespace Drupal\comingsoon_mode\Controller;

use Drupal\Core\Cache\CacheableResponse;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Returns responses for Comingsoon routes.
 */
class ComingsoonController extends ControllerBase {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;


  /**
   * The configuration factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new ComingsoonController object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration factory service.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account service.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    AccountInterface $account,
  ) {
    $this->configFactory = $configFactory;
    $this->account = $account;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('current_user')
    );
  }

  /**
   * Builds the response.
   */
  public function build() {
    $config = $this->configFactory->get('comingsoon_mode.settings');
    $comingSoonMode = $config->get('comingsoon_ckeck');
    $cacheTags = ['config:comingsoon_mode.settings'];
    $isAnonymous = $this->account->isAnonymous();

    if (!$comingSoonMode && $isAnonymous) {
      $response = new CacheableResponse(NULL, Response::HTTP_FORBIDDEN);
      $response->getCacheableMetadata()->addCacheTags($cacheTags);
      return $response;
    }

    return [
      '#theme' => 'comingsoon',
      '#cache' => [
        'tags' => $cacheTags,
      ],
    ];
  }

}
