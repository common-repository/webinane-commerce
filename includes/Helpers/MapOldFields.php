<?php

namespace WebinaneCommerce\Helpers;


trait MapOldFields
{
	public function map($fields) {

		foreach($fields as $key => $field) {
			if($children = array_get($field, 'children')) {
				$fields[$key]['children'] = static::mapChildren($children);
			} else {
				$fields[$key]['fields'] = static::fields($field['fields']);
			}
		}

		return $fields;
	}

	private function fields($fields) {
		foreach($fields as $key => $field) {
			if(array_get($field, 'type') == 'gateway_tab') {
				$fields[$key] = static::mapTabData($field);
			} else {
				$fields[$key] = static::setFieldData($field);
			}
		}


		return $fields;
	}

	private function mapChildren($children) {

		foreach($children as $k => $child) {
			$children[$k]['fields'] = static::fields($child['fields']);
		}

		return $children;
	}

	private function mapTabData($gateway_tab) {
		foreach($gateway_tab['tabs'] as $index => $tab) {
			if(isset($tab['title'])) {
				$gateway_tab['tabs'][$index]['label'] = $tab['title'];
				$gateway_tab['tabs'][$index]['heading'] = $tab['title'];
			}
			$tab['fields'] = static::hasSandboxField($tab['fields']);
			$gateway_tab['tabs'][$index]['fields'] = static::fields($tab['fields']);
		}
		// printr($gateway_tab);
		return $gateway_tab;
	}

	private function setFieldData($field) {
		if(isset($field['name'])) {
			$field['label'] = $field['name'];
			unset($field['name']);
		}
		if(isset($field['desc'])) {
			$field['desc'] = $field['desc'];
			unset($field['desc']);
		}

		$field = static::setFieldType($field);

		return $field;
	}

	private function hasSandboxField($fields) {
		foreach($fields as $key => $field) {
			if(strstr($field['id'], 'sandbox_status')) {
				unset($fields[$key]);
				break;
			}
		}
		return $fields;
	}

	private function setFieldType($field) {

		$type = array_get($field, 'is');

		switch ($type) {
			case 'wpcm-toggle':

				$field['type'] = 'el-switch';
				unset($field['is']);
				break;
			case 'wpcm-text':
				$field['type'] = 'el-input';
				unset($field['is']);
				break;
			case 'wpcm-media':
				$field['type'] = 'media';
				unset($field['is']);
				break;
			case 'wpcm-number':
				$field['type'] = 'el-input';
				$field['props']	= ['type' => 'number'];
				unset($field['is']);
				break;
			case 'wpcm-select':
				$field['type'] = 'el-select';
				unset($field['is']);
				break;
			
			default:
				# code...
				break;
		}

		return $field;
	}

	public function make(...$arguments) {
		return $this->map(...$arguments);
	}
}