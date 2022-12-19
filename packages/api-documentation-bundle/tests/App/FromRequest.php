<?php

declare(strict_types=1);

namespace Fusonic\ApiDocumentationBundle\Tests\App;

use Attribute;

#[\Attribute(\Attribute::TARGET_PARAMETER | \Attribute::TARGET_CLASS)]
class FromRequest
{
}
