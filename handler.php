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
	$response = $browser->get('https://steamcommunity.com/search/SearchCommunityAjax?text=' . $_GET['text'] . '&filter=users&sessionid=' . $sesId . '&steamid_user=false');
	$content = $response->getContent();

	// prs search result count
	function search_result_count($genContent){
		$result = '';
		// get part where is search_result_count field 
		$element = substr($genContent, strpos($genContent, 'search_result_count'), 42);
		// part character from string to  arr
		$toArr = str_split($element);
		// transform characters from arr to type int for element characters with type string . For remain only cifres
		foreach ($toArr as $key => $value) {
			//check if character is numeric
			if (is_numeric($value)) {
				$result .= $value;
			}
		}
		return $result;
	}
	// print_r(search_result_count($content));


	$search_row = explode('\t\t\t<div class=\"search_row\">\r\n\t', $content);
	$search_row_usurl = [];
	foreach ($search_row as $key => $value) {
		$search_row_usurl[] = substr($value, strrpos($value, ' class=\"avatarMedium\"><a href=\"https:\/\/steam') + 34, 150) . '<br>';
	}
	print_r($search_row_usurl);

	// print '<pre>';
	// print_r(explode('\t\t\t<div class=\"search_row\">\r\n\t', $content));
	// print '</pre>';
// }

class CommunityResult {
	public $success;
	public $search_text;
	public $search_result_count;
	public $search_filter;
	public $search_page;
	public $html;
}