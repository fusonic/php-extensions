<?php

declare(strict_types=1);

namespace Fusonic\AssertExtensions\Validation;

use Symfony\Component\Validator\Constraints as Assert;

class SymfonyConstraintMapping
{
    public const MAPPING = [
        Assertion::INVALID_FLOAT => Assert\Type::INVALID_TYPE_ERROR,
        Assertion::INVALID_INTEGER => Assert\Type::INVALID_TYPE_ERROR,
        Assertion::INVALID_DIGIT => Assert\Type::INVALID_TYPE_ERROR,
    ];
    public const INVALID_INTEGERISH = 12;
    public const INVALID_BOOLEAN = 13;
    public const VALUE_EMPTY = 14;
    public const VALUE_NULL = 15;
    public const VALUE_NOT_NULL = 25;
    public const INVALID_STRING = 16;
    public const INVALID_REGEX = 17;
    public const INVALID_MIN_LENGTH = 18;
    public const INVALID_MAX_LENGTH = 19;
    public const INVALID_STRING_START = 20;
    public const INVALID_STRING_CONTAINS = 21;
    public const INVALID_CHOICE = 22;
    public const INVALID_NUMERIC = 23;
    public const INVALID_ARRAY = 24;
    public const INVALID_KEY_EXISTS = 26;
    public const INVALID_NOT_BLANK = 27;
    public const INVALID_INSTANCE_OF = 28;
    public const INVALID_SUBCLASS_OF = 29;
    public const INVALID_RANGE = 30;
    public const INVALID_ALNUM = 31;
    public const INVALID_TRUE = 32;
    public const INVALID_EQ = 33;
    public const INVALID_SAME = 34;
    public const INVALID_MIN = 35;
    public const INVALID_MAX = 36;
    public const INVALID_LENGTH = 37;
    public const INVALID_FALSE = 38;
    public const INVALID_STRING_END = 39;
    public const INVALID_UUID = 40;
    public const INVALID_COUNT = 41;
    public const INVALID_NOT_EQ = 42;
    public const INVALID_NOT_SAME = 43;
    public const INVALID_TRAVERSABLE = 44;
    public const INVALID_ARRAY_ACCESSIBLE = 45;
    public const INVALID_KEY_ISSET = 46;
    public const INVALID_VALUE_IN_ARRAY = 47;
    public const INVALID_E164 = 48;
    public const INVALID_BASE64 = 49;
    public const INVALID_NOT_REGEX = 50;
    public const INVALID_DIRECTORY = 101;
    public const INVALID_FILE = 102;
    public const INVALID_READABLE = 103;
    public const INVALID_WRITEABLE = 104;
    public const INVALID_CLASS = 105;
    public const INVALID_INTERFACE = 106;
    public const INVALID_FILE_NOT_EXISTS = 107;
    public const INVALID_EMAIL = 201;
    public const INTERFACE_NOT_IMPLEMENTED = 202;
    public const INVALID_URL = 203;
    public const INVALID_NOT_INSTANCE_OF = 204;
    public const VALUE_NOT_EMPTY = 205;
    public const INVALID_JSON_STRING = 206;
    public const INVALID_OBJECT = 207;
    public const INVALID_METHOD = 208;
    public const INVALID_SCALAR = 209;
    public const INVALID_LESS = 210;
    public const INVALID_LESS_OR_EQUAL = 211;
    public const INVALID_GREATER = 212;
    public const INVALID_GREATER_OR_EQUAL = 213;
    public const INVALID_DATE = 214;
    public const INVALID_CALLABLE = 215;
    public const INVALID_KEY_NOT_EXISTS = 216;
    public const INVALID_SATISFY = 217;
    public const INVALID_IP = 218;
    public const INVALID_BETWEEN = 219;
    public const INVALID_BETWEEN_EXCLUSIVE = 220;
    public const INVALID_EXTENSION = 222;
    public const INVALID_CONSTANT = 221;
    public const INVALID_VERSION = 223;
    public const INVALID_PROPERTY = 224;
    public const INVALID_RESOURCE = 225;
    public const INVALID_COUNTABLE = 226;
    public const INVALID_MIN_COUNT = 227;
    public const INVALID_MAX_COUNT = 228;
    public const INVALID_STRING_NOT_CONTAINS = 229;
    public const INVALID_UNIQUE_VALUES = 230;
}
