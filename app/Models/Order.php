<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model {
	public function customer(): BelongsTo {
		return $this->belongsTo(Customer::class);
	}

	public function employee(): BelongsTo {
		return $this->belongsTo(Employee::class);
	}
}
