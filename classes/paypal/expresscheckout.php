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

} // End PayPal_ExpressCheckout
