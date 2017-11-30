<?php 
/*
	Search is performed by several etapes;
	1)we send request to https://steamcommunity.com for open session and it save.
	2)after we send request for searching users using several parametres a)texh which we search. b)sessionId which we open before.
*/
require('vendor/autoload.php');
use Buzz\Browser;
use Buzz\Client\Curl;
use Buzz\Listener\CookieListener;

if(!empty($_GET['text'])) {
	$search_text = $_GET['text'];
}

// if(!empty($_GET['text'])) { 
	$browser = new Browser();
	$client = new Curl();
	$client->setMaxRedirects(0);
	$browser->setClient($client);
	// Create CookieListener
	$listener = new CookieListener();
	$browser->addListener($listener);
	// This URL set  Cookies
	$response = $browser->get('https://steamcommunity.com');
	// Sort data from respons and after we found script which contain session id we save it in variable
	$sesId = substr($response, strrpos($response, 'g_sessionID = ')+15, 24);
	// request with parametres to server
	$response = $browser->get('https://steamcommunity.com/search/SearchCommunityAjax?text=' . $search_text . '&filter=users&sessionid=' . $sesId . '&steamid_user=false');
	$content = $response->getContent();

	/*-----general entyty---------*/
	class CommunityResult {
		public $success;
		public $search_text;
		public $search_result_count;
		public $search_page;
		public $users;
		public function __construct($success, $search_text, $search_result_count, $search_page){
			$this->success = $success;
			$this->search_text = $search_text;
			$this->search_result_count = $search_result_count;
			$this->search_page = $search_page;
		}

		public function setUserData($obj){
			$this->users[] = $obj;
		}
	}
	/*-----END general entity---------*/

	/*-----user entity---------*/
	class UserData {
		public $user_url;
		public $user_avatar;
		public $user_person_info;
		public $user_match_info;
		public function __construct($user_url, $user_avatar, $user_person_info, $user_match_info) {
			$this->user_url = $user_url;
			$this->user_avatar = $user_avatar;
			$this->user_person_info = $user_person_info;
			$this->user_match_info = $user_match_info;
		}
	}
	/*-----END user entity---------*/

	/*-----prs fields count---------*/
	function fieldCount($genContent, $fieldSearched, $indent){
		$result = '';
		// get part where is success field 
		$element = substr($genContent, strpos($genContent, $fieldSearched), $indent);
		// part character from string to  arr
		$toArr = str_split($element);
		// transform characters from arr to type int for element characters with type string . For remain only cifres
		foreach ($toArr as $key => $value) {
			//check if character is numeric
			if (is_numeric($value)) {
				$result .= $value;
			}
		}
		// will be return count  of search result 
		return $result;
	}
	/*-----END prs fields count---------*/

	/*create global objec which will be contain sorted data from respons*/
	$ComRes = new CommunityResult(fieldCount($content, 'success', 20), $search_text, fieldCount($content, 'search_result_count', 42), fieldCount($content, 'search_page', 20));

	//much manipulation for sort data
	$search_row = explode('\t\t\t<div class=\"search_row\">\r\n\t', $content);
	foreach ($search_row as $key => $value) {
		//get stem url 
		$crudity_user_id = substr($value, strrpos($value, ' class=\"avatarMedium\"><a href=\"https:\/\/steam') + 34, 173);
		$fromClip = strrpos($crudity_user_id, '\"><img src=\"htt');
		$toClip = strlen($crudity_user_id) - strrpos($crudity_user_id, '<img src=\"https:') +3;
		$clip = substr_replace($crudity_user_id, ' ', $fromClip, $toClip);
		$userUrl =  str_replace('\/' , '/', $clip);
		//END get stem url 

		$user = new UserData($userUrl, 'user_avatar', 'user_person_info', 'user_match_info');
		$ComRes->setUserData($user);
	}
	print_r($ComRes);
	// print_r($content);

// }

