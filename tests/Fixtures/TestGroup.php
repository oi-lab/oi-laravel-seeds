<?php

namespace OiLab\LaravelSeeds\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class TestGroup extends Model
{
    protected $table = 'test_groups';

    protected $fillable = ['name', 'description'];
}
