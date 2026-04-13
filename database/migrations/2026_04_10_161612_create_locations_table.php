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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed some initial locations
        \Illuminate\Support\Facades\DB::table('locations')->insert([
            ['name' => 'Main Agency Office', 'address' => '123 Business Ave', 'city' => 'Casablanca', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'International Airport', 'address' => 'Terminal 2 Arrivals', 'city' => 'Casablanca', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'City Center Mall', 'address' => 'Parking Level B', 'city' => 'Rabat', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
