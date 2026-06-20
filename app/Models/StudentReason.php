<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentReason extends Model
{
    protected $table = 'StudentReasons';
    protected $fillable = ['student_id', 'month', 'category_id', 'amount', 'academic_year', 'semester', 'year'];
    public $timestamps = false;
    const CREATED_AT = 'created_at';

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}