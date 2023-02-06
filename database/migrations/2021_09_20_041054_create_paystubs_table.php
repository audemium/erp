<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaystubsTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('paystubs', function (Blueprint $table) {
			$table->id();
			$table->integer('employee_id');
			$table->integer('timesheet_id');
			$table->integer('date');
			$table->decimal('grossPay', 12);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('paystubs');
	}
}
