<?php
namespace DBugIT\OCInterface;

use Curl\Curl;

/********************************
 ** load composer autoload.php **
 *******************************/
// require __DIR__ . '/vendor/autoload.php';


class OCInterface {
	
	/** @var string Administrator username for owncloud authentication **/ 
	protected $adminUsername = '';
	
	/** @var string Administrator password for owncloud authentication **/
	protected $adminPassword = '';
	
	protected $url		= '';
	protected $userid	= '';
	protected $data		= array();
	protected $userdata = array();
	
	protected $curl;
	
	protected $ocsv1_prefix 	= '/ocs/v1.php/cloud';
	protected $ocsv2_prefix 	= '/ocs/v2.php/cloud';
	protected $ocsshare_prefix 	= '/ocs/v1.php/apps/files_sharing/api/v1';
	
	public function __construct($url, $adminUsername, $adminPassword){
			$this->setAdmin($adminUsername, $adminPassword);
			$this->setURL($url);
			$this->connect();
	}
	
	public function setAdmin($username = '', $password = ''){
		$this->adminUsername = $username;
		$this->adminPassword = $password;
	}
	
	public function setUserData($data){
		$this->userid	= isset($data['userid']) ? $data['userid'] : null;
		$this->userdata = isset($data['userdata']) ? $data['userdata'] : array();
	}
	
	public function setURL($url){
		$this->url = $url;
	}
	
	private function connect(){
		$curl = new Curl();
		$curl->setBasicAuthentication($this->adminUsername, $this->adminPassword);
		$curl->setUserAgent('');
		$curl->setReferrer('');
		$curl->setHeader('X-Requested-With', 'XMLHttpRequest');
		
		$this->curl = $curl;
	}
	
	
	
	/*************
	 ** ACTIONS **
	 ************/	
	public function getAllUsers(){
		if($this->curl){
			$this->curl->get($this->url.$this->ocsv2_prefix."/users");
			$resp = $this->curl->response;
		} else $resp = 'No curl connection...';
		
		return $resp;
	}
	
	/**
	 * 
	 * @param unknown $userid
	 */
	public function getUser($userid = null){	
		if($this->curl && $userid){
			$this->curl->get($this->url.$this->ocsv2_prefix."/users/$userid");
			$resp = $this->curl->response;
		} else $resp = 'No curl connection...';
		
		return $resp;
	}
	
	/**
	 * 
	 * @param array $userdata Array with 'userid' and 'password' for the new user.
	 * @return string
	 */
	public function createUser($userdata = null){
		if($this->curl && $userdata){
			$this->curl->post($this->url.$this->ocsv2_prefix."/users", $userdata);				
			$resp = $this->curl->response;
		} else $resp = 'No curl connection...';
		
		return $resp;
	}
	
	/**
	 * 
	 */
	public function deleteUser($userid = null){
		if($this->curl && $userid){
			$this->curl->delete($this->url.$this->ocsv2_prefix."/users/$userid");
			$resp = $this->curl->response;
		} else $resp = 'No curl connection...';
		
		return $resp;
	}
	
	/**
	 * 
	 * @param unknown $userid
	 * @param unknown $userdata
	 * @return string
	 */
	public function updateUser($userid = null, $userdata = null){
		if($this->curl){
			$this->curl->put($this->url.$this->ocsv2_prefix."/users/".$this->userid, $this->userdata);
			$resp = $this->curl->response;
		} else $resp = 'No curl connection...';
		
		return $resp;
	}
	
	/**
	 * 
	 * @param string $path
	 * @return string
	 */
	function getShares($path = ''){
		if($this->curl){
			$this->curl->get($this->url.$this->ocsshare_prefix."/shares".$path);
			$resp = $this->curl->response;
		} else $resp = 'No curl connection...';
		
		return $resp;
	}
	
	/**
	 * 
	 * @param unknown $shareid
	 * @return string
	 */
	function getShareByShareID($shareid = null){
		if($this->curl){
			$this->curl->get($this->url.$this->ocsshare_prefix."/shares/".$shareid);
			$resp = $this->curl->response;
		} else $resp = 'No curl connection...';
	
		return $resp;
	}
	
	/**
	 * 
	 * @param unknown $shareid
	 * @return string
	 */
	function deleteShare($shareid = null){
		if($this->curl){
			$this->curl->delete($this->url.$this->ocsshare_prefix."/shares/".$shareid);
			$resp = $this->curl->response;
		} else $resp = 'No curl connection...';
	
		return $resp;
	}
	
	/**
	 * 
	 * @param unknown $data
	 * @return string
	 */
	public function shareContent($data){
		if($this->curl){			
			$this->curl->post($this->url.$this->ocsshare_prefix."/shares", $data);
			$resp = $this->curl->response;
		} else $resp = 'No curl connection...';
		
		return $resp;
	}
	
	
	
}