<?php 
/*
/--------------------------------------------------------------------------------------
/					Short describtion
/--------------------------------------------------------------------------------------
/	Search is performed by several etapes;
/	1)we send request to https://steamcommunity.com for open session and it save.
/	2)after we send request for searching users using several parametres a)texh which we search. b)sessionId which we open before. с) page searched
*/

require('../vendor/autoload.php');
use Buzz\Browser;
use Buzz\Client\Curl;
use Buzz\Listener\CookieListener;

if(!empty($_GET['text'])) {
	$search_text = $_GET['text'];
}

if (!empty($_GET['page'])) {
	$search_p = '&page=' . $_GET['page'];
} else {
	$search_p = '';
}

// if(!empty($_GET['text'])) { 
/*-----------------------------------------------------------------------------------------------------------VIRTUAL BROWSER---------------------------------------------*/
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
	$response = $browser->get('https://steamcommunity.com/search/SearchCommunityAjax?text=' . $search_text . '&filter=users&sessionid=' . $sesId . '&steamid_user=false' . $search_p);
	$content = $response->getContent();
/*-------------------------------------------------------------------------------------------------------END VIRTUAL BROWSER---------------------------------------------*/

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

/*------------------------------------------------------------------------------METHODS-------------------------------------------------------------------------*/

/*
/--------------------------------------------------------------------------------------
/				method fieldCount
/--------------------------------------------------------------------------------------
/ 	USED: for obtain feld "success" & "search_result count"
/	Return number of results found
*/
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


/*
/--------------------------------------------------------------------------------------
/					method clip_iter_value
/--------------------------------------------------------------------------------------
/	USED: everything
/	DESC:
/		cut value in each iteration (user_match_info)
/	RETURN: truncated string
*/
	function clip_iter_value($iter, $findTextPos){
		$result = '';
		$fromClip = strrpos($iter, $findTextPos);
		$toClip = strlen($iter) - strrpos($iter, $findTextPos);
		$result = substr($iter, $fromClip, $toClip);
		return $result;
	}


/*
/--------------------------------------------------------------------------------------
/					method clipSpans 
/--------------------------------------------------------------------------------------
/	USED: for obtain field user_match_info
/	DESC:
/		this function used only for obtain user_match_info "Also known as" 
*/
	function clipSpans($arr){
		$result = [];
		for($i = 1; $i < sizeof($arr); $i++){
			$fromClip = strrpos($arr[$i], '<\/span>');
			$toClip = strlen($arr[$i]) - $fromClip;

			// last element will not be included in arr / becose when we him clip appear another characters becouse we reduce length of string
			if ($i == count($arr)-1) {
				break;
			}

			$clip = substr_replace($arr[$i], ' ', $fromClip, $toClip);
			$clip = str_replace("\\", '%', $clip);

			$result[] = $clip;
		}

		return $result;
	}


/*
/--------------------------------------------------------------------------------------
/					method clean
/--------------------------------------------------------------------------------------
/	USED: throughout the code
/	TAKE ARG:
/		1) $string - single string
/		2) $arrChar - arr with characters what need to remove from string
/	DESC:
/		UNWANTED CHARACTERS FROM STRING (SINGLE DELETIONS)
/		clean function elimen characters from one string 
*/
	function clean($string, $arrChar) {
		$string = str_replace($arrChar, '', $string);
		return $string;
	}


/*
/--------------------------------------------------------------------------------------
/					UNWANTED CHARACTERS FROM STRINGS (MULTIPLE DELETIONS)
/--------------------------------------------------------------------------------------
/	used throughout the code
/	accepts as a parameter two arguments:
/		1) $usortedArr -arr with unsorted strings
/		2) $elimenArgArr - arr with characters what need to remove from strings
/
/	method unwantedCharacters 
/	clean function elimen characters from one string 
*/
	// 
	function unwantedCharacters($usortedArr, $elimenArgArr){
		$result= [];
		foreach ($usortedArr as $key => $value) {
			$result[] = clean($value, $elimenArgArr);
		}

		return $result;
	}
	//--------------END UNWANTED CHARACTERS FROM STRING


	// decode unicods methodes
	function replace_unicode_escape_sequence($match) {
		return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
	}

	function unicode_decode($str) {
		return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $str);
	}
	// END decode unicods method


	function utf8ize($d) {
		if (is_array($d)) {
			foreach ($d as $k => $v) {
				$d[$k] = utf8ize($v);
			}
		} else if (is_string ($d)) {
			return unicode_decode($d);
		}
		return $d;
	}
/*------------------------------------------------------------------------------------------------------------------------END METHODS---------------------------------------------*/
	/*GLOBAL OBJECT*/
	//create global objec which will be contain sorted data from respons
	$ComRes = new CommunityResult(fieldCount($content, 'success', 20), $search_text, fieldCount($content, 'search_result_count', 42), fieldCount($content, 'search_page', 20));

/*------------------------------------------------------------------------------------------------------------------------USAGE METHODS---------------------------------------------*/
	//MUCH MANIPULATON FOR SORT DATA
	//get parts of big string which contain minimized info about user . we find div with class search_row
	$search_row = explode('\t\t\t<div class=\"search_row\">\r\n\t', $content);
	for($i = 1; $i < sizeof($search_row); $i++){
		/*STEAM URL*/ 
		$crudity_user_id = substr($search_row[$i], strrpos($search_row[$i], ' class=\"avatarMedium\"><a href=\"https:\/\/steam') + 34, 173);
		$fromClip = strrpos($crudity_user_id, '\"><img src=\"htt');
		$toClip = strlen($crudity_user_id) - strrpos($crudity_user_id, '<img src=\"https:') +3;
		$clip = substr_replace($crudity_user_id, ' ', $fromClip, $toClip);
		$userUrl =  str_replace('\/' , '/', $clip);
		/*END STEAM URL*/ 

		/*USER AVATAR*/
		$crud_link_avatar = substr($search_row[$i], strrpos($search_row[$i], 'https:\/\/steamcdn-a.akamaihd.net\/steamcommunity\/public\/images'), 131);
		$userLinkAvatar =  str_replace('\/' , '/', $crud_link_avatar);
		/*END USER AVATAR*/

		/*USER MUCH INFO*/
		$crud_user_match_info = clip_iter_value($search_row[$i] , 'Also known as:');
		$explode_spans = explode('<span style=\"color: whitesmoke\">', $crud_user_match_info);

		//clip spans
		$user_match_info = clipSpans($explode_spans);
		//end clip spans method
		/*END USER MUCH INFO*/

		/*USER PERSONAL INFO*/
		$crud_user_personal_info = clip_iter_value($search_row[$i] , '<div class=\"searchPersonaInfo\">');
		$explode_personal_info = explode('\"searchPersonaName\"', $crud_user_personal_info);//part is to element in arrs 

		//-----elimen unwanted characters
		//call function unwantedCharacters($usortedArr, $elimenArgArr) return arr
		$arguments = ['\r', '\n', '\t', '\t', '\/id\/', '<\/span>', '<\/div>', 'href=\"https:\/\/steamcommunity.com\/', 'class=', '<span style=\"color: whitesmoke\">', '<div style=\"clear:left\">', '<div class=\"searchPersonaInfo\">', '<br \/>&nbsp', 'style=\"margin-bottom:-2px\"', '&nbsp;', '<span class=\"community_searchresults_title\">', 'href=\"https:\/\/steamcommunity.com', ' border=\"0\" \/><div class=\"search_match_info\"><div>', '<div class=\"search_match_info\">', '<div \"searchPersonaInfo\">'];
		//first arr is strings from from which need to elimen the following characters
		$elimened = unwantedCharacters($explode_personal_info, $arguments) ;

		$personaName = [];
		$personaRegion = [];
		$countryFlag = [];
		foreach ($elimened as $key => $value) {
			// replace "\/" to "/"
			$replaced = str_replace('\/' , '/', $value);

			/*-------logics needed to obtain personal Name------*/
			//remove part of steam url which has ben startet from word profiles
			$persona_name_crud = substr($replaced, strpos($replaced, 'profiles/'), 100);
			// find part what need to remove from string
			$need_to_remove = clip_iter_value($persona_name_crud, '</a>'); //from tag a to end of string
			$replace = str_replace($need_to_remove, " ", $persona_name_crud); // replace from tag a to end of string
			$need_to_add = clip_iter_value($replace, '\">');//from start to this characrer clip ' \"> '
			$need_to_add = str_replace('\">', '', $need_to_add);//inclusive and ' \"> ' need to remove
			$need_to_add = str_replace('\\', '%', $need_to_add);//inclusive and ' \"> ' need to remove
			// $need_to_add = unicode_decode($need_to_add);// decode unicode
			//final sort persona name
			$personaName[] = $need_to_add;
			//---END logics needed to obtain personal Name


			/*---------logics needed to obtain region------------*/
			$region_crud = substr($replaced, strpos($replaced, 'profiles/'), 150);
			if (strpos($region_crud, '<img  s')) {
				// cut unwanted part of string from "<img  s " to end of string
				$rc_need_to_rm = clip_iter_value($region_crud, "<img  s");
				// remove this part of string
				$rc_replace = str_replace($rc_need_to_rm, '', $region_crud);
				// cut needed part of string from declared characters to end of string 
				$rc_need_to_add = clip_iter_value($rc_replace , '</a><br />');
				// explode characters from string
				$rc_need_to_add = clean($rc_need_to_add, ['</a><br />']);
				// cut part of string from <br /> to end 
				$rc_need_to_add = clip_iter_value($rc_need_to_add, "<br />");
				// remove inclusiv and <br />
				$rc_need_to_add = clean($rc_need_to_add, '<br />');

				if ($rc_need_to_add !== '') {
					$personaRegion[] = $rc_need_to_add;
				}else {
					$personaRegion[] = '';
				}
			}



			/*---------END logics needed to obtain region------------*/

			/*---------logics needed to country img ------------------*/
			$country_img_crud = substr($replaced, strpos($replaced, 'https://steamcommunity-a.akamaihd.net/public/images/countryflags'), 71);
			// find part what need to remove from string
			if (strpos($country_img_crud, 'countryflags')) {
				$countryFlag[] = $country_img_crud;
			} else {
				$countryFlag[] = '';
			}
			/*---------END logics needed to country img ------------------*/

		}
		//end elimen unwanted characters

		$user_personal_info = [
			'personaName' => $personaName[1],
			'countryFlag' => $countryFlag[1],
			'personaRegion' => (!empty($personaRegion) ? $personaRegion[0] : '')
		];
		/*END USER PERSONAL INFO*/

		$user = new UserData($userUrl, $userLinkAvatar, $user_personal_info, $user_match_info);
		$ComRes->setUserData($user);
	}/*END MAIN FOR*/
/*----------------------------------------------------------------------------------------------------------------END USAGE METHODS---------------------------------------------*/

/*----------------------------------------------------------------------------------------------------------------RETURN JSON AT PAGE---------------------------------------------*/
	echo json_encode($ComRes);
	// echo json_last_error_msg();

	// print '<pre>';
	// print_r($ComRes);
	// print_r($content);
	// print '</pre>';


// }