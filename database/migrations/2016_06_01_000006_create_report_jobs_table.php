<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MBLSolutions\Report\Support\Enums\JobStatus;

class CreateReportJobsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_jobs', static function (Blueprint $table) {
            $table->uuid('uuid');
            $table->unsignedInteger('report_id')->index();
            $table->string('status', 20)->default(JobStatus::SCHEDULED);
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
        Schema::dropIfExists('report_jobs');
    }

}