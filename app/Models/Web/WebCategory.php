<?php
// app/Models/Web/WebCategory.php

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Model;

class WebCategory extends Model
{
    protected $connection = 'mysql_web';
    protected $table = 'categories';
}