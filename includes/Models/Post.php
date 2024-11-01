<?php

namespace WebinaneCommerce\Models;

use Illuminate\Database\Eloquent\Builder;
use WeDevs\ORM\Eloquent\Model;
use WeDevs\ORM\WP\Post as WPPost;
use WebinaneCommerce\Classes\Customers;
use WebinaneCommerce\Models\OrderItems;

class Post extends WPPost {


	/**
	 * Get meta fields from the post
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function orders() {
		return $this->hasMany( OrderItems::class, 'post_id' );
	}
}
