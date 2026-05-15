<?php

// File: database/migrations/2025_01_01_000002_create_rooms_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 20)->unique();
            $table->enum('type', ['workspace', 'studio', 'meeting'])->default('workspace');
            $table->tinyInteger('capacity')->unsigned();
            $table->text('description')->nullable();
            $table->json('facilities')->nullable(); // ["AC", "Proyektor", "Whiteboard"]
            $table->decimal('price_per_hour', 10, 2)->default(0);
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->index('type');
            $table->index('is_available');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
