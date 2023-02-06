<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model {
	public function employees() {
		return $this->hasMany(Employee::class);
	}

	public function products() {
		return $this->belongsToMany(Product::class);
	}
}
