<?php

namespace WebinaneCommerce\Models;

use WeDevs\ORM\Eloquent\Model;
use WeDevs\ORM\WP\User;
use WebinaneCommerce\Models\CustomerMeta;

class Token extends Model
{
	/**
	 * Name for table without prefix
	 *
	 * @var string
	 */
	protected $table = 'wpcommerce_payment_tokens';

	/**
	 * Columns that can be edited - IE not primary key or timestamps if being used
	 */
	protected $fillable = [
		'user_id',
		'email',
		'name',
	];

	protected $appends = [];

	/**
	 * Disable created_at and update_at columns, unless you have those.
	 */
	public $timestamps = false;

	/** Everything below this is best done in an abstract class that custom tables extend */

	/**
	 * Set primary key as ID, because WordPress
	 *
	 * @var string
	 */
	protected $primaryKey = 'id';

	/**
	 * Make ID guarded -- without this ID doesn't save.
	 *
	 * @var string
	 */
	protected $guarded = [ 'id' ];

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
			if(! strstr($this->table, $prefix)) {
				return $prefix . $this->table;
			}

		}

		return parent::getTable();
	}


	/**
	 * Meta relationship.
	 * 
	 * @return [type] [description]
	 */
	public function user() {
		return $this->belongsTo(User::class, 'user_id');
	}
	

}