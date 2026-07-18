<?php

declare(strict_types=1);

namespace Syriable\UserContext\Enums;

/**
 * Provenance of a detected context value. A value set explicitly by the
 * user always wins over values detected from the IP address or headers.
 */
enum ContextSource: string
{
    case Ip = 'ip';
    case Header = 'header';
    case User = 'user';
}
