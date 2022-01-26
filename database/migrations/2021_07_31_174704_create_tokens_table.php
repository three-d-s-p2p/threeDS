<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'tokens',
            function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('token', 64)->nullable();
                $table->unsignedBigInteger('idSubscriptions')->nullable();
                $table->string('message', 300)->nullable();
                $table->string('code')->nullable();
                $table->string('error')->nullable();
                $table->timestamps();
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tokens');
    }
}
