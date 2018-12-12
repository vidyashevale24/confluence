<?php
$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, "http://localhost:3000/rest/api/content?title=Logistics+Standard+Work&spaceKey=DEMO&expand=history");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_USERPWD, "admin" . ":" . "Abc@1234");

	$result = curl_exec($ch);
	if (curl_errno($ch)) {
	    echo 'Error:' . curl_error($ch);
	}
	curl_close ($ch);
	$result = json_decode($result);
	print_r($result);
	print_r($result->results[0]->id);
	if(isset($result->id))
	
die;
