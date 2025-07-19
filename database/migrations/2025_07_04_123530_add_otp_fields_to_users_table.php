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
        Schema::table('users', function (Blueprint $table) {
            $table->string('mobile')->unique()->after('email');
            $table->string('otp')->nullable();
            $table->timestamp('otp_expires_at')->nullable()->after('otp');
            $table->boolean('is_verified')->default(false);
            $table->string('device_id')->nullable();
            $table->text('firebase_token')->nullable();
            $table->timestamp('last_login_at')->nullable()->after('is_verified');
            $table->interger('user_journey_status')->default(0);
            $table->timestamp('user_assigned_date')->nullable();
            $table->text('block_reason')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
