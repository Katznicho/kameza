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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number');
            $table->string('account')->default(0);
            $table->string('pin')->nullable();
            $table->boolean("status")->default(0);
            $table->foreignId("customer_id")->references("id")->on("customers")->onDelete("cascade")->nullable();
            $table->foreignId("subscription_plan_id")->references("id")->on("subscription_plans")->onDelete("cascade")->default(1);
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
        Schema::dropIfExists('accounts');
    }
};
