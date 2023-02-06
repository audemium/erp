<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpensePaymentsTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('expensePayments', function (Blueprint $table) {
			$table->integer('payment_id');
			$table->integer('expense_id');
			$table->integer('date');
			$table->string('paymentType', 2);
			$table->decimal('paymentAmount', 12);
			$table->primary(['payment_id', 'expense_id']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('expensePayments');
	}
}
