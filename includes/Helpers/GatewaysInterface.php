<?php
namespace WebinaneCommerce\Helpers;

// Declare the interface 'iTemplate'
interface GatewaysInterface
{
    public function is_active();
    public static function settings($settings = array());
    public static function gateway($gateways = array());
    public function getTitle();
    public function getDesc();
}
