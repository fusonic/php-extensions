<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\FrameworkBundle\Tests\Unit\Application\Security;

use Fusonic\FrameworkBundle\Application\Security\BaseVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

/**
 * @extends BaseVoter<null>
 */
final class TestVoter extends BaseVoter
{
    public const ATTRIBUTE_A = 'attribute.a';
    public const ATTRIBUTE_B = 'attribute.b';

    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token,
        ?Vote $vote = null,
    ): bool {
        return true;
    }

    protected function getAvailableAttributes(): array
    {
        return [self::ATTRIBUTE_A, self::ATTRIBUTE_B];
    }

    public function supports(string $attribute, mixed $subject): bool
    {
        return parent::supports($attribute, $subject);
    }

    public function isGranted(TokenInterface $token, string|\BackedEnum $role): bool
    {
        return parent::isGranted($token, $role);
    }
}
