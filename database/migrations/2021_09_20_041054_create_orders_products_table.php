<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersProductsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('orders_products', function (Blueprint $table) {
			$table->id();
			$table->integer('order_id');
			$table->integer('product_id');
			$table->integer('date')->nullable();
			$table->decimal('unitPrice', 12);
			$table->integer('quantity');
			$table->decimal('lineAmount', 12);
			$table->integer('recurringID')->nullable();
			$table->integer('parentRecurringID')->nullable();
			$table->integer('dayOfMonth')->nullable();
			$table->integer('startDate')->nullable();
			$table->integer('endDate')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('orders_products');
	}
}
