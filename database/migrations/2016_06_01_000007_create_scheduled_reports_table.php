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
            $table->unsignedInteger('report_id')->index();
            $table->text('parameters')->nullable();
            $table->string('frequency')->index()->default(ReportSchedule::MONTHLY);
            $table->dateTime('limit')->nullable();
            $table->text('recipients')->nullable();
            $table->dateTime('last_run')->nullable();
            $table->string('authenticatable_id')->nullable()->index();
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