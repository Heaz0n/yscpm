<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserToken extends Model
{
    protected $table = 'user_tokens';
    public $timestamps = false;
    protected $fillable = ['user_id', 'token', 'expires_at'];
}