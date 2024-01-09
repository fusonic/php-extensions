<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $type = Kernel::VERSION_ID < 70000 ? 'annotation' : 'attribute';

    $routes->add('app.swagger', '/docs/{area}.json')
        ->methods(['GET'])
        ->controller('nelmio_api_doc.controller.swagger');

    $routes->import('./Controller/', $type);
};
