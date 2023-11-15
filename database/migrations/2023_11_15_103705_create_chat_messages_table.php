<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->text('message');
            $table->string('role'); // 'user' or 'bot'
            $table->timestamps(); // created_at will be used to order messages chronologically
        });
    }

    public function down()
    {
        Schema::dropIfExists('chat_messages');
    }
};
