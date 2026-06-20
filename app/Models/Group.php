<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = 'Groups';
    protected $fillable = ['direction_id', 'group_name', 'notes'];
    public $timestamps = false;
    const UPDATED_AT = 'updated_at';

    public function direction()
    {
        return $this->belongsTo(Direction::class, 'direction_id', 'code');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'group_id');
    }
}