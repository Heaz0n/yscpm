<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateVariable extends Model
{
    protected $table = 'TemplateVariables';
    protected $fillable = ['school_code', 'academic_year', 'placeholder', 'value'];
    public $timestamps = false;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public function school()
    {
        return $this->belongsTo(School::class, 'school_code', 'code');
    }
}