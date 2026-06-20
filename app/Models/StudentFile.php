<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentFile extends Model
{
    protected $table = 'StudentFiles';
    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'file_name',
        'file_type',
        'file_path',
        'file_size'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}