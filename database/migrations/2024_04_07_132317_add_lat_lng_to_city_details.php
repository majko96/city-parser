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
        Schema::table('city_details', function (Blueprint $table) {
            $table->decimal('lat', 10, 7)->after('name')->nullable();
            $table->decimal('lng', 10, 7)->after('lat')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('city_details', function (Blueprint $table) {
            //
        });
    }
};
