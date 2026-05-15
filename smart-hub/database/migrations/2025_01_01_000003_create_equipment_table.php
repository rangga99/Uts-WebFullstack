<?php

// File: database/migrations/2025_01_01_000003_create_equipment_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 30)->unique();
            $table->enum('category', ['camera', 'audio', 'lighting', 'computer', 'other'])
                  ->default('other');
            $table->string('brand', 80)->nullable();
            $table->string('model', 80)->nullable();
            $table->string('serial_number', 80)->unique()->nullable();
            $table->enum('condition', ['excellent', 'good', 'fair', 'needs_repair'])
                  ->default('good');
            $table->enum('status', ['available', 'checked_out', 'maintenance', 'retired'])
                  ->default('available');
            $table->text('description')->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->string('location', 100)->default('Storage Room');
            $table->timestamps();

            $table->index('status');
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
