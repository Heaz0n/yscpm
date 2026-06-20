<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('StudentCategories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('category_id');
            $table->string('academic_year', 10);
            $table->timestamp('created_at')->useCurrent();
            $table->integer('academic_year_id')->unsigned()->nullable();

            $table->foreign('student_id')->references('id')->on('Students')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('StudentCategories');
    }
};