<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\FrameworkBundle\Port\Http;

use Fusonic\FrameworkBundle\Application\Messenger\Bus\CommandBusInterface;
use Fusonic\FrameworkBundle\Application\Messenger\Bus\QueryBusInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\SerializerInterface;

abstract class BaseController extends AbstractController
{
    use CommandAndQueryTrait;
    use ControllerResponseTrait;

    public function __construct(
        #[Autowire(lazy: true)]
        protected readonly SerializerInterface $serializer,

        #[Autowire(lazy: true)]
        protected readonly QueryBusInterface $queryBus,

        #[Autowire(lazy: true)]
        protected readonly CommandBusInterface $commandBus,
    ) {
    }
}
