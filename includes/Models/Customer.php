<?php

namespace WebinaneCommerce\Models;

use WeDevs\ORM\Eloquent\Model;
use WeDevs\ORM\WP\User;
use WebinaneCommerce\Models\CustomerMeta;

class Customer extends Model
{
	/**
	 * Name for table without prefix
	 *
	 * @var string
	 */
	protected $table = 'wpcommerce_customers';

	/**
	 * Columns that can be edited - IE not primary key or timestamps if being used
	 */
	protected $fillable = [
		'user_id',
		'email',
		'name',
	];

	protected $appends = ['meta_data'];

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
	public function metas() {
		return $this->hasMany(CustomerMeta::class);
	}

	/**
	 * Meta relationship.
	 * 
	 * @return [type] [description]
	 */
	public function getMetaDataAttribute() {
		return $this->metas->pluck('meta_value', 'meta_key');
	}
	

	/**
	 * Meta relationship.
	 * 
	 * @return [type] [description]
	 */
	public function user() {
		return $this->belongsTo(User::class, 'user_id');
	}
	
	/**
	 * Adjust the name from meta first and last name.
	 *
	 * @return [type] [description]
	 */
	public function getFullNameAttribute() {
		$name = '';

		$first = $this->metas->where('meta_key', 'billing_first_name')->first();
		if($first) {
			$name = $first->meta_value;
		}
		if($last = $this->metas->where('meta_key', 'billing_last_name')->first()) {
			$name .= ' ' . $last->meta_value;
		}
		return ($this->name) ? $this->name : $name;
	}

	/**
	 * Get Address.
	 * 
	 * @return [type] [description]
	 */
	public function getAddressAttribute() {
		$address = $this->metas->where('meta_key', 'billing_address_line_1')->first();
		if( $address ) {
			return $address->meta_value;
		}

		return null;
	}

	/**
	 * Get City.
	 * 
	 * @return [type] [description]
	 */
	public function getCityAttribute() {
		$city = $this->metas->where('meta_key', 'billing_city')->first();
		if( $city ) {
			return $city->meta_value;
		}

		return null;
	}
	/**
	 * Get Country.
	 * 
	 * @return [type] [description]
	 */
	public function getCountryAttribute() {
		$country = $this->metas->where('meta_key', 'billing_base_country')->first();

		if( $country ) {

			$name = array_get(wpcm_countries()->toArray(), $country->meta_value, 'United States');

			if($name) {
				return $name;
			} else {
				return $country->meta_value;
			}
		}

		return null;
	}
	/**
	 * Get zip.
	 * 
	 * @return [type] [description]
	 */
	public function getZipAttribute() {
		$zip = $this->metas->where('meta_key', 'billing_zip')->first();
		if( $zip ) {
			return $zip->meta_value;
		}

		return null;
	}
	/**
	 * Get State.
	 * @return [type] [description]
	 */
	public function getStateAttribute() {
		$state = $this->metas->where('meta_key', 'billing_state')->first();
		if( $state ) {
			return $state->meta_value;
		}

		return null;
	}
	
	/**
	 * Get all metas as associative array.
	 * 
	 * @return [type] [description]
	 */
	public function getAllMetaAttribute() {
		$metas = [];

		foreach($this->metas as $met) {
			$metas[$met->meta_key] = $met->meta_value;
		}

		return $metas;
	}
}