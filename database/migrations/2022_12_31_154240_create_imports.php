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
        Schema::create('imports', function (Blueprint $table) {
            $table->id();
            $table->string('file', 150);
            $table->unsignedInteger('user_id');
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('type');
            $table->smallInteger('total')->default(0);
            $table->smallInteger('imported')->default(0);
            $table->string('error_file', 150)->nullable();

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
        Schema::dropIfExists('imports');
    }
};
