<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $table = 'Schools';
    protected $primaryKey = 'code';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['code', 'name', 'abbreviation', 'director', 'deputy_director', 'notes'];

    public function directions()
    {
        return $this->hasMany(Direction::class, 'school_code', 'code');
    }
}