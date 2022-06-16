<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->nullable();
            $table->string('title')->nullable();
            $table->string('level_1')->nullable();
            $table->string('level_2')->nullable();
            $table->string('level_3')->nullable();
            $table->decimal('price', 10, 2, true)->default(0);
            $table->decimal('price_sp', 10, 2, true)->default(0);
            $table->unsignedInteger('count')->default(0);
            $table->text('properties')->nullable();
            $table->boolean('joint_purchases')->default(false);
            $table->string('units')->nullable();
            $table->string('img')->nullable();
            $table->boolean('on_homepage')->default(0);
            $table->text('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
};
