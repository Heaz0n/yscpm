<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('login', 50)->unique();
            $table->string('full_name', 255)->nullable();
            $table->string('password', 255);
            $table->enum('role', ['admin', 'director', 'deputy_director', 'secretary', 'member']);
            $table->integer('school_code')->unsigned()->nullable();
            $table->boolean('active')->default(1);
            $table->string('school', 255)->nullable();
            $table->string('school_short', 100)->nullable();
            $table->string('avatar', 255)->nullable();

            $table->foreign('school_code')->references('code')->on('Schools')->onDelete('set null');
            $table->index(['school', 'id'], 'idx_school_id');
            $table->index('school_code', 'idx_users_school_code');
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};