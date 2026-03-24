<?php

declare(strict_types=1);

namespace Modules\Reports\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Reports\Database\Factories\ReportFactory;
use Modules\Reports\Enums\OutputFormat;

/**
 * @property int $id
 * @property int $organization_id
 * @property string $name
 * @property array<string, mixed>|null $puck_json
 * @property string|null $schedule
 * @property OutputFormat $output_format
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
final class Report extends Model
{
    use BelongsToOrganization;
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'puck_json',
        'schedule',
        'output_format',
    ];

    /**
     * @return HasMany<ReportOutput, $this>
     */
    public function outputs(): HasMany
    {
        return $this->hasMany(ReportOutput::class)->latest();
    }

    protected static function newFactory(): ReportFactory
    {
        return ReportFactory::new();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'puck_json' => 'array',
            'output_format' => OutputFormat::class,
        ];
    }
}
