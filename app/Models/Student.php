<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $table = 'Students';
    protected $fillable = [
        'group_id', 'full_name', 'phone', 'telegram', 'budget',
        'application_text', 'application_file_path', 'application_date',
        'document_paths', 'documents_info', 'status', 'status_updated_at', 'notes'
    ];
    protected $casts = [
        'document_paths' => 'array',
        'application_date' => 'date',
        'status_updated_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'StudentCategories', 'student_id', 'category_id')
                    ->withPivot('academic_year', 'academic_year_id', 'created_at')
                    ->withTimestamps();
    }

    public function reasons()
    {
        return $this->hasMany(StudentReason::class, 'student_id');
    }

    public function files()
    {
        return $this->hasMany(StudentFile::class, 'student_id');
    }
}