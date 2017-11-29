<?php 

require('vendor/autoload.php');
use Buzz\Browser;
use Buzz\Client\Curl;
use Buzz\Listener\CookieListener;
$browser = new Browser();
$client = new Curl();
$client->setMaxRedirects(0);
$browser->setClient($client);
// Create CookieListener
$listener = new CookieListener();
$browser->addListener($listener);
// This URL set two Cookies, k1=v1 and k2=v2
$response = $browser->get('https://steamcommunity.com');
// This URL will return the two set Cookies
// $response = $browser->get('http://httpbin.org/cookies');
$sesId = substr($response, strrpos($response, 'g_sessionID = ')+15, 24);
// echo $response;
$response = $browser->get('https://steamcommunity.com/search/SearchCommunityAjax?text=ostap+ben&filter=users&sessionid=' . $sesId . '&steamid_user=false');
echo $response;
// Should output
/*
{
  "cookies": {
    "k1": "v1", 
    "k2": "v2"
  }
}
*/
// The Cookies are able to be retrieved and set using getCookies and setCookies on the Listener.
// print_r($listener->getCookies());