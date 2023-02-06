<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersDiscountsTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('orders_discounts', function (Blueprint $table) {
			$table->id();
			$table->integer('order_id');
			$table->integer('discount_id');
			$table->string('appliesToType', 1);
			$table->integer('appliesToID')->default(0);
			$table->string('discountType', 1);
			$table->decimal('discountAmount', 12);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('orders_discounts');
	}
}
