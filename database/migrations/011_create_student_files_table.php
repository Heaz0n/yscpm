<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('StudentFiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('file_name', 255);
            $table->string('file_type', 50);
            $table->string('file_path', 500);
            $table->integer('file_size')->nullable();
            $table->timestamp('uploaded_at')->useCurrent();

            $table->foreign('student_id')->references('id')->on('Students')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('StudentFiles');
    }
};