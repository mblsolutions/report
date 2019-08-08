<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportFieldsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_fields', static function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('report_id')->index();
            $table->string('label', 100);
            $table->string('type', 8);
            $table->string('model')->nullable();
            $table->string('alias')->nullable();
            $table->string('model_select_value')->nullable();
            $table->string('model_select_name')->nullable();
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
        Schema::dropIfExists('report_fields');
    }

}