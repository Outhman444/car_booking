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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('display_name');
            $table->string('group')->default('general'); // general, contact, social, booking
            $table->string('type')->default('text'); // text, number, boolean, image, textarea
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed initial core settings
        \Illuminate\Support\Facades\DB::table('settings')->insert([
            // General
            [
                'key' => 'site_name',
                'value' => 'Real Rent Car',
                'display_name' => 'Site Name',
                'group' => 'general',
                'type' => 'text',
                'sort_order' => 1,
            ],
            [
                'key' => 'tax_rate',
                'value' => '7',
                'display_name' => 'Tax Rate (%)',
                'group' => 'booking',
                'type' => 'number',
                'sort_order' => 12,
            ],
            [
                'key' => 'currency_symbol',
                'value' => '$',
                'display_name' => 'Currency Symbol',
                'group' => 'general',
                'type' => 'text',
                'sort_order' => 3,
            ],
            [
                'key' => 'currency_code',
                'value' => 'USD',
                'display_name' => 'Currency Code',
                'group' => 'general',
                'type' => 'text',
                'sort_order' => 4,
            ],
            // Contact
            [
                'key' => 'contact_email',
                'value' => 'support@realrentcar.com',
                'display_name' => 'Contact Email',
                'group' => 'contact',
                'type' => 'text',
                'sort_order' => 1,
            ],
            [
                'key' => 'contact_phone',
                'value' => '+1 (555) 000-0000',
                'display_name' => 'Contact Phone',
                'group' => 'contact',
                'type' => 'text',
                'sort_order' => 2,
            ],
            // Booking
            [
                'key' => 'min_rental_days',
                'value' => '1',
                'display_name' => 'Minimum Rental Days',
                'group' => 'booking',
                'type' => 'number',
                'sort_order' => 1,
            ],
            [
                'key' => 'max_rental_days',
                'value' => '30',
                'display_name' => 'Maximum Rental Days',
                'group' => 'booking',
                'type' => 'number',
                'sort_order' => 2,
            ],
            [
                'key' => 'min_driving_experience',
                'value' => '2',
                'display_name' => 'Minimum Driving Experience (Years)',
                'group' => 'booking',
                'type' => 'number',
                'sort_order' => 3,
            ],
            [
                'key' => 'rental_terms',
                'value' => "Required Documents:\n1. National ID / Passport\n2. Valid Driver's License (Min. 2 years old)\n3. Credit Card for Security Deposit",
                'display_name' => 'Rental Terms & Requirements',
                'group' => 'booking',
                'type' => 'textarea',
                'sort_order' => 4,
            ],
            // Social
            [
                'key' => 'social_facebook',
                'value' => 'https://facebook.com/realrentcar',
                'display_name' => 'Facebook Page',
                'group' => 'social',
                'type' => 'text',
                'sort_order' => 1,
            ],
            [
                'key' => 'social_instagram',
                'value' => 'https://instagram.com/realrentcar',
                'display_name' => 'Instagram Profile',
                'group' => 'social',
                'type' => 'text',
                'sort_order' => 2,
            ],
            [
                'key' => 'social_whatsapp',
                'value' => '+1234567890',
                'display_name' => 'WhatsApp Number',
                'group' => 'social',
                'type' => 'text',
                'sort_order' => 3,
            ],
            // Appearance
            [
                'key' => 'hero_badge',
                'value' => 'Premium Car Rental Experience',
                'display_name' => 'Hero Section Badge',
                'group' => 'appearance',
                'type' => 'text',
                'sort_order' => 1,
            ],
            [
                'key' => 'hero_title',
                'value' => 'Drive Your Dreams',
                'display_name' => 'Hero Section Title',
                'group' => 'appearance',
                'type' => 'text',
                'sort_order' => 2,
            ],
            [
                'key' => 'hero_subtitle',
                'value' => 'Experience luxury and reliability with our premium fleet. From business meetings to weekend adventures, find the perfect vehicle for every journey.',
                'display_name' => 'Hero Section Subtitle',
                'group' => 'appearance',
                'type' => 'textarea',
                'sort_order' => 3,
            ],
            [
                'key' => 'special_offer_text',
                'value' => '',
                'display_name' => 'Home Special Offer (Leave empty to hide)',
                'group' => 'appearance',
                'type' => 'text',
                'sort_order' => 4,
            ],
            [
                'key' => 'footer_text',
                'value' => '© 2026 Real Rent Car. All rights reserved.',
                'display_name' => 'Footer Copyright Text',
                'group' => 'general',
                'type' => 'text',
                'sort_order' => 10,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
