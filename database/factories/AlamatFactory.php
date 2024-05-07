<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AlamatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'tujuan'    => 'Dinkes KAB ',
            'alamat'    => 'Jl',
            'ekspedisi' => 'Tiki Reg',
            'koli'      => 1,
            'do'        => 'CENT/OUT/0',
        ];
    }
}
