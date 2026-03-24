<?php

declare(strict_types=1);

namespace Modules\Reports\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Modules\Reports\Database\Factories\ReportOutputFactory;

/**
 * @property int $id
 * @property int $report_id
 * @property string $format
 * @property string $disk
 * @property string $path
 * @property int $size_bytes
 * @property bool $is_scheduled
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
final class ReportOutput extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'report_id',
        'format',
        'disk',
        'path',
        'size_bytes',
        'is_scheduled',
    ];

    /**
     * @return BelongsTo<Report, $this>
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function downloadUrl(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function fullPath(): string
    {
        return Storage::disk($this->disk)->path($this->path);
    }

    protected static function newFactory(): ReportOutputFactory
    {
        return ReportOutputFactory::new();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'is_scheduled' => 'boolean',
        ];
    }
}
