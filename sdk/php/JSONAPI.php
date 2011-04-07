<?php

/**
 * A PHP class for access Minecraft servers that have Bukkit with the {@link http://github.com/alecgorge/JSONAPI JSONAPI} plugin installed.
 * 
 * This class handles everything from key creation to URL creation to actually returning the decoded JSON as an associative array.
 * 
 * @author Alec Gorge <alecgorge@gmail.com>
 * @version Alpha 5
 * @link http://github.com/alecgorge/JSONAPI
 * @package JSONAPI
 * @since Alpha 5
 */
class JSONAPI {
	private $host;
	
	private $port;
	
	private $salt;
	
	private $username;
	
	private $password;
	
	private $urlFormats = array(
		"call" => "http://%s:%s/api/call?method=%s&args=%s&key=%s"
		"callMultiple" => "http://%s:%s/api/call-multiple?method=%s&args=%s&key=%s"
	);
	
	/**
	 * Creates a new JSONAPI instance.
	 */
	public function __construct ($host, $port, $uname, $pword, $salt) {
		$this->host = $host;
		$this->port = $host;
		$this->username = $uname;
		$this->password = $pword;
		$this->salt = $salt;
	}
	
	/**
	 * Generates the proper SHA256 based key from the given method suitable for use as the key GET parameter in a JSONAPI API call.
	 * 
	 * @param string $method The name of the JSONAPI API method to generate the key for.
	 * @return string The SHA256 key suitable for use as the key GET parameter in a JSONAPI API call.
	 */
	public function createKey($method) {
		return hash('sha256', $this->username . $method . $this->password . $this->salt);
	}
	
	/**
	 * Generates the proper URL for a standard API call the given method and arguments.
	 * 
	 * @param string $method The name of the JSONAPI API method to generate the URL for.
	 * @param array $args An array of arguments that are to be passed in the URL.
	 * @return string A proper standard JSONAPI API call URL. Example: "http://localhost:20059/api/call?method=methodName&args=jsonEncodedArgsArray&key=validKey".
	 */
	public function makeURL($method, array $args) {
		return sprintf($this->urlFormats["call"], $this->host, $this->port, rawurlencode($method), rawurlencode(json_encode($args)), $this->createKey($method));
	}
	
	/**
	 * Generates the proper URL for a multiple API call the given method and arguments.
	 * 
	 * @param array $methods An array of strings, where each string is the name of the JSONAPI API method to generate the URL for.
	 * @param array $args An array of arrays, where each array contains the arguments that are to be passed in the URL.
	 * @return string A proper multiple JSONAPI API call URL. Example: "http://localhost:20059/api/call-multiple?method=[methodName,methodName2]&args=jsonEncodedArrayOfArgsArrays&key=validKey".
	 */
	public function makeURLMultiple(array $methods, array $args) {
		return sprintf($this->urlFormats["callMultiple"], $this->host, $this->port, rawurlencode($method), rawurlencode(json_encode($args)), $this->createKey($method));
	}
	
	/**
	 * Calls the single given JSONAPI API method with the given args.
	 * 
	 * @param string $method The name of the JSONAPI API method to call.
	 * @param array $args An array of arguments that are to be passed.
	 * @return array An associative array representing the JSON that was returned.
	 */
	public function call($method, array $args) {
		if(is_array($method)) {
			$this->callMultiple($method, $args);
		}
	
		foreach((array)$args as &$v) {
			if(is_numeric($v)) {
				$v = (float)$v;
			}
		}
		
		$url = $this->makeURL($method, $args);

		return json_decode($this->curl($url), true);
	}
	
	private function curl($url) {
		$c = curl_init($url);
		curl_setopt($c, CURLOPT_PORT, $this->port);
		curl_setopt($c, CURLOPT_HEADER, false);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($c);
		curl_close($c);
		return $result;
	}
	
	/**
	 * Calls the given JSONAPI API methods with the given args.
	 * 
	 * @param array $methods An array strings, where each string is the name of a JSONAPI API method to call.
	 * @param array $args An array of arrays of arguments that are to be passed.
	 * @throws Exception When the length of the $methods array and the $args array are different, an exception is thrown.
	 * @return array An array of associative arrays representing the JSON that was returned.
	 */
	public function callMultiple(array $methods, array $args) {
		if(count($methods) !== count($args)) {
			throw new Exception("The length of the arrays \$methods and \$args are different! You need an array of arguments for each method!");
		}
	
		foreach((array)$args as &$v) {
			foreach((array)$v as &$x) {
				if(is_numeric($x)) {
					$x = (float)$x;
				}
			}
		}
		
		
	}
}