<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneratedProtocol extends Model
{
    protected $table = 'GeneratedProtocols';
    protected $fillable = [
        'protocol_number', 'month', 'academic_year', 'school_code',
        'student_snapshot', 'file_name', 'file_content'
    ];
    protected $casts = [
        'student_snapshot' => 'array',
        'file_content' => 'binary'
    ];
    public $timestamps = false;
    const CREATED_AT = 'generated_at';

    public function school()
    {
        return $this->belongsTo(School::class, 'school_code', 'code');
    }
}