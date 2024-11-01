<?php

namespace WebinaneCommerce\Models;

use Illuminate\Database\Eloquent\Builder;
use WeDevs\ORM\Eloquent\Model;
use WeDevs\ORM\WP\User as WPUser;
use WebinaneCommerce\Classes\Customers;
use WebinaneCommerce\Models\OrderItems;

class User extends WPUser {


	
	public function getFirstNameAttribute() {
		return $this->meta->where('meta_key', 'first_name')->first()->meta_value;
	}
	
	public function getLastNameAttribute() {
		return $this->meta->where('meta_key', 'last_name')->first()->meta_value;
	}

	
	public function getDescriptionAttribute() {
		return $this->meta->where('meta_key', 'description')->first()->meta_value;
	}
}
