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
    public function up(): void
    {
        Schema::create('report_joins', static function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('report_id')->index();
            $table->string('type', 10);
            $table->string('table', 128);
            $table->string('first', 128);
            $table->string('operator', 2);
            $table->string('second', 128);
            $table->timestamps();
            $table->softDeletes();
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('report_joins');
    }
};