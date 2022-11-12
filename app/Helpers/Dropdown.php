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

	public static function listColor()
	{
		return [
			0 => 'rgb(255, 0, 0)',
			1 => 'rgb(60, 179, 113)',
			2 => 'rgb(255, 165, 0)',
			3 => 'rgb(0, 0, 255)',
			4 => 'rgb(238, 130, 238)',
			5 => 'rgb(106, 90, 205)',
			6 => 'rgba(60,141,188)',
			7 => 'rgba(0,0,255)',
			8 => 'rgb(60, 60, 60)',
			9 => 'rgb(90, 90, 90)',
		];
	}

	public static function allowedColor()
	{
		return array_keys(static::listN());
	}
}