<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Employee extends Authenticatable {
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int, string>
	 */
	protected $fillable = [
		'name',
		'email',
		'password',
	];

	/**
	 * The attributes that should be hidden for serialization.
	 *
	 * @var array<int, string>
	 */
	protected $hidden = [
		'password',
		'resetToken',
	];

	/**
	 * Get the attributes that should be cast.
	 *
	 * @return array<string, string>
	 */
	protected function casts(): array {
		return [
			'password' => 'hashed',
		];
	}

	public function location(): BelongsTo {
		return $this->belongsTo(Location::class);
	}

	public function position(): BelongsTo {
		return $this->belongsTo(Position::class);
	}

	public function manager(): BelongsTo {
		return $this->belongsTo(Employee::class, 'id', 'managerID');
	}

	protected function name(): Attribute {
		return Attribute::make(
			get: fn (mixed $value, array $attributes) => $attributes['firstName'].' '.$attributes['lastName'],
		);
	}

	protected function url(): Attribute {
		return Attribute::make(
			get: fn (mixed $value, array $attributes) => route('employees.show', ['id' => $attributes['id']]),
		);
	}
}
