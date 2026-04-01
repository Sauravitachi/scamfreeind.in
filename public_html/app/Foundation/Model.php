<?php

namespace App\Foundation;

use App\Traits\ModelSupport;

abstract class Model extends \Illuminate\Database\Eloquent\Model
{
    use ModelSupport;
}
