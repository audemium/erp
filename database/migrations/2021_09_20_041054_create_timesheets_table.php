<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTimesheetsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('timesheets', function (Blueprint $table) {
			$table->id();
			$table->integer('employee_id');
			$table->integer('firstDate');
			$table->integer('lastDate');
			$table->string('payType', 1);
			$table->decimal('payAmount', 12);
			$table->string('status', 1);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('timesheets');
	}
}
