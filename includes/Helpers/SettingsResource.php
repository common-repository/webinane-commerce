<?php

namespace WebinaneCommerce\Helpers;

use WebinaneCommerce\Fields\Field;

class SettingsResource
{
	public $fields;
	public $settings;
	public $values = [];

	public function __construct($fields, $settings) {
		$this->fields = $fields;
		$this->settings = $settings;
	}

	public function resolve() {
		$newfield = [];

		$newfield = $this->get_fields($this->fields);
		return $newfield;

	}

	public function get_fields($data) {
		// $newdata = []
		foreach($data as $key => $value) {
			
			if(isset($value['children'])) {
				$data[$key]['children'] = $this->get_fields($value['children']);
			} elseif( isset($value['tabs']) ) {
				$data[$key]['tabs'] = $this->get_fields($value['tabs']);
			} elseif( isset($value['fields']) ) {
				$data[$key]['fields'] = $this->process_fields($value['fields']);
			}
		}

		return $data;
	}

	/**
	 * Process Fields.
	 * 
	 * @param  [type] $fields [description]
	 * @return [type]         [description]
	 */
	private function process_fields($fields) {
		$newfield = [];
		foreach ($fields as $key => $field) {
			if($field instanceof Field) {
				$field->resolve($this->settings);
				$newfield[$key] = $field;
			} elseif($field['type'] === 'gateway_tab') {
				$newfield[$key] = $field;
				$newfield[$key]['tabs'] = $this->get_fields($field['tabs']);
			} else {
				$field['value'] = $this->resolve_value($field);
				$newfield[$key] = $field;
			} 
		}
		return $newfield;
	}

	/**
	 * Resolve value.
	 *
	 * @param  [type] $field [description]
	 * @return [type]        [description]
	 */
	private function resolve_value($field) {

		if(isset($field['parent'])) {
			$key = $field['parent']. '.'. $field['id'];
			$value = array_get($this->settings, $key);
			$this->add_to_values($key, $value, $field);
		} else {
			$value = array_get($this->settings, $field['id']);
			$this->add_to_values($field['id'], $value, $field);
		}

		return $value ? $value : array_get($field, 'default');
	}

	/**
	 * [add_to_values description]
	 * 
	 * @param [type] $key   [description]
	 * @param [type] $value [description]
	 * @param [type] $field [description]
	 */
	private function add_to_values($key, $value, $field) {
		$value = $value ? $value : array_get($field, 'default');
		$this->values = array_add($this->values, $key, $value);
	}
}