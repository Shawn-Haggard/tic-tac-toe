<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    protected $attributes = [
        'code' => '',
        'active' => true,
        'turn' => -1,
        'winner' => 0,
        'board' => '[[0,0,0],
                    [0,0,0],
                    [0,0,0]]',

    ];

    protected $casts = [
        'board' => 'array',
    ];
}
