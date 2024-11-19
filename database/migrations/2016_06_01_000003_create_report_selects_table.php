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
        Schema::create('report_selects', static function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('report_id')->index();
            $table->text('column');
            $table->string('alias', 50);
            $table->string('type');
            $table->unsignedTinyInteger('column_order')->default(0);
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
        Schema::dropIfExists('report_selects');
    }
};