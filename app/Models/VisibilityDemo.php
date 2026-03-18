<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasVisibility;
use Illuminate\Database\Eloquent\Model;

/**
 * Demo model for HasVisibility trait. Used in tests and as a reference.
 *
 * @property int $id
 * @property int|null $organization_id
 * @property \App\Enums\VisibilityEnum $visibility
 * @property int|null $cloned_from
 * @property string $title
 */
final class VisibilityDemo extends Model
{
    use HasVisibility;
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'title',
        'cloned_from',
    ];
}
