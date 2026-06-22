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
        Schema::create('audit_trails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('entity_type', 100);
            $table->unsignedBigInteger('entity_id');
            $table->enum('action', ['create', 'update', 'delete', 'approve', 'reject']);
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->index('user_id', 'idx_audit_trails_user_id');
            $table->index(['entity_type', 'entity_id'], 'idx_audit_trails_entity');
            $table->index('created_at', 'idx_audit_trails_created_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_trails');
    }
};