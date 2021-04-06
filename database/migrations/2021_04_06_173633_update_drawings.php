<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateDrawings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('drawings', function (Blueprint $table) {
            $table->integer("height");
            $table->integer("width");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('drawings', function (Blueprint $table) {
            $table->dropColumn("height");
            $table->dropColumn("width");
        });
    }
}
