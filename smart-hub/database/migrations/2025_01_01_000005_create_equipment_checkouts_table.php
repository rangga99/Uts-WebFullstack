<?php

// File: database/migrations/2025_01_01_000005_create_equipment_checkouts_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_checkouts', function (Blueprint $table) {
            $table->id();
            $table->string('checkout_code', 30)->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('equipment_id')->constrained('equipment')->cascadeOnDelete();
            $table->foreignId('booking_id')
                  ->nullable()
                  ->constrained('bookings')
                  ->nullOnDelete();
            $table->dateTime('checked_out_at');
            $table->dateTime('expected_return_at');
            $table->dateTime('returned_at')->nullable();
            $table->enum('status', ['active', 'returned', 'overdue', 'lost'])->default('active');
            $table->enum('condition_before', ['excellent', 'good', 'fair', 'needs_repair'])
                  ->default('good');
            $table->enum('condition_after', ['excellent', 'good', 'fair', 'needs_repair'])
                  ->nullable();
            $table->text('notes_checkout')->nullable();
            $table->text('notes_return')->nullable();
            $table->foreignId('processed_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamps();

            $table->index('user_id');
            $table->index('equipment_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_checkouts');
    }
};