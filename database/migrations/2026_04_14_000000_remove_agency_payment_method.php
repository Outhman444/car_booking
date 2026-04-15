<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First change any records with 'agency' to something else, like 'cash'
        DB::table('payments')
            ->where('payment_method', 'agency')
            ->update(['payment_method' => 'cash']);

        DB::table('payment_methods_settings')
            ->where('method', 'agency')
            ->update([
                'method' => 'cash',
                'display_name' => 'Pay at the Agency (Cash)',
                'description' => 'Pay in cash when you arrive to pick up your car.',
            ]);
        
        // This is tricky with SQLite, which doesn't support changing enum.
        // For MySQL or PostgreSQL, we'd alter the column.
        // For production Laravel projects, it's often safer to re-create the enum type if using DBs that support it.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op for safety as re-adding 'agency' isn't always helpful
    }
};
