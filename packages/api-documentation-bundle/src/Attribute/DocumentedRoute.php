<?php

/*
 * Copyright (c) Fusonic GmbH. All rights reserved.
 * Licensed under the MIT License. See LICENSE file in the project root for license information.
 */

declare(strict_types=1);

namespace Fusonic\ApiDocumentationBundle\Attribute;

use Symfony\Component\Routing\Annotation\Route;

#[\Attribute(\Attribute::TARGET_METHOD)]
class DocumentedRoute extends Route
{
    /**
     * @param class-string|string|null $input
     * @param class-string|string|null $output
     * @param string[]                 $requirements
     * @param string[]|string          $methods
     * @param string[]|string          $schemes
     */
    public function __construct(
        array|string|null $path = null,
        ?string $name = null,
        array $requirements = [],
        array $options = [],
        array $defaults = [],
        ?string $host = null,
        array|string $methods = [],
        array|string $schemes = [],
        ?string $condition = null,
        ?int $priority = null,
        ?string $locale = null,
        ?string $format = null,
        ?bool $utf8 = null,
        ?bool $stateless = null,
        ?string $env = null,
        private readonly ?string $input = null,
        private readonly ?string $output = null,
        private readonly ?bool $outputIsCollection = null,
        private readonly ?int $statusCode = null,
        private readonly ?string $description = null
    ) {
        parent::__construct(
            path: $path,
            name: $name,
            requirements: $requirements,
            options: $options,
            defaults: $defaults,
            host: $host,
            methods: $methods,
            schemes: $schemes,
            condition: $condition,
            priority: $priority,
            locale: $locale,
            format: $format,
            utf8: $utf8,
            stateless: $stateless,
            env: $env
        );
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return class-string|string|null
     */
    public function getInput(): ?string
    {
        return $this->input;
    }

    /**
     * @return class-string|string|null
     */
    public function getOutput(): ?string
    {
        return $this->output;
    }

    public function getOutputIsCollection(): ?bool
    {
        return $this->outputIsCollection;
    }
}
