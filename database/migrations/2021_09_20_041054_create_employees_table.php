<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('employees', function (Blueprint $table) {
			$table->id();
			$table->string('username', 100)->unique('username');
			$table->string('password', 60);
			$table->boolean('changePassword')->default(1);
			$table->integer('resetTime');
			$table->string('resetToken', 25);
			$table->string('firstName', 200);
			$table->string('lastName', 200);
			$table->string('payType', 1);
			$table->decimal('payAmount', 12);
			$table->string('address', 200)->nullable();
			$table->string('city', 200)->nullable();
			$table->string('state', 2)->nullable();
			$table->string('zip', 10)->nullable();
			$table->string('workEmail', 200);
			$table->integer('location_id');
			$table->integer('position_id');
			$table->integer('managerID');
			$table->integer('vacationTotal');
			$table->string('timeZone', 200);
			$table->timestamps();
			$table->softDeletes();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('employees');
	}
}
