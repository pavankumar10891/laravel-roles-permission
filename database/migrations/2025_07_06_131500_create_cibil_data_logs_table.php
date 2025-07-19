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
        Schema::create('cibil_data_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('cibilscore')->nullable();
            $table->text('cibilrequestlink')->nullable();
            $table->text('cibilresponselink')->nullable();
            $table->text('manualcibildoc_url')->nullable();
            $table->enum('type', ['0', '1'])->default('0')->comment('0: Auto, 1: Manual');
            $table->enum('old_new', ['0', '1'])->default('1')->comment('0: Old, 1: New');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cibil_data_logs');
    }
};
