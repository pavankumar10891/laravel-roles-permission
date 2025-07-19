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
        Schema::create('pan_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('panid')->nullable();
            $table->string('name')->nullable();
            $table->enum('gender', ['M', 'F', 'T'])->nullable()->comment('M Male, F Female, T Transgender');
            $table->date('dob')->nullable();
            $table->longText('pancard_image')->nullable();
            $table->enum('old_new', ['0', '1'])->default('1')->comment('0 = Old, 1 = New');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pan_data');
    }
};
