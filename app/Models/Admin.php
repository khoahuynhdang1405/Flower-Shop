<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $fillable = ['role_id', 'name', 'email', 'phone', 'avatar', 'password', 'is_active'];
}
