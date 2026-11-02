<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Import extends Model
{
    protected $fillable = [
        'store_id',
        'tipe',
        'file_name',
        'stored_path',
        'file_hash',
        'periode_start',
        'periode_end',
        'total_rows',
        'status',
        'error_message',
    ];

    protected $casts = [
        'periode_start' => 'date',
        'periode_end' => 'date',
        'total_rows' => 'integer',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}