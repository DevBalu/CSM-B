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
	// This URL set Cookies
	$response = $browser->get('https://steamcommunity.com');
	// Sort data from respons and after we found script which contain session id we save it in variable
	$sesId = substr($response, strrpos($response, 'g_sessionID = ')+15, 24);
	// request with parametres to server
	$response = $browser->get('https://steamcommunity.com/search/SearchCommunityAjax?text=' . $search_text . '&filter=users&sessionid=' . $sesId . '&steamid_user=false');
	$content = $response->getContent();

	/*-----GENERAL ENTITY---------*/
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
	/*-----END GENERAL ENTITY---------*/

	/*-----USER ENTITY---------*/
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
	/*-----END USER ENTITY---------*/

	/*-----PRS FIELDS COUNT---------*/
	function fieldCount($genContent, $fieldSearched, $indent){
		$result = '';
		// get part where is success field 
		$element = substr($genContent, strpos($genContent, $fieldSearched), $indent);
		// part character from string to  arr
		$toArr = str_split($element);
		// transform characters from arr in type int for remain only cifres
		foreach ($toArr as $key => $value) {
			//check if character is numeric
			if (is_numeric($value)) {
				$result .= $value;
			}
		}
		// will be return count  of search result 
		return $result;
	}
	/*-----END PRS FIELDS COUNT---------*/

	//clip iteration (user_match_info)
	function clip_iter_value($iter, $findTextPos){
		$result = '';
		$fromClip = strrpos($iter, $findTextPos);
		$toClip = strlen($iter) - strrpos($iter, $findTextPos);
		$result = substr($iter, $fromClip, $toClip);
		return $result;
	}
	//END clip iteration(user_match_info)

	//clip spans(user_match_info)
	function clipSpans($arr){
		$result = [];
		for($i = 1; $i < sizeof($arr); $i++){
			$fromClip = strrpos($arr[$i], '<\/span>');
			$toClip = strlen($arr[$i]) - strrpos($arr[$i], '<\/span>');
			$clip = substr_replace($arr[$i], ' ', $fromClip, $toClip);
			$result[] = $clip;
		}

		return $result;
	}
	//clip spans (user_match_info)

	/*GLOBAL OBJECT*/
	//create global objec which will be contain sorted data from respons
	$ComRes = new CommunityResult(fieldCount($content, 'success', 20), $search_text, fieldCount($content, 'search_result_count', 42), fieldCount($content, 'search_page', 20));

	//MUCH MANIPULATON FOR SORT DATA
	$search_row = explode('\t\t\t<div class=\"search_row\">\r\n\t', $content);
	for($i = 1; $i < sizeof($search_row); $i++){
		/*STEAM URL*/ 
		$crudity_user_id = substr($search_row[$i], strrpos($search_row[$i], ' class=\"avatarMedium\"><a href=\"https:\/\/steam') + 34, 173);
		$fromClip = strrpos($crudity_user_id, '\"><img src=\"htt');
		$toClip = strlen($crudity_user_id) - strrpos($crudity_user_id, '<img src=\"https:') +3;
		$clip = substr_replace($crudity_user_id, ' ', $fromClip, $toClip);
		$userUrl =  str_replace('\/' , '/', $clip);
		/*END STEAM URL*/ 

		//USER AVATAR
		$crud_link_avatar = substr($search_row[$i], strrpos($search_row[$i], 'https:\/\/steamcdn-a.akamaihd.net\/steamcommunity\/public\/images'), 131);
		$userLinkAvatar =  str_replace('\/' , '/', $crud_link_avatar);
		//END USER AVATAR

		//USER MUCH INFO
		$crud_user_match_info = clip_iter_value($search_row[$i] , 'Also known as:') . "\n";
		$explode_spans = explode('<span style=\"color: whitesmoke\">', $crud_user_match_info);
		//clip spans
		print_r (clipSpans($explode_spans));
		//END clip spans
		//END USER MUCH INFO

		$user = new UserData($userUrl, $userLinkAvatar, 'user_person_info', 'user_match_info');
		$ComRes->setUserData($user);
	}
	// print_r($ComRes);
	// print_r($content);
	//MUCH MANIPULATON FOR SORT DATA

// }