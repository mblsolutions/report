<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MBLSolutions\Report\Support\Enums\ReportSchedule;

class CreateScheduledReportsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scheduled_reports', static function (Blueprint $table) {
            $table->uuid('uuid');
            $table->string('schedule')->index()->default(ReportSchedule::MONTHLY);
            $table->unsignedInteger('report_id')->index();
            $table->string('authenticatable_id')->nullable()->index();
            $table->text('parameters')->nullable();
            $table->dateTime('last_run')->nullable();
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
        Schema::dropIfExists('scheduled_reports');
    }

}