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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number')->unique();
            $table->string('nin')->nullable();
            $table->string("name")->nullable();
            $table->integer("number_of_children")->default(0);
            $table->string('dob')->nullable();
            $table->string('location')->nullable();
            $table->string("policy")->nullable();
            $table->string("pin")->nullable();
            $table->foreignId("subscription_plan_id")->references("id")->on("subscription_plans")->onDelete("cascade")->default(1);
            $table->boolean("is_active")->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
