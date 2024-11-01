<?php

namespace WebinaneCommerce\Models;

use Illuminate\Database\Eloquent\Builder;
use WeDevs\ORM\Eloquent\Model;
use WeDevs\ORM\WP\Post;
use WebinaneCommerce\Classes\Customers;

class Order extends Post
{
	/**
	 * Name for table without prefix
	 *
	 * @var string
	 */
	protected $table = 'posts';

	/**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('post_type', function (Builder $builder) {
            $builder->where('post_type', 'orders');
        });
    }

    /**
	 * Overide parent method to make sure prefixing is correct.
	 *
	 * @return string
	 */
	public function getTable()
	{
		//In this example, it's set, but this is better in an abstract class
		if( isset( $this->table ) ){
			$prefix =  $this->getConnection()->db->prefix;
			if(!strstr($this->table, $prefix)) {
				return $prefix . $this->table;
			}

		}

		return parent::getTable();
	}

	public function items() {
		return $this->hasMany('WebinaneCommerce\Models\OrderItems', 'order_id');
	}

	public function update_meta($key, $value) {
		update_post_meta($this->ID, $key, $value);
	}

	/**
	 * [getMetaDataAttribute description]
	 * @return [type] [description]
	 */
	public function getMetaDataAttribute() {
		$return = new \stdClass;
		foreach($this->meta as $item) {
			$return->{$item->meta_key} =  maybe_unserialize( $item->meta_value );
		}

		return $return;
	}
	/**
	 * getCustomerAttribute
	 * @return [type] [description]
	 */
	public function getCustomerAttribute() {
		$customer_id = get_post_meta( $this->ID, '_wpcm_order_customer_id', true );
		if( $customer_id ) {
			// $customer = new Customers($customer_id);
			// return $customer->full_customer_info();
			return Customer::find($customer_id);
		}

		return false;
	}

	/**
	 * [getTotalAttribute description]
	 *
	 * @return [type] [description]
	 */
	public function getTotalAttribute() {
		$total = 0;

		if($this->items) {
			foreach($this->items as $item) {
				$total += (float)$item->price * (int)$item->qty;
			}
		}

		return $total;
	}
	/**
	 * [getFormattedPriceAttribute description]
	 * @return [type] [description]
	 */
	public function getFormattedPriceAttribute() {
		return webinane_cm_price_with_symbol($this->total);
	}

	/**
	 * [getAllQtyAttribute description]
	 * @return [type] [description]
	 */
	public function getAllQtyAttribute() {
		return $this->items->sum('qty');
	}

	/**
	 * [getProfileUrlAttribute description]
	 * @return [type] [description]
	 */
	public function getProfileUrlAttribute() {
		$my_acc_page = wpcm_get_settings()->get('my_account_page');

		return get_permalink($my_acc_page);
	}

	/**
	 * Get admin url for order.
	 * 
	 * @return [type] [description]
	 */
	public function adminUrl() {
		return admin_url('post.php?post='. $this->ID . '&action=edit');
	}
}