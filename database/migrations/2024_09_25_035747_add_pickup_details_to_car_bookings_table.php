<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('car_bookings', function (Blueprint $table) {
        $table->date('pickup_date')->nullable();
        $table->time('pickup_time')->nullable();
    });
}

public function down()
{
    Schema::table('car_bookings', function (Blueprint $table) {
        $table->dropColumn(['pickup_date', 'pickup_time']);
    });
}
};
