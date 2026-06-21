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
        Schema::create('company_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('company_name', 255);
            $table->text('address')->nullable();
            $table->string('logo_path', 255)->nullable();
            $table->string('contact', 255)->nullable();
            $table->enum('date_format', ['Y-m-d', 'd/m/Y', 'm/d/Y'])->default('Y-m-d');
            $table->string('currency', 10)->default('IDR');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_profiles');
    }
};