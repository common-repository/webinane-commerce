<?php

namespace WeDevs\ORM\WP;


use WeDevs\ORM\Eloquent\Model;

class User extends Model
{
    protected $primaryKey = 'ID';
    protected $timestamp = false;

    protected $hidden = [
    	'user_pass'
    ];

    public function meta()
    {
        return $this->hasMany('WeDevs\ORM\WP\UserMeta', 'user_id');
    }
}