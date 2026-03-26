<?php

declare(strict_types=1);

namespace Modules\BotStudio\Models;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\BotStudio\Database\Factories\AgentEmbedTokenFactory;

/**
 * @property int $id
 * @property int $agent_definition_id
 * @property int $organization_id
 * @property string $token
 * @property string $name
 * @property array<int, string> $allowed_domains
 * @property bool $is_active
 * @property \Carbon\Carbon|null $last_used_at
 * @property int $request_count
 * @property int $rate_limit_per_minute
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read AgentDefinition $agentDefinition
 * @property-read Organization $organization
 */
final class AgentEmbedToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_definition_id',
        'organization_id',
        'token',
        'name',
        'allowed_domains',
        'is_active',
        'rate_limit_per_minute',
    ];

    protected $hidden = [
        'token',
    ];

    /** @return BelongsTo<AgentDefinition, $this> */
    public function agentDefinition(): BelongsTo
    {
        return $this->belongsTo(AgentDefinition::class);
    }

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    protected static function newFactory(): AgentEmbedTokenFactory
    {
        return AgentEmbedTokenFactory::new();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'allowed_domains' => 'array',
            'is_active' => 'boolean',
            'last_used_at' => 'datetime',
            'request_count' => 'integer',
            'rate_limit_per_minute' => 'integer',
        ];
    }
}
