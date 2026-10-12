<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPickupTimesToCarBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('car_bookings', function (Blueprint $table) {
            $table->time('pickup_start_time')->nullable();  // เพิ่มคอลัมน์ pickup_start_time
            $table->time('pickup_end_time')->nullable();    // เพิ่มคอลัมน์ pickup_end_time
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('car_bookings', function (Blueprint $table) {
            $table->dropColumn('pickup_start_time');
            $table->dropColumn('pickup_end_time');
        });
    }
}