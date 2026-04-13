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
        \Illuminate\Support\Facades\DB::table('payment_methods_settings')->insert([
            'method' => 'agency',
            'is_enabled' => true,
            'is_sandbox' => false,
            'display_name' => 'Pay at the Agency',
            'description' => 'Pay in cash when you arrive to pick up your car.',
            'sort_order' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::table('payment_methods_settings')
            ->where('method', 'agency')
            ->delete();
    }
};
