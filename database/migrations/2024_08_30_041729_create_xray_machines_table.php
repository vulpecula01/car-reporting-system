<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateXrayMachinesTable extends Migration
{
    public function up()
    {
        Schema::create('xray_machines', function (Blueprint $table) {
            $table->id();
            $table->string('machine_id')->unique(); // เปลี่ยนเป็น machine_id
            $table->string('machine_name');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('xray_machines');
    }
}
