<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('raw_imports', function (Blueprint $table) {
            $table->id();
            $table->string('source');
            $table->string('file_name');
            $table->string('status')->default('pending');
            $table->integer('record_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raw_imports');
    }
};
