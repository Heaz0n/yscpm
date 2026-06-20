<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('TemplateVariables', function (Blueprint $table) {
            $table->id();
            $table->integer('school_code')->unsigned();
            $table->string('academic_year', 9);
            $table->string('placeholder', 50);
            $table->mediumText('value')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['school_code', 'academic_year', 'placeholder'], 'unique_template');
            $table->foreign('school_code')->references('code')->on('Schools')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('TemplateVariables');
    }
};