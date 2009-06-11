<?php defined('SYSPATH') or die('No direct script access.');

class PayPal_ExpressCheckout_Core extends PayPal {

	// Default parameters
	protected $_default = array(

		'PAYMENTACTION' => 'Sale',

	);

	public function set(array $params)
	{
		// Add the default parameters
		$params += $this->_default;

		if ( ! isset($params['AMT']))
		{
			throw new Kohana_Exception('You must provide a :param parameter for :method',
				array(':param' => 'AMT', ':method' => __METHOD__));
		}

		return $this->_post('SetExpressCheckout', $params);
	}

	public function redirect_url($token)
	{
		if ($this->_environment === 'live')
		{
			// Live environment does not use a sub-domain
			$env = '';
		}
		else
		{
			// Use the environment sub-domain
			$env = $this->_environment.'.';
		}

		// Request parameters
		$params = array(
			'cmd'   => '_express-checkout',
			'token' => $token
		);

		return 'https://www.'.$env.'paypal.com/webscr?'.http_build_query($params, NULL, '&');
	}

} // End PayPal_ExpressCheckout
