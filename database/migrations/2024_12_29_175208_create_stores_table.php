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
            $table->uuid('uuid');
            $table->unsignedBigInteger('organisation_id');
            $table->unsignedBigInteger('postcode_id')->nullable();
            $table->string('name');
            $table->boolean('open')->default(1);
            $table->integer('max_delivery_distance')->default(1);
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            // $table->geography('coordinates', subtype: 'POINT', srid: 4326);
            $table->softDeletes();

            // Foreign key constraint
            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade');
            $table->foreign('postcode_id')->references('id')->on('postcodes')->onDelete('cascade');

            // Spatial index for efficient geospatial queries
            // $table->spatialIndex('coordinates');

            // Add indexes on lat/long for distance calculations
            $table->index(['latitude', 'longitude']);

            $table->timestamps(); // Created at and updated at timestamps
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
