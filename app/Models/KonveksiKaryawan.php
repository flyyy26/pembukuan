<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KonveksiKaryawan extends Model
{
    protected $fillable = ['nama', 'no_hp'];

    public function hancas()
    {
        return $this->hasMany(Hanca::class);
    }
}