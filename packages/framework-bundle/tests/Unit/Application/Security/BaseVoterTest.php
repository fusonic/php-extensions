<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\FrameworkBundle\Tests\Unit\Application\Security;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

final class BaseVoterTest extends TestCase
{
    public function testSupportsAttributeReturnsTrueForKnownAttribute(): void
    {
        // arrange
        $voter = new TestVoter(self::createStub(AccessDecisionManagerInterface::class));

        // act
        $result = $voter->supportsAttribute(TestVoter::ATTRIBUTE_A);

        // assert
        self::assertTrue($result);
    }

    public function testSupportsAttributeReturnsFalseForUnknownAttribute(): void
    {
        // arrange
        $voter = new TestVoter(self::createStub(AccessDecisionManagerInterface::class));

        // act
        $result = $voter->supportsAttribute('attribute.unknown');

        // assert
        self::assertFalse($result);
    }

    public function testSupportsReturnsTrueForKnownAttributeSubjectCombination(): void
    {
        // arrange
        $voter = new TestVoter(self::createStub(AccessDecisionManagerInterface::class));

        // act
        $result = $voter->supports(TestVoter::ATTRIBUTE_B, null); // @phpstan-ignore method.alreadyNarrowedType

        // assert
        self::assertTrue($result);
    }

    public function testSupportsReturnsFalseForUnknownAttributeSubjectCombination(): void
    {
        // arrange
        $voter = new TestVoter(self::createStub(AccessDecisionManagerInterface::class));

        // act
        $result = $voter->supports('attribute.unknown', null); // @phpstan-ignore method.alreadyNarrowedType

        // assert
        self::assertFalse($result);
    }

    public function testIsGrantedDelegatesToAccessDecisionManager(): void
    {
        // arrange
        $token = self::createStub(TokenInterface::class);
        $accessDecisionManager = $this->createMock(AccessDecisionManagerInterface::class);

        $accessDecisionManager
            ->expects($this->once())
            ->method('decide')
            ->with($token, ['ROLE_ADMIN'])
            ->willReturn(true);

        $voter = new TestVoter($accessDecisionManager);

        // act
        $result = $voter->isGranted($token, 'ROLE_ADMIN');

        // assert
        self::assertTrue($result);
    }

    public function testIsGrantedExtractsValueFromBackedEnum(): void
    {
        // arrange
        $token = self::createStub(TokenInterface::class);
        $accessDecisionManager = $this->createMock(AccessDecisionManagerInterface::class);

        $accessDecisionManager
            ->expects($this->once())
            ->method('decide')
            ->with($token, ['ROLE_ADMIN'])
            ->willReturn(true);

        $voter = new TestVoter($accessDecisionManager);

        // act
        $result = $voter->isGranted($token, TestRole::Admin);

        // assert
        self::assertTrue($result);
    }

    public function testIsGrantedReturnsFalseWhenAccessDecisionManagerDenies(): void
    {
        // arrange
        $token = self::createStub(TokenInterface::class);
        $accessDecisionManager = $this->createMock(AccessDecisionManagerInterface::class);

        $accessDecisionManager
            ->expects($this->once())
            ->method('decide')
            ->with($token, ['ROLE_ADMIN'])
            ->willReturn(false);

        $voter = new TestVoter($accessDecisionManager);

        // act
        $result = $voter->isGranted($token, 'ROLE_ADMIN');

        // assert
        self::assertFalse($result);
    }
}
