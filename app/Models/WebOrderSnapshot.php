<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebOrderSnapshot extends Model
{
    protected $table = 'web_orders_snapshot';

    protected $fillable = [
        'web_order_id',
        'order_number',
        'customer_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'status',
        'payment_status',
        'payment_method',
        'payment_proof',
        'total_amount',
        'shipping_cost',
        'discount_amount',
        'marketplace',
        'web_created_at',
        'web_updated_at',
        'synced_at',
    ];

    protected $casts = [
        'web_order_id'    => 'integer',
        'customer_id'     => 'integer',
        'total_amount'    => 'integer',
        'shipping_cost'   => 'integer',
        'discount_amount' => 'integer',
        'web_created_at'  => 'datetime',
        'web_updated_at'  => 'datetime',
        'synced_at'       => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(WebOrderItemSnapshot::class, 'web_order_id', 'web_order_id');
    }

    public function scopeCompleted($q)
    {
        return $q->where('status', 'delivered');
    }

    public function scopeBetweenDates($q, $start, $end)
    {
        return $q->whereBetween('web_created_at', [
            $start . ' 00:00:00',
            $end . ' 23:59:59',
        ]);
    }
}