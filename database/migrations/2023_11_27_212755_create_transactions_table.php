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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number');
            $table->string('amount');
            $table->string("payment_mode")->nullable();
            $table->string("payment_phone_number")->nullable();
            $table->string("type");
            $table->string('status');
            $table->text('description');
            $table->string('reference');
            $table->string('network_code')->nullable();
            $table->string('service_code')->nullable();
            $table->foreignId("customer_id")->references("id")->on("customers")->onDelete("cascade")->nullable();
            //subscription id
            $table->foreignId("subscription_plan_id")->references("id")->on("subscription_plans")->onDelete("cascade")->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
