<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->onDelete('cascade');
            $table->decimal('total', 10, 2);
            $table->integer('number_of_installments');
            $table->date('first_due_date');
            $table->unsignedTinyInteger('notifications_per_installment')->default(1);
            $table->unsignedTinyInteger('notify_days_before');
            $table->unsignedTinyInteger('notify_days_before_secondary')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billings');
    }
};