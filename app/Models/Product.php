<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model {
	public function locations() {
		return $this->belongsToMany(Location::class);
	}
}
