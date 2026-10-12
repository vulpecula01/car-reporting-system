<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * เพิ่มคอลัมน์ department (xray / checkup) ให้ users, cars, events
 * ข้อมูลเดิมทั้งหมดจะถูกตั้งเป็น 'xray'
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['users', 'cars', 'events'] as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'department')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->string('department', 20)->default('xray')->index();
                });
            }
        }

        // ให้ event เดิมใช้แผนกเดียวกับรถ
        if (Schema::hasTable('events') && Schema::hasTable('cars')) {
            DB::statement("UPDATE events e JOIN cars c ON c.id = e.car_id SET e.department = c.department");
        }
    }

    public function down(): void
    {
        foreach (['users', 'cars', 'events'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'department')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropIndex([ 'department' ]);
                    $t->dropColumn('department');
                });
            }
        }
    }
};
