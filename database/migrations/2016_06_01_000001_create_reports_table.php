<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reports', static function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->string('description');
            $table->string('connection', 100);
            $table->unsignedSmallInteger('display_limit')->default(25);
            $table->string('table', 128);
            $table->text('where')->nullable();
            $table->text('groupby')->nullable();
            $table->text('having')->nullable();
            $table->text('orderby')->nullable();
            $table->boolean('show_data')->default(1);
            $table->boolean('show_totals')->default(1);
            $table->boolean('active')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reports');
    }

}