<?php
/*
   - this is functional example for sending SMS from PHP code on website thru SMS gate sender gosms.eu
   - before running this code, you must create account on gosms.eu and fill your $client_id and $secret in function SMSgetAcessToken
   - you can switch between testing and production mode by change of $url in function SMSsendMessage
      ** when you are using testing mode, SMS is not sended, it's doesn't consume your credit, it's just recorded in logs of gosms.eu 
*/


// Main code for calling function SMSsendMessage.
$phone = '+420111222333'; // with telephone preselection, already valid phone number
$content = 'Your validaton code for online form on website is 66145.'; // any string you want to send in SMS
$result = SMSsendMessage($phone, $content);
if ($result)
   echo ("SMS sucesfully sended");
else
   echo ("Failiture on sending SMS, contact administrator.");

   
   
function SMSsendMessage($phone, $content) {
	// https://doc.gosms.eu/?lang=cs#tag/Messages
	// https://app.gosms.eu/selfservice/api/
	$access_token = SMSgetAcessToken();
	
	$url = 'https://app.gosms.eu/api/v1/messages'; // url for production mode
	//$url = 'https://app.gosms.eu/api/v1/messages/test';	// url for testing purposes
	$url .= '?access_token='.$access_token;
	$post_data = array( 'message' => $content, 'recipients' => $phone, 'channel' => '403980' );
	$post_data = json_encode($post_data);
	
	$response = ReturnResponseFromPOSTrequestOnWebsite($url, $post_data);
   // print_r($response); // here you can see other response data such as const of this SMS and credit balance
	
	if (str_contains($url, 'test')) {
		if ($response[1] == 200) // on success with testing purposes
			return true;
		else
			return false;
	}
	else {
		if ($response[1] == 201) // on success with production scenario
			return true;
		else
			return false;
	}
}

function SMSgetAcessToken() {
	$client_id = '28820_2lzhoct4ljkkck44w8w480sgw800c84ckwkksg0saggw4gk4cs'; // just example, same format, this is not really valid credentials,
	$secret = '49dqebv4hr0ggog8kkskgokclgw80cog4k444gcc8gswww84g0'; // example
	$url = "https://app.gosms.eu/oauth/v2/token";
	$post_data = array( 'client_id' => $client_id, 'client_secret' => $secret, 'grant_type' => 'client_credentials');
	
	$response = ReturnResponseFromPOSTrequestOnWebsite($url, $post_data);
	$response = $response[0];
	
	$obj = json_decode($response);
	if (!is_object($obj))
		return false;
	
	$access_token = $obj->access_token;
	if ($access_token == '')
		return false;
	
	return $access_token;	
}


function ReturnResponseFromPOSTrequestOnWebsite($url, $post_data) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
	$response = curl_exec($ch);
	/* // debuging feature
	if (curl_errno($ch))
		echo 'Chyba curl: ' . curl_error($ch);
	*/
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	
	curl_close($ch);
	
	return array($response, $httpCode);
}





