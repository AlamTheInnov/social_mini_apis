<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onUpdate('cascade')->onDelete('cascade');

            $table->foreignId('by_user_id')->references('id')->on('users')->constrained()->onUpdate('cascade')->onDelete('cascade');
            /**
             * followed: user followed you
             * like: user liked your post
             * comment: user commented on your post
             */
            $table->enum('type', ['followed', 'like', 'comment'])->nullable();

            $table->tinyInteger('seen')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notifications');
    }
}
