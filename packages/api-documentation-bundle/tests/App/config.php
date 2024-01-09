<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

use Fusonic\ApiDocumentationBundle\Tests\App\FromRequest;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('framework', [
        'test' => true,
        'http_method_override' => false,
        'router' => [
            'utf8' => true,
            'resource' => '%kernel.project_dir%/tests/App/routes.php',
        ],
        'property_info' => [
            'enabled' => true,
        ],
    ]);

    $containerConfigurator->extension('fusonic_api_documentation', [
        'request_object_class' => FromRequest::class,
    ]);

    $containerConfigurator->extension('nelmio_api_doc', [
        'documentation' => [
            'info' => [
                'title' => 'My App',
                'description' => 'This is an awesome app!',
                'version' => '1.0.0',
            ],
        ],
    ]);

    $services = $containerConfigurator->services();

    $services->load(
        'Fusonic\ApiDocumentationBundle\Tests\App\Controller\\',
        '/%kernel.project_dir%/tests/App/Controller'
    )
        ->tag('controller.service_arguments');
};
