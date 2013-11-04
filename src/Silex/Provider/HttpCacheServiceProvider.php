<?php

/*
 * This file is part of the Silex framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Silex\Provider;

use Silex\Application;
use Silex\HttpCache;
use Silex\Api\ServiceProviderInterface;
use Silex\Api\EventListenerProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\HttpCache\Esi;
use Symfony\Component\HttpKernel\HttpCache\Store;
use Symfony\Component\HttpKernel\EventListener\EsiListener;

/**
 * Symfony HttpKernel component Provider for HTTP cache.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class HttpCacheServiceProvider implements ServiceProviderInterface, EventListenerProviderInterface
{
    public function register(Application $app)
    {
        $app['http_cache'] = $app->share(function ($app) {
            $app['http_cache.options'] = array_replace(
                array(
                    'debug' => $app['debug'],
                ), $app['http_cache.options']
            );

            return new HttpCache($app, $app['http_cache.store'], $app['http_cache.esi'], $app['http_cache.options']);
        });

        $app['http_cache.esi'] = $app->share(function ($app) {
            return new Esi();
        });

        $app['http_cache.store'] = $app->share(function ($app) {
            return new Store($app['http_cache.cache_dir']);
        });

        $app['http_cache.esi_listener'] = $app->share(function ($app) {
            return new EsiListener($app['http_cache.esi']);
        });

        $app['http_cache.options'] = array();
    }

    public function subscribe(Application $app, EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addSubscriber($app['http_cache.esi_listener']);
    }

    public function boot(Application $app)
    {
    }
}
