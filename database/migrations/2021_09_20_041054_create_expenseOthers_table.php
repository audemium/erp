<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpenseOthersTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('expenseOthers', function (Blueprint $table) {
			$table->id();
			$table->integer('expense_id');
			$table->string('name', 200);
			$table->integer('date')->nullable();
			$table->decimal('unitPrice', 12);
			$table->decimal('quantity', 12);
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
	public function down() {
		Schema::dropIfExists('expenseOthers');
	}
}
