<?php
// app/Models/Web/WebUser.php

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Model;

class WebUser extends Model
{
    protected $connection = 'mysql_web';
    protected $table = 'users';

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function orders()
    {
        return $this->hasMany(WebOrder::class, 'user_id', 'id');
    }
}