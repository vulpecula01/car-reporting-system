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
    Schema::table('cars', function (Blueprint $table) {
        $table->string('service_status')->default('maintenance'); // Or set appropriate default
    });
}

public function down()
{
    Schema::table('cars', function (Blueprint $table) {
        $table->dropColumn('service_status');
    });
}
};
