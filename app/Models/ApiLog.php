<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * We need minified logging for API calls to know which API endpoints are used by which applications,
 * so we can focus us on developing the most important ones.
 * We don't save any user information, ip addresses or anything else.
 * The user agent is required to identify applications.
 */
class ApiLog extends Model
{
    protected $fillable = ['method', 'route', 'status_code', 'user_agent_id'];
    protected $casts    = [
        'method'        => 'string',
        'route'         => 'string',
        'status_code'   => 'integer',
        'user_agent_id' => 'integer',
    ];

    public function userAgent(): BelongsTo {
        return $this->belongsTo(UserAgent::class, 'user_agent_id', 'id');
    }
}
