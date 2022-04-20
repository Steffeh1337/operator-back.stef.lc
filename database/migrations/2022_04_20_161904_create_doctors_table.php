<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('doctors', function (Blueprint $table) {
			$table->id();
			$table->float('rating', 2, 2);
			$table->string('last_name');
			$table->string('first_name');
			$table->string('phone');
			$table->string('email')->unique();
			$table->smallInteger('doctor_type');
			$table->integer('id_city');
			$table->integer('id_clinic');
			$table->integer('id_field');
			$table->string('password');
			$table->rememberToken();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('doctors');
	}
};
