<?php

use CisionBlock\Backend\Backend;
use CisionBlock\Cision\Service;
use CisionBlock\DI\Container;
use CisionBlock\Frontend\Frontend;
use CisionBlock\GuzzleHttp\Client;
use CisionBlock\Settings\Settings;
use CisionBlock\Widget\Widget;

return [
    Settings::class => function (Container $c) {
        return new Settings(Frontend::SETTINGS_NAME);
    },
    Backend::class => function (Container $c) {
        return new Backend(
            $c,
            $c->get(Settings::class),
            $c->get(Widget::class)
        );
    },
    Frontend::class => function (Container $c) {
        return new Frontend(
            $c,
            $c->get(Settings::class),
            $c->get(Widget::class)
        );
    },
    Client::class => function (Container $c) {
        return new Client([
            'headers' => [
                'User-agent' => Service::USER_AGENT,
                'Content-type' => 'application/json',
            ],
        ]);
    },
    Service::class => function (Container $c) {
        return new Service(
            $c,
            $c->get(Settings::class),
            $c->get(Client::class)
        );
    },
];
