<?php
/**
 * Order items model.
 *
 * @package WordPress
 */

namespace WebinaneCommerce\Models;

use WeDevs\ORM\Eloquent\Model;
use WebinaneCommerce\Models\Order;
use WebinaneCommerce\Models\Post;

/**
 * Order items model
 */
class OrderItems extends Model {

	/**
	 * Name for table without prefix
	 *
	 * @var string
	 */
	protected $table = 'wpcommerce_order_items';

	/**
	 * Name for table without prefix
	 *
	 * @var string
	 */
	protected $appends = array( 'date' );


	/**
	 * Columns that can be edited - IE not primary key or timestamps if being used
	 *
	 * @var $fillable
	 */
	protected $fillable = array(
		'order_item_id',
		'order_item_name',
		'qty',
		'price',
		'base_price',
		'currency',
		'order_item_type',
		'order_id',
		'post_id',
	);

	/**
	 * Disable created_at and update_at columns, unless you have those.
	 *
	 * @var $timestamps
	 */
	public $timestamps = false;

	/** Everything below this is best done in an abstract class that custom tables extend */

	/**
	 * Set primary key as ID, because WordPress
	 *
	 * @var $primaryKey
	 */
	protected $primaryKey = 'order_item_id';

	/**
	 * Make ID guarded -- without this ID doesn't save.
	 *
	 * @var $guarded
	 */
	protected $guarded = array( 'order_item_id' );

	/**
	 * Overide parent method to make sure prefixing is correct.
	 *
	 * @return string
	 */
	public function getTable() {
		// In this example, it's set, but this is better in an abstract class.
		if ( isset( $this->table ) ) {
			$prefix = $this->getConnection()->db->prefix;
			if ( ! strstr( $this->table, $prefix ) ) {
				return $prefix . $this->table;
			}
		}

		return parent::getTable();
	}

	/**
	 * Orders
	 *
	 * @return [type] [description]
	 */
	public function order() {
		return $this->belongsTo( Order::class, 'order_id' );
	}

	/**
	 * Orders.
	 *
	 * @return object
	 */
	public function post() {
		return $this->belongsTo( Post::class, 'post_id' );
	}

	public function getDateAttribute() {
		return $this->order->post_date;
	}

	public function getCustomerAttribute() {
		return $this->order->customer;
	}

	public function getTotalAttribute() {
		return $this->qty * $this->price;
	}
}
