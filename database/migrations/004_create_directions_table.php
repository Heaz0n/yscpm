<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('Directions', function (Blueprint $table) {
            $table->increments('code');
            $table->integer('vsh_code')->unsigned()->nullable();
            $table->integer('school_code')->unsigned()->nullable();
            $table->string('direction_name', 255);
            $table->enum('level', ['Бакалавриат', 'Специалитет', 'Магистратура', 'Аспирантура'])->nullable();
            $table->text('notes')->nullable();

            $table->foreign('vsh_code')->references('code')->on('Schools')->onDelete('cascade');
            $table->foreign('school_code')->references('code')->on('Schools')->onDelete('cascade');

            $table->index('school_code', 'idx_directions_school');
        });
    }

    public function down()
    {
        Schema::dropIfExists('Directions');
    }
};