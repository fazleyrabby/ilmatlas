<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('raw_institutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('raw_import_id')->constrained('raw_imports')->cascadeOnDelete();
            $table->string('source');
            $table->string('external_id')->nullable();
            $table->json('json_data');
            $table->string('hash');
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->index('status');
            $table->index(['source', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raw_institutions');
    }
};
