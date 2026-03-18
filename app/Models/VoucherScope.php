<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Scope/owner for vouchers. Used as the morph "model" for global vouchers.
 * Create one "Global" scope and bind vouchers to it.
 *
 * @property int $id
 * @property string $name
 */
final class VoucherScope extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    public const string GLOBAL_SLUG = 'global';

    protected $fillable = ['name'];
}
