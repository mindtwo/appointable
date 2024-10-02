<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('calendar_appointments', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('user_id')->constrained();

            $table->string('title');
            $table->string('description');
            $table->dateTime('start');
            $table->dateTime('end');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('calendar_appointments');
    }
};
