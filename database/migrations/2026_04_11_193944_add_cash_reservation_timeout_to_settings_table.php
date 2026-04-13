<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('settings')->insert([
            'key' => 'cash_reservation_timeout',
            'value' => '24',
            'display_name' => 'Cash Pending Timeout (Hours)',
            'group' => 'booking',
            'type' => 'number',
            'sort_order' => 5,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->where('key', 'cash_reservation_timeout')->delete();
    }
};
