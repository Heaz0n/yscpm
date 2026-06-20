<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('Students', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id');
            $table->string('full_name', 255);
            $table->string('phone', 50)->nullable();
            $table->string('telegram', 255)->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->enum('budget', ['РФ', 'ХМАО'])->nullable();
            $table->text('application_text')->nullable();
            $table->string('application_file_path', 500)->nullable();
            $table->date('application_date')->nullable();
            $table->json('document_paths')->nullable();
            $table->text('documents_info')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'in_progress'])->default('pending');
            $table->timestamp('status_updated_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('group_id')->references('id')->on('Groups')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('Students');
    }
};