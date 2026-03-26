<?php

declare(strict_types=1);

namespace Modules\BotStudio\Models;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\BotStudio\Database\Factories\AgentReviewFactory;

/**
 * @property int $id
 * @property int $agent_definition_id
 * @property int $organization_id
 * @property int $user_id
 * @property int $rating
 * @property string|null $review
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read AgentDefinition $agentDefinition
 * @property-read Organization $organization
 * @property-read User $user
 */
final class AgentReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_definition_id',
        'organization_id',
        'user_id',
        'rating',
        'review',
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

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function newFactory(): AgentReviewFactory
    {
        return AgentReviewFactory::new();
    }

    protected static function booted(): void
    {
        $recalculate = function (self $review): void {
            $definition = $review->agentDefinition;
            $definition->update([
                'average_rating' => round((float) $definition->reviews()->avg('rating'), 1),
                'review_count' => $definition->reviews()->count(),
            ]);
        };

        self::created($recalculate);
        self::deleted($recalculate);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }
}
