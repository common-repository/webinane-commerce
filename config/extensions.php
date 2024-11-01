<?php

return [
	'webinane-stripe/webinane-stripe.php' => [
		'load'	=> 'includes/class-stripe.php',
		'callback'	=> ['WebinaneStripe\Stripe', 'init']
	],
	'webinane-2co/webinane-2co.php' => [
		'load'	=> 'includes/class-2co.php',
		'callback'	=> ['Webinane2Co\TwoCheckout', 'init']
	],
	'webinane-paystack/webinane-paystack.php' => [
		'load'	=> 'includes/class-paystack.php',
		'callback'	=> ['WebinanePaystack\Paystack', 'init']
	],
	
];