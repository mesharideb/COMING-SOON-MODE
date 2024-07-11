<?php

namespace Drupal\comingsoon_mode\EventSubscriber;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\RedirectDestinationInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Subscribe to KernelEvents::RESPONSE events.
 */
class RedirectComingSoonSubscriber implements EventSubscriberInterface {

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;
  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The redirection service.
   *
   * @var \Drupal\Core\Routing\RedirectDestinationInterface
   */
  protected $destinationHelper;

  /**
   * Constructs a RedirectComingSoonSubscriber object.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Routing\RedirectDestinationInterface $destination_helper
   *   The destination helper.
   * @param \Drupal\Core\Path\CurrentPathStack $current_path
   *   The current path for the current request.
   */
  public function __construct(
    RequestStack $requestStack,
    AccountInterface $account,
    ConfigFactoryInterface $config_factory,
    RedirectDestinationInterface $destination_helper
  ) {
    $this->requestStack = $requestStack;
    $this->account = $account;
    $this->configFactory = $config_factory;
    $this->destinationHelper = $destination_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array{
    // Set priority to be -1 to fire after the destination parameter
    // is processed, so we can ignore if the destination is set to the same page
    // we're accessing.
    return [
      KernelEvents::REQUEST => ['redirectToComingSoonEvent', -1],
    ];
  }

  /**
   * This method is called whenever the KernelEvents::REQUEST event is
   * dispatched.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The Request event object.
   */
  public function redirectToComingSoonEvent(RequestEvent $event) {
    $request = $this->requestStack->getCurrentRequest();
    $currentRoute = $request->attributes->get('_route');
    $isAnonymous = $this->account->isAnonymous();
    $haveAccess = $this->account->hasPermission('access website in comingsoon mode');
    $config = $this->configFactory->get('comingsoon_mode.settings');
    $comingSoonCheck = $config->get('comingsoon_ckeck') ?? 0;
    $accessRoutes = ['user.login', 'user.pass', 'user.reset.login', 'user.reset.form', 'user.logout'];

    if ($comingSoonCheck == 1) {
      if (($isAnonymous || !$haveAccess) && !in_array($currentRoute, $accessRoutes)) {
        $url = Url::fromRoute('comingsoon.page');
        // Add the current query parameters to the redirect URL.
        $query = $request->query->all();
        $url->setOption('query', $query);
        $url->setAbsolute(TRUE);

        // Create a RedirectResponse.
        $response = new RedirectResponse($url->toString());

        // Set cache tags.
        $cacheableMetadata = new CacheableMetadata();
        $cacheableMetadata->addCacheTags(['config:comingsoon_mode.settings']);

        // Avoid infinite redirection.
        if ($currentRoute != 'comingsoon.page') {
          $request = $event->getRequest();
          $request->attributes->set('_cacheable_metadata', $cacheableMetadata);
          $event->setResponse($response);
        }

        return;
      }

      return;
    }
  }

}
