<?php

declare(strict_types=1);

namespace Syriable\UserContext\Exceptions;

use InvalidArgumentException;

final class InvalidLocale extends InvalidArgumentException
{
    public static function make(string $locale): self
    {
        return new self("[{$locale}] is not a valid locale identifier.");
    }
}
