<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;

class User extends Model implements Authenticatable
{
    use AuthenticatableTrait;

    protected $table = 'users';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $fillable = [
        'login', 'full_name', 'password', 'role', 'school_code',
        'active', 'school', 'school_short', 'avatar'
    ];
    protected $hidden = ['password'];
    public $timestamps = false;

    public function school()
    {
        return $this->belongsTo(School::class, 'school_code', 'code');
    }
}