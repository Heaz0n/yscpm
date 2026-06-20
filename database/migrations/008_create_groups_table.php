<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('Groups', function (Blueprint $table) {
            $table->id();
            $table->integer('direction_id')->unsigned()->nullable();
            $table->string('group_name', 50);
            $table->text('notes')->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('direction_id')->references('code')->on('Directions')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('Groups');
    }
};