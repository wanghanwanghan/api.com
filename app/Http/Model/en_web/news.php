<?php

namespace App\Http\Model\en_web;

use Illuminate\Database\Eloquent\Model;

class news extends Model
{
    protected $connection='en_web';

    protected $table='news';

    protected $primaryKey='id';

    public $timestamps=false;

    protected $guarded=[];








}
