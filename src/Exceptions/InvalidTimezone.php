<?php

declare(strict_types=1);

namespace Syriable\UserContext\Exceptions;

use InvalidArgumentException;

final class InvalidTimezone extends InvalidArgumentException
{
    public static function make(string $timezone): self
    {
        return new self("[{$timezone}] is not a valid IANA timezone identifier.");
    }
}
