<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('number', 255)->unique();
            $table->string('category_name', 500);
            $table->string('category_short', 255);
            $table->text('documents_list');
            $table->string('payment_frequency', 255);
            $table->decimal('max_amount', 10, 2);
            $table->string('amount_condition', 20)->default('fixed');
            $table->string('condition', 20)->default('fixed');

            $table->index('number', 'idx_categories_number');
        });
    }

    public function down()
    {
        Schema::dropIfExists('categories');
    }
};