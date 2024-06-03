<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model {
	/**
	 * The attributes that should be hidden for serialization.
	 *
	 * @var array
	 */
	protected $hidden = [
		'password'
	];

	public function location() {
		return $this->belongsTo(Location::class);
	}

	public function position() {
		return $this->belongsTo(Position::class);
	}

	public function manager() {
		return $this->belongsTo(Employee::class, 'id', 'managerID');
	}

	protected function name(): Attribute {
		return Attribute::make(
			get: fn (mixed $value, array $attributes) => $attributes['firstName'].' '.$attributes['lastName'],
		);
	}
}
