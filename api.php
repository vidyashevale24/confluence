<?php
class Api{

	/******************************* Create space *****************************
	curl -u admin:Abc@1234 -X POST -H 'Content-Type: application/json' -d' { "key":"RAID", "name":"Raider",
	"type":"global",  "description":{"plain": { "value": "Raider Space for raiders","representation":
	"plain" }}}' http://localhost:3000/rest/api/space
	**************************************************************************/

	public function create_space(){
		$qbody = file_get_contents("php://input");
	    $ch = curl_init();

	    curl_setopt($ch, CURLOPT_URL, "http://localhost:3000/rest/api/space/");
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $qbody);
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_USERPWD, "admin" . ":" . "Abc@1234");

	    $headers = array();
	    $headers[] = "Content-Type: application/json";
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	    $result = curl_exec($ch);
	    if (curl_errno($ch)) {
	        echo 'Error:' . curl_error($ch);
	    }
	    curl_close ($ch);
	    print_r($result);
	}
	
	/************************* Create a new page/content *************************

	curl -u admin:Abc@1234 -X POST -H 'Content-Type: application/json' -d '{"type":"page","title":"new page parent","space":{"key":"RAID"},"body":{"storage":{"value":"<p>This is <br/> a new page</p>","representation":"storage"}}}' http://localhost:3000/rest/api/content
	******************************************************************************/
	
	public function create_content(){
		$qbody = file_get_contents("php://input");
		
	    $ch = curl_init();

	    curl_setopt($ch, CURLOPT_URL, "http://localhost:3000/rest/api/content/");
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $qbody);
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_USERPWD, "admin" . ":" . "Abc@1234");

	    $headers = array();
	    $headers[] = "Content-Type: application/json";
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	    $result = curl_exec($ch);
	    if (curl_errno($ch)) {
	        echo 'Error:' . curl_error($ch);
	    }
	    curl_close ($ch);
	    print_r($result);
	}
	
	/********************** Create a page with a task (CURL is not working)*********
	curl -u admin:Abc@1234 -X POST -H 'Content-Type: application/json' -d '{"type":"page","title":"Another planning specs with username","body":{"storage":{"value":"<ac:task-list><ac:task><ac:task-status>incomplete</ac:task-status><ac:task-body><ac:link><ri:user ri:username='admin' /></ac:link>do something</ac:task-body></ac:task></ac:task-list>","representation":"storage"}},"space":{"key":"RAID"}}' http://localhost:3000/rest/api/content/
	*********************************************************************************/

	public function create_page_with_task(){
		$qbody = file_get_contents("php://input");
	    $ch = curl_init();

	    curl_setopt($ch, CURLOPT_URL, "http://localhost:3000/rest/api/content/");
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $qbody);
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_USERPWD, "admin" . ":" . "Abc@1234");

	    $headers = array();
	    $headers[] = "Content-Type: application/json";
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	    $result = curl_exec($ch);
	    if (curl_errno($ch)) {
	        echo 'Error:' . curl_error($ch);
	    }
	    curl_close ($ch);
	    print_r($result);
	}

	/**************** Create a new page as a child of another page**************
		curl -u admin:Abc@1234 -X POST -H 'Content-Type: application/json' -d '{"type":"page","title":"new page3","ancestors":[{"id":2654260}],"space":{"key":"RAID"},"body":{"storage":{"value":"<p>This is a new page</p>","representation":"storage"}}}' http://localhost:3000/rest/api/content/
	****************************************************************************/

	public function create_child(){
		$qbody = file_get_contents("php://input");
	    $ch = curl_init();

	    curl_setopt($ch, CURLOPT_URL, "http://localhost:3000/rest/api/content/");
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $qbody);
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_USERPWD, "admin" . ":" . "Abc@1234");

	    $headers = array();
	    $headers[] = "Content-Type: application/json";
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	    $result = curl_exec($ch);
	    if (curl_errno($ch)) {
	        echo 'Error:' . curl_error($ch);
	    }
	   
	    curl_close ($ch);
	    print_r($result);
	}
	
	/*********************  working CURL for upload attachment *******************
	curl -v -S -u admin:Abc@1234 -X POST -H "X-Atlassian-Token: no-check" -F "file=@/home/neosoft/Pictures/Screenshot from 2018-10-04 16-23-33.png" -F "comment=this is my file" http://localhost:3000/rest/api/content/3244039/child/attachment
	*****************************************************************************/


	public function upload_attachment(){

		$file_name_with_full_path = '/home/neosoft/Desktop/VS doc/download.jpeg';

		$request_url = 'http://localhost:3000/rest/api/content/7406823/child/attachment';
		
		if (function_exists('curl_file_create')) { 
		  $cFile = curl_file_create($file_name_with_full_path);
		} else { 
		  $cFile = '@' . realpath($file_name_with_full_path);
		}

		$post = array('id'=>'7406823','comment' => 'abc','file' =>$cFile);

	   	$ch = curl_init();
	 	curl_setopt($ch, CURLOPT_URL, $request_url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $post); 
	    curl_setopt($ch, CURLOPT_USERPWD, "admin" . ":" . "Abc@1234");

	   	$headers = array();
		$headers[] = "X-Atlassian-Token: no-check";
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	    $result = curl_exec($ch);
	    $info = curl_getinfo($ch);
	    curl_close ($ch);
  }

	/********************** Downlaod an attachment *****************************
	curl -u admin:Abc@1234 http://localhost:3000/rest/api/content/3244039/child/attachment

	****************************************************************************/

	public function download_attachment(){
		$json 		= 	json_decode(file_get_contents("php://input"));
	    $CURLOPT_URL= 	"http://localhost:3000/rest/api/content/".$json->id."/child/attachment";
	    $ch = curl_init();

	    curl_setopt($ch, CURLOPT_URL, $CURLOPT_URL);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
	    curl_setopt($ch, CURLOPT_USERPWD, "admin" . ":" . "Abc@1234");

	    $headers = array();
	    $headers[] = "Content-Type: application/json";
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	    $result = curl_exec($ch);
	    if (curl_errno($ch)) {
	        echo 'Error:' . curl_error($ch);
	    }
	    curl_close ($ch);
	    print_r($result);
	}

	/***************Update a page/content ***********************************
	curl -u admin:Abc@1234 -X PUT -H 'Content-Type: application/json' -d '{"id":"2654256","type":"page","title":"new 4 page","space":{"key":"RAID"},"body":{"storage":{"value":"<p>This is the updated content for the new page</p>","representation":"storage"}},"version":{"number":4}}' http://localhost:3000/rest/api/content/2654256
	*****************************************************************************/

	public function update_page(){
		$qbody 		= 	file_get_contents("php://input");
		$json 		= 	json_decode($qbody);
	    $CURLOPT_URL= 	"http://localhost:3000/rest/api/content/".$json->id;
	   // $CURLOPT_URL= 	"http://localhost:3000/rest/api/content/7406786";
	   	$ch 		= curl_init();
		curl_setopt($ch, CURLOPT_URL, $CURLOPT_URL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,  $qbody);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch, CURLOPT_USERPWD, "admin" . ":" . "Abc@1234");
		$headers = array();
		$headers[] = "Content-Type: application/json";
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$result = curl_exec($ch);
		if (curl_errno($ch)) {
		    echo 'Error:' . curl_error($ch);
		}
		curl_close ($ch);
	    print_r($result);
	}

	/********************************** Delete a Page **************************
	curl -v -S -u admin:Abc@1234 -X DELETE http://localhost:3000/rest/api/content/3604482
	****************************************************************************/

	public function delete_page(){
		$qbody 		= 	file_get_contents("php://input");
		$json 		= 	json_decode($qbody);
		$ch 		= 	curl_init();
		$CURLOPT_URL= 	"http://localhost:3000/rest/api/content/".$json->id;
		curl_setopt($ch, CURLOPT_URL, $CURLOPT_URL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");

		curl_setopt($ch, CURLOPT_USERPWD, "admin" . ":" . "Abc@1234");

		$result = curl_exec($ch);
		if (curl_errno($ch)) {
		    echo 'Error:' . curl_error($ch);
		}
		curl_close ($ch);
		print_r($result);
	}
}
