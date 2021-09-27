<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChangesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('changes', function (Blueprint $table) {
			$table->id();
			$table->string('type', 100);
			$table->integer('type_id');
			$table->integer('employee_id');
			$table->integer('changeTime');
			$table->string('action', 1);
			$table->text('data')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('changes');
	}
}
