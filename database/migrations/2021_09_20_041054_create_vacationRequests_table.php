<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVacationRequestsTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('vacationRequests', function (Blueprint $table) {
			$table->id();
			$table->integer('employee_id');
			$table->integer('submitTime');
			$table->integer('startTime');
			$table->integer('endTime');
			$table->string('status', 1);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('vacationRequests');
	}
}
