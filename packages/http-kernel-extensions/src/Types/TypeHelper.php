<?php

namespace Fusonic\HttpKernelExtensions\Types;

class TypeHelper
{
    public static function isUnionType(string $type): bool
    {
        return str_starts_with($type, '(') && str_ends_with($type, ')');
    }
}
