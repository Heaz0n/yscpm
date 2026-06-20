<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentCategory extends Model
{
    protected $table = 'StudentCategories';
    protected $fillable = ['student_id', 'category_id', 'academic_year', 'academic_year_id'];
    public $timestamps = false;
    const CREATED_AT = 'created_at';

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }
}