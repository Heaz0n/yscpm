<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('GeneratedProtocols', function (Blueprint $table) {
            $table->id();
            $table->string('protocol_number', 50);
            $table->string('month', 20);
            $table->string('academic_year', 9);
            $table->integer('school_code')->unsigned();
            $table->timestamp('generated_at')->useCurrent();
            $table->json('student_snapshot');
            $table->string('file_name', 255)->nullable();
            $table->binary('file_content')->nullable();

            $table->foreign('school_code')->references('code')->on('Schools')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('GeneratedProtocols');
    }
};