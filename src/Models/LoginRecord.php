<?php

declare(strict_types=1);

namespace Syriable\UserContext\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Syriable\UserContext\Database\Factories\LoginRecordFactory;

/**
 * Append-only login ledger: one row per login, with the logout timestamp
 * filled in when the session ends. Not final on purpose — swap it via
 * the `user-context.models.login_record` config key.
 *
 * @property int $id
 * @property string $user_type
 * @property int $user_id
 * @property string|null $ip_address
 * @property string|null $country_code
 * @property string|null $city
 * @property string|null $timezone
 * @property string|null $user_agent
 * @property CarbonImmutable $logged_in_at
 * @property CarbonImmutable|null $logged_out_at
 * @property-read Model|null $user
 */
class LoginRecord extends Model
{
    /** @use HasFactory<LoginRecordFactory> */
    use HasFactory;

    protected $table = 'user_login_records';

    protected $guarded = [];

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'logged_in_at' => 'immutable_datetime',
            'logged_out_at' => 'immutable_datetime',
        ];
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function user(): MorphTo
    {
        return $this->morphTo();
    }

    protected static function newFactory(): LoginRecordFactory
    {
        return LoginRecordFactory::new();
    }
}
