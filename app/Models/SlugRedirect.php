<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $old_slug
 * @property int $organization_id
 * @property string $redirects_to_slug
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon|null $expires_at
 * @property-read Organization $organization
 */
final class SlugRedirect extends Model
{
    use BelongsToOrganization;
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'old_slug',
        'organization_id',
        'redirects_to_slug',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }
}
