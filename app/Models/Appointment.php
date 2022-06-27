<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Appointment extends Model
{
	use HasFactory;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int, string>
	 */
	protected $fillable = [
		'id_user',
		'id_doctor',
		'id_city',
		'id_clinic',
		'id_field',
		'start_date',
		'end_date',
		'active',
		'amount',
		'cancelled_user',
		'cancelled_user_date',
		'cancelled_user_obs',
		'cancelled_doctor',
		'cancelled_doctor_date',
		'cancelled_doctor_obs',
		'updated_at'
	];

	/**
	 * The attributes that should be hidden for serialization.
	 *
	 * @var array<int, string>
	 */
	protected $hidden = [];

	/**
	 * The attributes that should be cast.
	 *
	 * @var array<string, string>
	 */
	protected $casts = [];
}
