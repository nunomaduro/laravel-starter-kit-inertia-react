<?php

declare(strict_types=1);

namespace Modules\BotStudio\Models;

use App\Models\AgentConversation;
use App\Models\Concerns\HasVisibility;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\BotStudio\Database\Factories\AgentDefinitionFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property int $id
 * @property int|null $organization_id
 * @property int|null $created_by
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $avatar_path
 * @property string $system_prompt
 * @property string $model
 * @property float $temperature
 * @property int $max_tokens
 * @property array<int, string> $enabled_tools
 * @property array<string, mixed> $knowledge_config
 * @property array<int, string> $conversation_starters
 * @property array<string, mixed>|null $wizard_answers
 * @property \App\Enums\VisibilityEnum $visibility
 * @property bool $is_published
 * @property bool $is_featured
 * @property bool $is_template
 * @property int $total_conversations
 * @property int $total_messages
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read User|null $creator
 * @property-read Organization|null $organization
 * @property-read \Illuminate\Database\Eloquent\Collection<int, AgentKnowledgeFile> $knowledgeFiles
 * @property-read \Illuminate\Database\Eloquent\Collection<int, AgentConversation> $conversations
 */
final class AgentDefinition extends Model implements HasMedia
{
    use HasFactory;
    use HasVisibility;
    use InteractsWithMedia;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'avatar_path',
        'system_prompt',
        'model',
        'temperature',
        'max_tokens',
        'enabled_tools',
        'knowledge_config',
        'conversation_starters',
        'wizard_answers',
        'is_published',
        'is_featured',
        'is_template',
        'cloned_from',
    ];

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return HasMany<AgentKnowledgeFile, $this> */
    public function knowledgeFiles(): HasMany
    {
        return $this->hasMany(AgentKnowledgeFile::class);
    }

    /** @return HasMany<AgentConversation, $this> */
    public function conversations(): HasMany
    {
        return $this->hasMany(AgentConversation::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')->singleFile();
    }

    protected static function newFactory(): AgentDefinitionFactory
    {
        return AgentDefinitionFactory::new();
    }

    protected static function booted(): void
    {
        self::creating(function (self $model): void {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }

            if (empty($model->created_by) && auth()->check()) {
                $model->created_by = auth()->id();
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'enabled_tools' => 'array',
            'knowledge_config' => 'array',
            'conversation_starters' => 'array',
            'wizard_answers' => 'array',
            'temperature' => 'decimal:1',
            'max_tokens' => 'integer',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'is_template' => 'boolean',
            'total_conversations' => 'integer',
            'total_messages' => 'integer',
        ];
    }
}
