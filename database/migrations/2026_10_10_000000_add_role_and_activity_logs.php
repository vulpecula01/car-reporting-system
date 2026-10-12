<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * V2: สิทธิ์ผู้ใช้ (role) + ตารางประวัติการใช้งาน (activity_logs)
 * ใช้แทน database/sql/upgrade_v2.sql ได้
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $t) {
                $t->string('role', 20)->default('user');
            });
            DB::table('users')->where('id', 1)->update(['role' => 'admin']);
        }

        if (!Schema::hasTable('activity_logs')) {
            Schema::create('activity_logs', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('user_id')->nullable();
                $t->string('user_name')->nullable();
                $t->string('department', 20)->nullable()->index();
                $t->string('action', 30);
                $t->string('subject_type', 50)->nullable();
                $t->unsignedBigInteger('subject_id')->nullable();
                $t->text('description')->nullable();
                $t->json('changes')->nullable();
                $t->string('ip_address', 45)->nullable();
                $t->timestamps();
                $t->index('created_at');
                $t->index(['subject_type', 'subject_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        if (Schema::hasColumn('users', 'role')) {
            Schema::table('users', fn (Blueprint $t) => $t->dropColumn('role'));
        }
    }
};
