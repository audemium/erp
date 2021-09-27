<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderPaymentsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('orderPayments', function (Blueprint $table) {
			$table->integer('payment_id');
			$table->integer('order_id');
			$table->integer('date');
			$table->string('paymentType', 2);
			$table->decimal('paymentAmount', 12);
			$table->primary(['payment_id', 'order_id']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('orderPayments');
	}
}
