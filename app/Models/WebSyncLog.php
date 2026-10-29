<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebSyncLog extends Model
{
    protected $table = 'web_sync_logs';

    protected $fillable = [
        'type',
        'status',
        'records_created',
        'records_updated',
        'records_skipped',     // ✅ GANTI dari records_failed
        'error_message',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'records_created' => 'integer',
        'records_updated' => 'integer',
        'records_skipped' => 'integer',   // ✅ GANTI
        'started_at'      => 'datetime',
        'finished_at'     => 'datetime',
    ];
}