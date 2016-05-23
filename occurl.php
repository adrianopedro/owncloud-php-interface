<?php
class ocCURL {
	protected $url = '';
	protected $authUsername = '';
	protected $authPassword = '';
	protected $postFields	= array();
	
	public function __construct($url, $user, $pass, $data){
		$this->url 			= $url;	
		$this->authUsername = $user;
		$this->authPassword	= $pass;
		$this->postFields	= $data;
	}
	
	public function post(){
		// Get cURL resource
		$curl = curl_init();
		// Set some options - we are passing in a useragent too here
		curl_setopt_array($curl, array(
				CURLOPT_URL 			=> $this->url,
				CURLOPT_RETURNTRANSFER 	=> 1,
				CURLOPT_HTTPAUTH		=> CURLAUTH_BASIC,
				CURLOPT_USERPWD			=> $this->authUsername . ":" . $this->authPassword,
		));
		if(isset($this->postFields) && count($this->postFields) > 0) curl_setopt($curl, CURLOPT_POSTFIELDS,$this->postFields);
		
		// Send the request & save response to $resp
		$resp = curl_exec($curl);
		// Close request to clear up some resources
		curl_close($curl);
	
		return $resp;
	}
}
?>