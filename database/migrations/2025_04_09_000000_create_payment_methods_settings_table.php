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
        Schema::create('payment_methods_settings', function (Blueprint $table) {
            $table->id();
            $table->string('method')->unique(); // 'paypal' or 'stripe'
            $table->boolean('is_enabled')->default(false);
            $table->boolean('is_sandbox')->default(true); // For testing
            $table->string('display_name')->nullable();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Insert default payment methods
        DB::table('payment_methods_settings')->insert([
            [
                'method' => 'paypal',
                'is_enabled' => false,
                'is_sandbox' => true,
                'display_name' => 'PayPal',
                'description' => 'Pay securely with your PayPal account',
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'method' => 'stripe',
                'is_enabled' => false,
                'is_sandbox' => true,
                'display_name' => 'Credit/Debit Card',
                'description' => 'Pay with credit or debit card via Stripe',
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods_settings');
    }
};
