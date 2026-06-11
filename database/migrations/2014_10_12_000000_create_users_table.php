<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->enum('role', ['ceo', 'commercial_director', 'finance', 'sales', 'marketing', 'production', 'surveyors'])->default('sales');
                $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
                $table->boolean('is_active')->default(false);
                $table->rememberToken();
                $table->timestamps();
            });
        } else {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'role')) {
                    $table->enum('role', ['ceo', 'commercial_director', 'finance', 'sales', 'marketing', 'production', 'surveyors'])->default('sales')->after('password');
                }
                if (!Schema::hasColumn('users', 'department_id')) {
                    $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete()->after('role');
                }
                if (!Schema::hasColumn('users', 'is_active')) {
                    $table->boolean('is_active')->default(false)->after('department_id');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
