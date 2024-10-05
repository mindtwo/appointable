<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();

            $table->string('uid')->unique();

            $table->morphs('invitee', 'invitee_index');

            $table->nullableMorphs('linkable');

            $startDate = $table->date('start_date')->index();
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            $table->string('timezone')->nullable();

            $table->boolean('is_entire_day')->default(false);

            $table->string('status')->nullable();

            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('location')->nullable();

            $table->unsignedInteger('sequence')->default(0);

            $table->timestamps();

            $table->index(['invitee_type', 'invitee_id', $startDate->name]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appointments');
    }
};
