<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\ApiDocumentationBundle\Exception;

final class DuplicateAttributesException extends \LogicException
{
    public function __construct(string $attributeClass)
    {
        parent::__construct(\sprintf('Attribute `%s` can only be set once.', $attributeClass));
    }
}
