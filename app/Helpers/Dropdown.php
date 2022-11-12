<?php

namespace App\Helpers;

class Dropdown
{
	public static function listRheologicalModel()
	{
		return [
			'fann_data' => 'Fann Data',
			'power_law' => 'Power - Law',
			'herschel_buckley' => 'Herschel Buckley',
			'bingham_plastic' => 'Bingham Plastic',
			'newtonian_model' => 'Newtonian Model',
		];
	}

	public static function allowedRheologicalModel()
	{
		return array_keys(static::listRheologicalModel());
	}

	public static function listN()
	{
		return [
			'600' => '600',
			'300' => '300',
			'200' => '200',
			'100' => '100',
			'6' => '6',
			'3' => '3',
		];
	}

	public static function allowedN()
	{
		return array_keys(static::listN());
	}
}