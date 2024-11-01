<?php
namespace WebinaneCommerce\Classes;

use WebinaneCommerce\Helpers\GatewaysInterface;
use JsonSerializable;

abstract class Gateways implements GatewaysInterface, JsonSerializable {

	public $name = '';
	public $description = '';
	public $id = '';
	public $is_refundable = false;
	public $recurring = false;
	public $icon = '';
	public $heading;

	function is_active() {
		return false;
	}

	static function isSandbox() {
		return false;
	}

	/**
	 * [getTitle description]
	 * @return [type] [description]
	 */
	function getTitle() {
		$title = wpcm_get_settings()->get($this->id.'_title');
		if( ! $title ) {
			$title = $this->name;
		}

		return $title;
	}
	/**
	 * [getTitle description]
	 * @return [type] [description]
	 */
	function getDesc() {
		$desc = wpcm_get_settings()->get($this->id.'_description');
		if( ! $desc ) {
			$desc = $this->description;
		}

		return $desc;
	}

	function computedValue($resource, $att) {
		return array_get($resource, 'gateways.'.$this->id.'_gateway.'.$att);
	}

	public function jsonSerialize() {
		return [
			'heading'	=> $this->heading,
			'icon'		=> $this->icon,
			'id'		=> $this->id,
			'recurring'	=> $this->recurring,
			'label'		=> ($this->title) ? $this->title : $this->name,
			'name'		=> ($this->title) ? $this->title : $this->name,
			'description'	=> $this->description,
			'is_refundable'	=> $this->is_refundable,
		];
	}
	
}
