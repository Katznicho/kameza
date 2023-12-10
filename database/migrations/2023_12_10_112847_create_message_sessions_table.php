<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// 'linkId' => 'dd96368c-e22a-496a-9e32-3c3c5de06189',
//   'text' => 'Katende Nicholas , CM12457',
//   'to' => '22884',
//   'id' => '9f1ec03a-afec-4ac4-855c-7cabe7b15fd8',
//   'date' => '2023-12-10 14:23:09',
//   'from' => '+256756976723',
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('message_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('linkId')->nullable();
            $table->string('text')->nullable();
            $table->string('to')->nullable();
            $table->string('message_id')->nullable();
            $table->string('date')->nullable();
            $table->string('from')->nullable();
            $table->string("status")->nullable();
            $table->string("message")->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_sessions');
    }
};
