<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttachmentsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('attachments', function (Blueprint $table) {
			$table->id();
			$table->string('attachedToType', 100);
			$table->integer('attachedToID');
			$table->integer('employee_id');
			$table->string('name', 200);
			$table->string('extension', 10);
			$table->string('mime', 100);
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('attachments');
	}
}
