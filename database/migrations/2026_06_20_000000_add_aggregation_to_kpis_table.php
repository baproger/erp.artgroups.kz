<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kpis', function (Blueprint $table) {
            if (! Schema::hasColumn('kpis', 'aggregation')) {
                // 'sum'   — абсолютный показатель (складывается по дням)
                // 'ratio' — относительный (пересчитывается из базовых за период)
                $table->string('aggregation', 10)->default('sum')->after('direction');
            }
            if (! Schema::hasColumn('kpis', 'numerator_slug')) {
                $table->string('numerator_slug')->nullable()->after('aggregation');
            }
            if (! Schema::hasColumn('kpis', 'denominator_slug')) {
                $table->string('denominator_slug')->nullable()->after('numerator_slug');
            }
            if (! Schema::hasColumn('kpis', 'factor')) {
                // Множитель результата: 1 для денег (CPL/CAC), 100 для процентов (конверсия)
                $table->unsignedInteger('factor')->default(1)->after('denominator_slug');
            }
        });
    }

    public function down(): void
    {
        Schema::table('kpis', function (Blueprint $table) {
            foreach (['aggregation', 'numerator_slug', 'denominator_slug', 'factor'] as $col) {
                if (Schema::hasColumn('kpis', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
