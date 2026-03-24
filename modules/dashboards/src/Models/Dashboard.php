<?php

declare(strict_types=1);

namespace Modules\Dashboards\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Dashboards\Database\Factories\DashboardFactory;

/**
 * @property int $id
 * @property int $organization_id
 * @property string $name
 * @property array<string, mixed>|null $puck_json
 * @property bool $is_default
 * @property int|null $refresh_interval
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
final class Dashboard extends Model
{
    use BelongsToOrganization;
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'puck_json',
        'is_default',
        'refresh_interval',
    ];

    protected static function newFactory(): DashboardFactory
    {
        return DashboardFactory::new();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'puck_json' => 'array',
            'is_default' => 'boolean',
            'refresh_interval' => 'integer',
        ];
    }
}
