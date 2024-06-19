<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model {
	public function employees(): HasMany {
		return $this->hasMany(Employee::class);
	}

	public function products(): BelongsToMany {
		return $this->belongsToMany(Product::class);
	}
}
