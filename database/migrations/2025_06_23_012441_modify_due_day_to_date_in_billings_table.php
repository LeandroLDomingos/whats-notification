<?php
// database/migrations/YYYY_MM_DD_HHMMSS_modify_due_day_to_date_in_billings_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billings', function (Blueprint $table) {
            // Adiciona a nova coluna de data
            $table->date('first_due_date')->after('installments');
            // Remove a coluna antiga
            $table->dropColumn('due_day');
        });
    }

    public function down(): void
    {
        Schema::table('billings', function (Blueprint $table) {
            // Reverte as alterações caso seja necessário
            $table->unsignedTinyInteger('due_day');
            $table->dropColumn('first_due_date');
        });
    }
};