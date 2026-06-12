<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\HttpKernelBundle\Exception;

/**
 * Allows attaching structured context to an exception. Useful for exceptions returned in an API response.
 *
 * Can be used with the api-documentation-bundle to automatically document the response body shape.
 * @see https://github.com/fusonic/php-extensions/tree/master/packages/api-documentation-bundle
 */
interface ContextAwareExceptionInterface extends \Throwable
{
    public function getContext(): mixed;
}
