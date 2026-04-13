<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\FrameworkBundle\Application\Security;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @template TSubject
 *
 * @template-extends Voter<string, TSubject>
 */
abstract class BaseVoter extends Voter
{
    public function __construct(
        #[Autowire(lazy: true)]
        protected AccessDecisionManagerInterface $accessDecisionManager,
    ) {
    }

    public function supportsAttribute(string $attribute): bool
    {
        return \in_array($attribute, $this->getAvailableAttributes(), true);
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $this->supportsAttribute($attribute);
    }

    protected function isGranted(TokenInterface $token, string|\BackedEnum $role): bool
    {
        $role = $role instanceof \BackedEnum ? $role->value : $role;

        return $this->accessDecisionManager->decide($token, [$role]);
    }

    /**
     * @return string[]
     */
    abstract protected function getAvailableAttributes(): array;
}
