<?php defined('SYSPATH') or die('No direct script access.');

abstract class PayPal_Core {

	public $instances = array();

	public static function instance($type)
	{
		if ( ! isset($instances[$type]))
		{
			// Set the class name
			$class = 'PayPal_'.$type;

			// Load default configuration
			$config = Kohana::config('paypal');

			// Create a new PayPal instance with the default configuration
			$instances[$type] = new $class($config['username'], $config['password'], $config['signature'], $config['environment']);
		}

		return $instances[$type];
	}

	// API username
	protected $_username;

	// API password
	protected $_password;

	// API signature
	protected $_signature;

	// Environment type
	protected $_environment = FALSE;

	public function __construct($username, $password, $signature, $environment = 'live')
	{
		// Set the API username and password
		$this->_username = $username;
		$this->_password = $password;

		// Set the API signature
		$this->_signature = $signature;

		// Set the environment
		$this->_environment = $environment;
	}

	public function api_url()
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

		return 'https://api-3t.'.$env.'paypal.com/nvp';
	}

	protected function _post($method, array $params)
	{
		// Create POST data
		$post = array(
			'METHOD'    => $method,
			'VERSION'   => 51.0,
			'USER'      => $this->_username,
			'PWD'       => $this->_password,
			'SIGNATURE' => $this->_signature,
		) + $params;

		// Create a new curl instance
		$curl = curl_init();

		// Set curl options
		curl_setopt_array($curl, array(
			CURLOPT_URL            => $this->api_url(),
			CURLOPT_POST           => TRUE,
			CURLOPT_POSTFIELDS     => http_build_query($post, NULL, '&'),
			CURLOPT_SSL_VERIFYPEER => FALSE,
			CURLOPT_SSL_VERIFYHOST => FALSE,
			CURLOPT_RETURNTRANSFER => TRUE,
		));

		if (($response = curl_exec($curl)) === FALSE)
		{
			// Get the error code and message
			$code  = curl_errno($curl);
			$error = curl_error($curl);

			// Close curl
			curl_close($curl);

			throw new Kohana_Exception('PayPal API request for :method failed: :error (:code)',
				array(':method' => $method, ':error' => $error, ':code' => $code));
		}

		// Parse the response
		parse_str($response, $data);

		if ( ! isset($data['ACK']) OR strpos($data['ACK'], 'Success') === FALSE)
		{
			throw new Kohana_Exception('PayPal API request for :method failed: :error (:code)',
				array(':method' => $method, ':error' => $data['L_LONGMESSAGE0'], ':code' => $data['L_ERRORCODE0']));
		}

		return $data;
	}

} // End PayPal
