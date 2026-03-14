<?php

use Spatie\Prometheus\Actions\RenderCollectorsAction;
use Spatie\Prometheus\Http\Middleware\AllowIps;

return [
    'enabled' => true,

    /*
     * The urls that will return metrics.
     */
    'urls' => [
        'default' => 'prometheus',
    ],

    /*
     * Only these IP's will be allowed to visit the above urls.
     * All IP's are allowed when empty.
     */
    'allowed_ips' => env('PROMETHEUS_ALLOWED_IPS')
        ? explode(',', env('PROMETHEUS_ALLOWED_IPS'))
        : [],

    /*
     * This is the default namespace that will be
     * used by all metrics
     */
    'default_namespace' => 'trwl',

    /*
     * The middleware that will be applied to the urls above
     */
    'middleware' => [
        AllowIps::class,
    ],

    /*
     * You can override these classes to customize low-level behaviour of the package.
     * In most cases, you can just use the defaults.
     */
    'actions' => [
        'render_collectors' => RenderCollectorsAction::class,
    ],
];
