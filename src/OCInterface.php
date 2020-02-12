<?php
namespace AdrianoPedro\OCInterface;

use GuzzleHttp\Client;
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
	
	protected $client;
	protected $headers;
	
	protected $ocsv1_prefix 	= '/ocs/v1.php/cloud';
	protected $ocsv2_prefix 	= '/ocs/v2.php/cloud';
	protected $ocsshare_prefix 	= '/ocs/v1.php/apps/files_sharing/api/v1';
	
	public function __construct($url, $adminUsername, $adminPassword){
			$this->setAdmin($adminUsername, $adminPassword);
			$this->setURL($url);
			$this->setHeaders();
			$this->setClient();
	}
	
	public function setAdmin($username = '', $password = ''){
		$this->adminUsername = $username;
		$this->adminPassword = $password;
	}
	
	public function setUserData($data){
		$this->userid	= isset($data['userid']) ? $data['userid'] : null;
		$this->userdata = isset($data['userdata']) ? $data['userdata'] : [];
	}
	
	public function setURL($url){
		$this->url = $url;
	}

	public function setHeaders(){
		$this->headers = [
			'auth' 			=> [$this->adminUsername, $this->adminPassword],
       		'headers' 		=> ['Accept' => 'application/xml'],
       		'stream' 		=> false,
       		'synchronous'	=> true,       
        ];
	}

	public function setClient(){
		$this->client = new Client(['base_uri' => $this->url]);
	}
	
	
	/*************
	 ** ACTIONS **
	 ************/	

	public function enableUser(){
		if($this->client){
			$resp = $this->client->put($this->ocsv1_prefix."/users/".$this->userid."/enable", $this->headers);
		} else $resp = 'No curl connection...';
		
		return $resp;
	}

	public function disableUser(){
		if($this->client){
			$resp = $this->client->put($this->ocsv1_prefix."/users/".$this->userid."/disable", $this->headers);
		} else $resp = 'No curl connection...';
		
		return $resp;
	}

	public function getAllUsers(){
		if($this->client){
			$resp = $this->client->get($this->ocsv2_prefix."/users", $this->headers);
		} else $resp = 'No curl connection...';
		
		return $resp;
	}
	
	/************
	 ** GROUPS **
	 ***********/

	/**
	 * 
	 * @param unknown $userid
	 */
	public function getUser($userid = null){	
		if($this->client && $userid){
			$resp = $this->client->get($this->ocsv1_prefix."/users/$userid", $this->headers);
			$resp = simplexml_load_string($resp->getBody()->getContents());
		} else $resp = 'No curl connection...';
		
		return $resp;
	}

	
	/**
	 * 
	 * @param array $userdata Array with 'userid' and 'password' for the new user.
	 * @return string
	 */
	public function createUser($userdata = null){
		if($this->client && $this->userdata){
			$this->headers["form_params"] 	= $this->userdata;
			$this->headers["headers"] 		=['Accept' => 'application/x-www-form-urlencoded'];
			$resp = $this->client->request("POST",$this->ocsv1_prefix."/users", $this->headers);
			$resp = $resp->getBody()->getContents();
		} else $resp = 'No curl connection...';
		
		return $resp;
	}




	/**
	 * 
	 * @param unknown $userid
	 * @param unknown $userdata
	 * @return string
	 */
	public function updateUser(){
		$resp = [];
		if($this->client){
			foreach($this->userdata as $key => $value){
				$this->headers["form_params"] = ["key" => $key, "value" => $value];
				$r = $this->client->put($this->ocsv1_prefix."/users/".$this->userid, $this->headers);
				$resp[] = simplexml_load_string($r->getBody()->getContents());
			}
		} else {
			$resp = 'No curl connection...';
		}

		return $resp;
	}

	/**
	 * 
	 */
	public function deleteUser($userid = null){
		if($this->client && $userid){	
			$this->client->delete($this->ocsv2_prefix."/users/$userid");
			$resp = $this->client->response;
		} else $resp = 'No curl connection...';
		
		return $resp;
	}
	




	/**
	 * 
	 * @param array $userdata Array with 'userid' and 'password' for the new user.
	 * @return string
	 */
	// public function addGroup($groupdata = null){
	// 	if($this->client && $this->groupdata){
	// 		$this->headers["form_params"] 	= $this->groupdata;
	// 		$this->headers["headers"] 		=['Accept' => 'application/x-www-form-urlencoded'];
	// 		$resp = $this->client->request("POST",$this->ocsv1_prefix."/groups", $this->headers);
	// 		$resp = $resp->getBody()->getContents();
	// 	} else $resp = 'No curl connection...';
		
	// 	return $resp;
	// }

	public function getGroups(){
		if($this->client){
			$resp = $this->client->get($this->ocsv1_prefix."/groups", $this->headers);
			$resp = simplexml_load_string($resp->getBody()->getContents());
		} else $resp = 'No curl connection...';
		
		return $resp;
	}

	public function getUserGroups($userid = null){	
		if($this->client && $userid){
			$resp = $this->client->get($this->ocsv1_prefix."/users/$userid/groups", $this->headers);
			$resp = simplexml_load_string($resp->getBody()->getContents());
		} else $resp = 'No curl connection...';
		
		return $resp;
	}

	public function setUserGroups($userid = null){
		if($this->client && $userid){
			foreach((array)$this->getGroups()->data->groups->element as $group){
				$this->headers["form_params"] 	= [ "groupid" => $group ];
				$resp = $this->client->delete($this->ocsv1_prefix."/users/$userid/groups", $this->headers);
				$resp = simplexml_load_string($resp->getBody()->getContents());
			}
			foreach($this->userdata['usergroups'] as $group){
				$this->headers["form_params"] 	= [ "groupid" => $group ];
				$resp = $this->client->post($this->ocsv1_prefix."/users/$userid/groups", $this->headers);
				$resp = simplexml_load_string($resp->getBody()->getContents());
			}
		} else $resp = 'No curl connection...';
		
		return $resp;
	}

	public function removeUserGroups($userid = null){
		if($this->client && $userid){
			foreach($this->userdata['usergroups'] as $group){
				$this->headers["form_params"] 	= [ "groupid" => $group ];
				$resp = $this->client->delete($this->ocsv1_prefix."/users/$userid/groups", $this->headers);
				$resp = simplexml_load_string($resp->getBody()->getContents());
			}
		} else $resp = 'No curl connection...';
		
		return $resp;
	}



	
	/**
	 * 
	 * @param string $path
	 * @return string
	 */
	function getShares($path = ''){
		if($this->client){
			$this->client->get($this->ocsshare_prefix."/shares".$path);
			$resp = $this->client->response;
		} else $resp = 'No curl connection...';
		
		return $resp;
	}
	
	/**
	 * 
	 * @param unknown $shareid
	 * @return string
	 */
	function getShareByShareID($shareid = null){
		if($this->client){
			$this->client->get($this->ocsshare_prefix."/shares/".$shareid);
			$resp = $this->client->response;
		} else $resp = 'No curl connection...';
	
		return $resp;
	}
	
	/**
	 * 
	 * @param unknown $shareid
	 * @return string
	 */
	function deleteShare($shareid = null){
		if($this->client){
			$this->client->delete($this->ocsshare_prefix."/shares/".$shareid);
			$resp = $this->client->response;
		} else $resp = 'No curl connection...';
	
		return $resp;
	}
	
	/**
	 * 
	 * @param unknown $data
	 * @return string
	 */
	public function shareContent($data){
		if($this->client){			
			$this->client->post($this->ocsshare_prefix."/shares", $data);
			$resp = $this->client->response;
		} else $resp = 'No curl connection...';
		
		return $resp;
	}
	
	
	
}