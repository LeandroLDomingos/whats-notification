<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('billings', function (Blueprint $table) {
            $table->decimal('original_total', 10, 2)->after('contact_id');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('billings', function (Blueprint $table) {
            $table->dropColumn('original_total');
        });
    }

};
