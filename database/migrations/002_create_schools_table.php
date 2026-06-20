<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('Schools', function (Blueprint $table) {
            $table->increments('code');
            $table->string('name', 255);
            $table->string('abbreviation', 50)->nullable();
            $table->string('director', 100)->nullable();
            $table->string('deputy_director', 100)->nullable();
            $table->text('notes')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('Schools');
    }
};