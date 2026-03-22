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
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('store_name');
            $table->string('ownership_type');
            $table->string('street_address');
            $table->string('city');
            $table->string('state');
            $table->string('postcode')->nullable();

            $table->integer('median_household_income')->nullable();
            $table->integer('per_capita_income')->nullable();
            $table->float('college_degree_pct')->nullable();
            $table->float('median_age')->nullable();
            $table->boolean('geocoded')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
