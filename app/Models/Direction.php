<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Direction extends Model
{
    protected $table = 'Directions';
    protected $primaryKey = 'code';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['code', 'vsh_code', 'school_code', 'direction_name', 'level', 'notes'];

    public function school()
    {
        return $this->belongsTo(School::class, 'school_code', 'code');
    }

    public function groups()
    {
        return $this->hasMany(Group::class, 'direction_id', 'code');
    }
}