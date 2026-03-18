<?php

declare(strict_types=1);

namespace Modules\Contact\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Mattiverse\Userstamps\Traits\Userstamps;
use Modules\Contact\Database\Factories\ContactSubmissionFactory;

final class ContactSubmission extends Model
{
    use BelongsToOrganization;

    /** @use HasFactory<ContactSubmissionFactory> */
    use HasFactory;

    use Userstamps;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'status',
    ];

    protected static function newFactory(): ContactSubmissionFactory
    {
        return ContactSubmissionFactory::new();
    }
}
