<?php
// app/Models/Web/WebOrder.php

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Model;

class WebOrder extends Model
{
    protected $connection = 'mysql_web';
    protected $table = 'orders';

    protected $casts = [
        'total_amount' => 'decimal:2',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(WebOrderItem::class, 'order_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(WebUser::class, 'user_id', 'id');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeBetweenDates($query, $start, $end)
    {
        return $query->whereBetween('created_at', [
            $start . ' 00:00:00',
            $end . ' 23:59:59',
        ]);
    }
}