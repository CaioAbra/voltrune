<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('hub_mysql')->create('company_billing_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->enum('financial_status', ['pending', 'paid', 'overdue', 'canceled'])->default('pending');
            $table->enum('payment_method', ['pix', 'transfer', 'card', 'boleto', 'other'])->nullable();
            $table->date('last_payment_date')->nullable();
            $table->decimal('paid_amount', 10, 2)->nullable();
            $table->date('next_billing_date')->nullable();
            $table->text('financial_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('hub_mysql')->dropIfExists('company_billing_records');
    }
};
