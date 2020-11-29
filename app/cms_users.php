<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class cms_users extends Authenticatable
{
    //
    protected $table = 'cms_users';
    public $timestamps = false;
    public $primaryKey  = 'username';

}
