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
        Schema::create('customer_subscription', function (Blueprint $table) {
            $table->id();
            $table->string('customer_id');
            $table->string('subscription_plan_id');
            $table->string('phone_number');
            $table->integer("number_of_children")->default(0);
            $table->string('amount')->default(0);
            $table->boolean('is_amount_paid')->default(0);
            $table->boolean("is_active")->default(0);
            $table->timestamp("expires_at")->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_subscription');
    }
};
