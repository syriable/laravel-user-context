<?php

declare(strict_types=1);

namespace Syriable\UserContext\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Syriable\UserContext\Data\ContextSnapshot;
use Syriable\UserContext\Models\UserContext;

/**
 * JSON representation of a user's context. The shape is part of the
 * package's public contract — changing it is a breaking change.
 *
 * @property-read UserContext $resource
 */
final class UserContextResource extends JsonResource
{
    /**
     * Return a flat object (no "data" envelope) — the response shape is
     * documented as top-level keys.
     *
     * @var string|null
     */
    public static $wrap = null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ContextSnapshot::fromContext($this->resource)->toArray();
    }
}
