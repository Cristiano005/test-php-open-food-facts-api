<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->string('code', 25)->primary();
            $table->string('product_name');
            $table->string('quantity', 15);
            $table->text('url');
            $table->string('creator');
            $table->timestamp('created_t');
            $table->dateTimeTz('created_datetime');
            $table->timestamp('imported_t');
            $table->enum('status', [
                'draft', 'trash', 'published'
            ])->default('draft');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
