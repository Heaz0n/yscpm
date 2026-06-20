<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    protected $table = 'AcademicYears';
    protected $fillable = ['year'];
    public $timestamps = false;
    const CREATED_AT = 'created_at';
}