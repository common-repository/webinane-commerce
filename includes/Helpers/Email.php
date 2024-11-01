<?php

namespace WebinaneCommerce\Helpers;

class Email
{
	protected $subject;
	protected $item_image;
	protected $item_title;
	protected $logo;
	protected $btn_link;
	protected $btn_text;
	protected $desc;
	protected $total;
	protected $quantity;

	/**
	 * Set email subject.
	 * 
	 * @param [type] $subject [description]
	 */
	function setSubject($subject) {
		$this->subject = $subject;
	}

	/**
	 * Get email subject
	 * 
	 * @return [type] [description]
	 */
	function getSubject() {
		return $this->subject;
	}

	/**
	 * Set item image.
	 * @param [type] $url [description]
	 */
	function setItemImage($url) {
		$this->item_image = $url;
	}

	/**
	 * get item image
	 * @return [type] [description]
	 */
	function getItemImage() {
		return $this->item_image;
	}
}