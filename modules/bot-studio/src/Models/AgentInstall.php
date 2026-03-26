<?php

declare(strict_types=1);

namespace Modules\BotStudio\Models;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\BotStudio\Database\Factories\AgentInstallFactory;

/**
 * @property int $id
 * @property int $organization_id
 * @property int $agent_definition_id
 * @property int|null $installed_definition_id
 * @property int|null $installed_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read Organization $organization
 * @property-read AgentDefinition $agentDefinition
 * @property-read AgentDefinition|null $installedDefinition
 * @property-read User|null $installer
 */
final class AgentInstall extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'agent_definition_id',
        'installed_definition_id',
        'installed_by',
    ];

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /** @return BelongsTo<AgentDefinition, $this> */
    public function agentDefinition(): BelongsTo
    {
        return $this->belongsTo(AgentDefinition::class);
    }

    /** @return BelongsTo<AgentDefinition, $this> */
    public function installedDefinition(): BelongsTo
    {
        return $this->belongsTo(AgentDefinition::class, 'installed_definition_id');
    }

    /** @return BelongsTo<User, $this> */
    public function installer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'installed_by');
    }

    protected static function newFactory(): AgentInstallFactory
    {
        return AgentInstallFactory::new();
    }
}
