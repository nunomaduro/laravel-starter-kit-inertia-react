<?php

declare(strict_types=1);

namespace Modules\BotStudio\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Modules\BotStudio\Database\Factories\AgentKnowledgeFileFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property int $id
 * @property int $agent_definition_id
 * @property int $organization_id
 * @property string $filename
 * @property string|null $mime_type
 * @property int|null $file_size
 * @property int|null $media_id
 * @property string $status
 * @property int $chunk_count
 * @property string|null $error_message
 * @property \Carbon\Carbon|null $processed_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read AgentDefinition $agentDefinition
 * @property-read \App\Models\Organization $organization
 */
final class AgentKnowledgeFile extends Model implements HasMedia
{
    use BelongsToOrganization;
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'agent_definition_id',
        'filename',
        'mime_type',
        'file_size',
        'media_id',
        'status',
        'chunk_count',
        'error_message',
        'processed_at',
    ];

    /** @return BelongsTo<AgentDefinition, $this> */
    public function agentDefinition(): BelongsTo
    {
        return $this->belongsTo(AgentDefinition::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('knowledge')->singleFile();
    }

    protected static function newFactory(): AgentKnowledgeFileFactory
    {
        return AgentKnowledgeFileFactory::new();
    }

    protected static function booted(): void
    {
        self::deleting(function (self $model): void {
            DB::table('model_embeddings')
                ->where('embeddable_type', self::class)
                ->where('embeddable_id', $model->id)
                ->delete();
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'media_id' => 'integer',
            'chunk_count' => 'integer',
            'processed_at' => 'datetime',
        ];
    }
}
