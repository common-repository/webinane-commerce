<?php

namespace WebinaneCommerce\Helpers;

trait Rulable
{
	public $rules = [];


	public function withRules(array $rules = []) {
		$this->$rules = $rules;

		return $this;
	}

	public function rules() {
		return $this->rules;
	}
}