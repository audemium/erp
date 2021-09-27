<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTimesheetHoursTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('timesheetHours', function (Blueprint $table) {
			$table->integer('timesheet_id');
			$table->integer('date');
			$table->decimal('regularHours', 5);
			$table->decimal('overtimeHours', 5);
			$table->decimal('holidayHours', 5);
			$table->decimal('vacationHours', 5);
			$table->primary(['timesheet_id', 'date']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('timesheetHours');
	}
}
