<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model {
	public function locations(): BelongsToMany {
		return $this->belongsToMany(Location::class);
	}
}
