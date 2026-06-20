<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';
    protected $fillable = [
        'number', 'category_name', 'category_short', 'documents_list',
        'payment_frequency', 'max_amount', 'amount_condition', 'condition'
    ];
    public $timestamps = false;

    public function students()
    {
        return $this->belongsToMany(Student::class, 'StudentCategories', 'category_id', 'student_id')
                    ->withPivot('academic_year', 'academic_year_id', 'created_at')
                    ->withTimestamps();
    }
}