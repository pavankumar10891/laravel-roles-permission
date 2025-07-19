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
        Schema::create('a_adhar_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->longText('selfie')->nullable();
            $table->string('uid')->nullable()->unique(); 
            $table->string('fullname')->nullable();
            $table->enum('gender', ['M', 'F', 'T'])->nullable()->comment('M Male, F Female, T Transgender');
            $table->date('dob')->nullable();
            $table->string('father_name')->nullable();
            $table->longText('current_address')->nullable();
            $table->string('current_post_office')->nullable();
            $table->string('current_city')->nullable();
            $table->string('current_state')->nullable();
            $table->string('current_pincode')->nullable();
            $table->text('permanent_address')->nullable();
            $table->string('permanent_post_office')->nullable();
            $table->string('permanent_city')->nullable();
            $table->string('permanent_state')->nullable();
            $table->string('permanent_pincode')->nullable();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->longText('aadharcard_image')->nullable();
            $table->text('profile_report')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Enables soft deletion
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('a_adhar_data');
    }
};
