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
        Schema::create('postcodes', function (Blueprint $table) {
            $table->id();
            $table->string('postcode', 8)->unique()->index();
            $table->string('outcode', 4)->index();
            $table->string('incode', 3);
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            // Add point column for efficient geospatial queries
            // $table->geography('coordinates', subtype: 'POINT', srid: 4326);
            $table->string('country')->nullable();
            $table->string('region')->nullable();
            $table->string('district')->nullable();
            $table->boolean('active')->default(true);
            // Add indexes on lat/long for distance calculations
            $table->index(['latitude', 'longitude']);
            $table->timestamps();

            // Spatial index for efficient geospatial queries
            // $table->spatialIndex('coordinates');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('postcodes');
    }
};
