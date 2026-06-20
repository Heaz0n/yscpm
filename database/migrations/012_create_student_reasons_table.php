<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('StudentReasons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->integer('month');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->string('academic_year', 9)->default('2024/2025');
            $table->enum('semester', ['1', '2'])->nullable();
            $table->integer('year');

            $table->unique(['student_id', 'month'], 'unique_student_month');
            $table->foreign('student_id')->references('id')->on('Students')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('StudentReasons');
    }
};