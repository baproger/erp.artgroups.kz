<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recommendations', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['missing_fact', 'critical_lag', 'department_drop']);
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('kpi_id')->nullable()->constrained()->nullOnDelete();
            $table->text('message');
            $table->json('meta')->nullable();
            $table->boolean('is_dismissed')->default(false);
            $table->foreignId('dismissed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('dismissed_at')->nullable();
            $table->timestamps();
            $table->index(['is_dismissed', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendations');
    }
};
