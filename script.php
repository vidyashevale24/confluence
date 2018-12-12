<?php
//Increase maximum execution time

ini_set('max_execution_time', 2000);
$root = "{$_SERVER['DOCUMENT_ROOT']}/Warburn";
//$root = "{$_SERVER['DOCUMENT_ROOT']}/CMS";
$pathLen = strlen($root);
myScanDir($root, 0, strlen($root),  $parentID = '', $workspaceKey = '', $version = '');

/******** Function to read directory and files in recursive order **********/
function myScanDir($dir, $level, $rootLen, $parentID = '', $workspaceKey = '', $version = '' )
{ 
	global $pathLen; 
	global $formID; 
	$version = 1; 
	global $IDs;
	global $workspaceKey; 
	if($level == 0){

		/*$workspaceKey = 'WE';
		$parentID = 'WE';*/
		
		$workspaceKey = 'VID';
		$parentID = 'VID';
		
		$parentID =  create_page($dir, $parentID , $level);
	}
	if($level >= 1){
		$parentID= create_page_child($dir, $parentID, $workspaceKey , $content = '' ,$level , $ext = '');
		if(isset($parentID['form_id'])){
			$IDs['form_id'] 	= 	$parentID['form_id'];
		}
		if(isset($parentID['ppm_id'])){
			$IDs['ppm_id'] 		= 	$parentID['ppm_id'];
		}
		if(isset($parentID['std_id'])){
			$IDs['std_id'] 		= 	$parentID['std_id'];
		}
		if(isset($parentID['pow_id'])){
			$IDs['pow_id'] 		= 	$parentID['pow_id'];
		}
		if(isset($parentID['std_tmp_id'])){
			$IDs['std_tmp_id'] 	= 	$parentID['std_tmp_id'];
		}
		$parentID 	= 	$parentID['result_id'];
		 
	}
	if ($handle = opendir($dir)) {
		$allFiles = array();
	    while (false !== ($entry = readdir($handle))) {
	      	if ($entry != "." && $entry != "..") {
	        	if (is_dir($dir . "/" . $entry))
		        {
		            preg_match("/[^\/]+$/", $dir, $matches);
		            $match_key = $matches[0]; 
		            //Remove XLIST folder and files
		            if($entry != "XLIST" && $match_key != "XLIST")
		            $allFiles[] = "D: " . $dir . "/" . $entry;
		        }
		        else
		        {
		            //Convert WMF format to the JPG format
		            $ext = pathinfo($entry, PATHINFO_EXTENSION);
		            if($ext == 'WMF' || $ext == 'wmf'){
		                $file = convertWmfToJpg($ext,$dir."/".$entry);  
		                $allFiles[] = "F: " . $dir . "/" . $file;
		            }
		            if(
		                ($ext == 'ppt') ||  ($ext == 'PPT') || 
		                ($ext == 'doc') ||  ($ext == 'DOC') || 
		                ($ext == 'xls') ||  ($ext == 'XLS') || 
		                ($ext == 'jpg') ||  ($ext == 'JPG') ||
		                ($ext == 'pdf') ||  ($ext == 'PDF') ||
		                ($ext == 'txt') ||  ($ext == 'TXT')
		            ) 
		            $allFiles[] = "F: " . $dir . "/" . $entry;
		            //Add only MAIN html file
		            elseif ((strpos($entry,'MAIN') == true) && (($ext == 'HTML') || ($ext == 'html')))
		           $allFiles[] = "F: " . $dir . "/" . $entry;
		        }
	      	}
	    }
	    closedir($handle);
	    foreach($allFiles as $value)
	    {
	     	$displayName = substr($value, $rootLen + 4);
	        $fileName    = substr($value, 3);
	       	$linkName    = str_replace(" ", "%20", substr($value, $pathLen + 3));
	        if (is_dir($fileName)) { 
	      		preg_match("/[^\/]+$/", $fileName, $matches);
				$match_key = $matches[0]; 
				myScanDir($fileName, $level + 1, strlen($fileName), $parentID, $workspaceKey, $version ); 
			}else {
	          	
					$pieces = explode("/", $fileName );
					$count= count($pieces);
					$match_key = $pieces[$count-1];
				
					$ext = pathinfo($match_key, PATHINFO_EXTENSION);
						
					if(($ext == 'html' || $ext == 'htm' || $ext == 'HTML' || $ext == 'HTM' )){
						$content = parse_HTML($fileName, $parentID, $version , $IDs ,$workspaceKey );
						create_page_child($fileName , $parentID, $workspaceKey ,$content ,$level ,$ext);
		        	}else{
	        		if(
	        			($ext == 'ppt') ||  ($ext == 'PPT') || 
	        			($ext == 'pdf') ||  ($ext == 'PDF') || 
	        			($ext == 'doc') ||  ($ext == 'DOC') || 
	        			($ext == 'xls') ||  ($ext == 'XLS') || 
	        			($ext == 'jpg') || 	($ext == 'JPG')
	        		){
	        			if($ext != 'JPG'){
	        				$version++;
	        			}
      					
	      				//call to update and upload attachment API
	      				upload_attachment($parentID , $fileName , $workspaceKey, $version , $level ,$ext ); 
	      			}
	      		}
			} 
 	    }
	}
}

/********************* Function to create page  ****************************/
function create_page($fileName, $parentID ,$level)
{
	preg_match("/[^\/]+$/", $fileName, $matches);
	$match_key = $matches[0]; 
	$page = array(
        "type"=>"page",
        "title"=>$match_key,
        "space"=>array("key"=>$parentID),
        "body"=>array(
                "storage"	=> array(
                "value"		=> 	"CMS Home",
                "representation"=>"storage"
             )
        )
    );
    $qbody = json_encode($page);	
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "http://localhost:3000/rest/api/content/");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $qbody);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_USERPWD, "admin" . ":" . "Abc@1234");

    $headers = array();
    $headers[] = "Content-Type: application/json";
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close ($ch);
    $result = json_decode($result);
    if(isset($result->id))
    return $result->id;
}

/****************** Function to create child page **************************/
function create_page_child($fileName, $parentID, $workspaceKey ,$content,$level ,$ext)
{
	$pieces 		= 	explode("/", $fileName );
	$count 			= 	count($pieces);
	$match_key 		= 	$pieces[$count-1];
	$parent_key 	= 	$pieces[$count-2];
	$top_parent_key = 	$pieces[$count-3];

	if($level == 1){
		if(($ext == 'html' || $ext == 'htm' || $ext == 'HTML' || $ext == 'HTM' )){
			$title 	     =   $match_key;
			$title 	 = 	 str_replace('-MAIN','', $title);
		}else{
			$title 	     =   $match_key;
		}
	}
	if($level == 2 ){
		if(($ext == 'html' || $ext == 'htm' || $ext == 'HTML' || $ext == 'HTM' )){
			$title 	     =   $match_key;
			$title 	 = 	 str_replace('-MAIN','', $title);
		}else{
			$title =$parent_key." ".$match_key;
		}
	}
	if($level >= 3 ){
		if(($ext == 'html' || $ext == 'htm' || $ext == 'HTML' || $ext == 'HTM' )){
			$title 	     =   $match_key;
			$title 	 	 = 	 str_replace('-MAIN','', $title);
		}else{
			$title       =   $top_parent_key." ".$parent_key." ".$match_key;
		}
	}
	$requestChild = array (
        "type"=>"page",
        "title"=>$title,
        "ancestors"=>
            [
                array("id"=>$parentID)
            ],
            "space"=>
            array(
                "key"=>$workspaceKey
            ),
            "body"=>
            array(
                "storage"=>
                array(
					    "value"=>"$content",
                    	"representation"=>"storage"
                )
            )           
    );
    $qbodyChild = json_encode($requestChild);

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, "http://localhost:3000/rest/api/content/");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $qbodyChild);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_USERPWD, "admin" . ":" . "Abc@1234");

	$headers = array();
	$headers[] = "Content-Type: application/json";
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	$result = curl_exec($ch);
	if (curl_errno($ch)) {
		echo 'Error:' . curl_error($ch);die;
	}
	curl_close ($ch);
	$result = json_decode($result);
	echo "title:       ".$title."<br>";
	if(isset($result->id))
	{
		if ( preg_match("~\bForms\b~",$match_key) ){
				$data['form_id']=$result->id;
				echo "form".$result->id."<br>";
		}
		if ((preg_match("~\bPPM's\b~",$match_key)) || 
			(preg_match("~\bPPMs\b~",$match_key))){
				$data['ppm_id']=$result->id;
			echo "ppm".$result->id."<br>";
		}
		if ((preg_match("~\bStandard Work\b~",$match_key) ) || (preg_match("~\bStandard work\b~",$match_key))){
				$data['std_id']=$result->id;
				echo "std".$result->id."<br>";
		}
		if ( (preg_match("~\bPowerpoint\b~",$match_key) )|| (preg_match("~\bPower Point\b~",$match_key))){
				$data['pow_id']=$result->id;
				echo "pow".$result->id."<br>";
		}
		if ( preg_match("~\bStandard Work Templates\b~",$match_key) ){
				$data['std_tmp_id']=$result->id;
				echo "temp".$result->id."<br>";
		}

		$data['result_id']=$result->id;
		return $data;
	}
}

/*********************** Function to upload a file  ************************/
function upload_attachment($contentID , $fileName , $workspaceKey, $version , $level ,$ext){

	$request_url = 'http://localhost:3000/rest/api/content/'.$contentID.'/child/attachment';
	
	if (function_exists('curl_file_create')) { 
	  $cFile = curl_file_create($fileName);
	} else { 
	  $cFile = '@' . realpath($fileName);
	}

	$post = array('id'=>$contentID,'comment' => 'File attachment','file' =>$cFile);

   	$ch = curl_init();
 	curl_setopt($ch, CURLOPT_URL, $request_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_USERPWD, "admin" . ":" . "Abc@1234");

   	$headers = array();
	$headers[] = "X-Atlassian-Token: no-check";
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    $info   = curl_getinfo($ch);

    if (curl_errno($ch)) {
    	echo 'Error:' . curl_error($ch);die;
	}
    curl_close ($ch);

    //Call to update page API to update content on page
    if($ext != 'JPG')
    update_page($contentID , $fileName , $workspaceKey , $version , $level ,$ext);
 }

/****************** Function to update specific page ***********************/
function update_page($parentID , $fileName , $workspaceKey , $version , $level ,$ext)
{
	//Call to get Attachment API to get all existing attachments of given parentID
	$pieces 		= 	explode("/", $fileName );
	$count 			= 	count($pieces);
	$content 		= 	$pieces[$count-1];
	$match_key 		= 	$pieces[$count-2];
	$parent_key 	= 	$pieces[$count-3];
	$top_parent_key	= 	$pieces[$count-4];

	//Code to replace -MAIN with space
	if($level == 1){
		if(($ext == 'html' || $ext == 'htm' || $ext == 'HTML' || $ext == 'HTM' )){
			$title 	     =   $match_key;
			$title 	     = 	 str_replace('-MAIN','', $title);
		}else{
			$title 	     =   $match_key;
		}

	}
	if($level == 2 ){
		if(($ext == 'html' || $ext == 'htm' || $ext == 'HTML' || $ext == 'HTM' )){
			$title 	     =   $match_key;
			$title 	 	 = 	 str_replace('-MAIN','', $title);
		}else{
			$title       =  $parent_key." ".$match_key;
		}
	}
	if($level >= 3 ){
		if(($ext == 'html' || $ext == 'htm' || $ext == 'HTML' || $ext == 'HTM' )){
			$title 	     =   $match_key;
			$title 	 	 = 	 str_replace('-MAIN','', $title);
		}else{
			$title       =   $top_parent_key." ".$parent_key." ".$match_key;
		}
	}
	$attachArray  	= 	array();
	$contentName 	=  	'';
	$attachments 	= 	get_attachment($parentID);
	
	if(!empty($attachments)){
		foreach ($attachments->results as $key => $value) {
			$ext = pathinfo($value->title, PATHINFO_EXTENSION);
			if($ext != 'JPG'){
				$titleDoc = preg_replace("@\s+@",' ',htmlspecialchars(addslashes($value->title))); 
				$contentName.= "<a class=\"filename\" href=\"/download/attachments/$parentID/$titleDoc?api=v$version\" title=\"$titleDoc\">$titleDoc</a><BR />";
			}
		}
	}
	
	$requestStorage = array(
	  	"id" 		=> 	$parentID,
	 	"type" 		=> 	"page",
	  	"title" 	=>	$title,
	  	"space" 	=> 	array(
	    	"key" 	=>  $workspaceKey
	  	),
	  	"body" =>  array(
				"storage"  	=> 	array(
				"value" 	=> 	"".$contentName."",
				"representation" => "storage"
			),
	  	),
	  	"version"  	=> array(
	    	"number"=> $version
	  	)
	);

    $qbodyChild = json_encode($requestStorage);
	$CURLOPT_URL= 	"http://localhost:3000/rest/api/content/".$parentID ;
   	$ch 		= curl_init();
	curl_setopt($ch, CURLOPT_URL, $CURLOPT_URL);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,  $qbodyChild);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_USERPWD, "admin" . ":" . "Abc@1234");
	$headers = array();
	$headers[] = "Content-Type: application/json";
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	$result = curl_exec($ch);
	if (curl_errno($ch)) {
	    echo 'Error:' . curl_error($ch); die;
	} 
	curl_close ($ch);
}

/************* Function to get Attachments of given content ID *************/
function get_attachment($contentID){

	$CURLOPT_URL= 	"http://localhost:3000/rest/api/content/$contentID/child/attachment";
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $CURLOPT_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_USERPWD, "admin" . ":" . "Abc@1234");

    $headers = array();
    $headers[] = "Content-Type: application/json";
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close ($ch);
   	$result = json_decode($result);
	return $result;
}

/********************** Function to parse HTML file ************************/

function parse_HTML($fileName, $parentID, $version , $IDs ,$workspaceKey){
	//Read File content 
	$content = 	file_get_contents($fileName);

	//Remove script , style, map from the html content
	$content = 	preg_replace('#<style(.*?)>(.*?)</style>#is', '', $content);
	$content = 	preg_replace('#<script(.*?)>(.*?)</script>#is', '', $content);
	$content = 	preg_replace('#<map(.*?)>(.*?)</map>#is', '', $content);
	$html = 	str_replace('.GIF">','.GIF"/>', $content);

	//find all BODY with JS and CSS tags and replace with only BODY tag
	if (strpos($html, '<BODY ONUNLOAD="javascript:dounload();" LINK="#0000ff" VLINK="#800080">') !== false){
	    $html = str_replace('<BODY ONUNLOAD="javascript:dounload();" LINK="#0000ff" VLINK="#800080">',"<BODY>", $html);
	}

	if (strpos($html,'<BODY ONUNLOAD="javascript:dounload();">') !== false) {
	    $html = str_replace('<BODY ONUNLOAD="javascript:dounload();">',"<BODY>", $html);
	}

	//Get body content from Html file
	$content  = get_string_between($html, '<BODY>', '</BODY>');

	preg_match_all( '@SRC="([^"]+)"@' , $content , $match );
	$src = array_pop($match);
	$imgName = array();
	foreach ($src as $key => $value) {
		$ext = pathinfo($value, PATHINFO_EXTENSION);
		if($ext == 'WMF')
			$imgName[] = str_replace('WMF','JPG', $value);
		else	
			unset($src[$key]);
	}
	$src = array_values($src);

	$newImageArr   	= 	array_combine($src, $imgName);

	$patterns 		= 	array();
	$replacements 	=	array();
	foreach ($newImageArr as $key => $value ) {
		$patterns[] = '<IMG USEMAP="#map1" SRC="'.$key.'">';
		$replacements[] = 'img src ="/download/attachments/'.$parentID.'/'.$value.'?api=v'.$version.'"/';
	}
	$content      =  	preg_replace($patterns, $replacements, $content);
	
	//Convert all documents into downloadable links
	$href =array();
	preg_match_all( '@HREF="([^"]+)"@' , $content , $matchHref );
	$href = array_pop($matchHref);
	$href = array_unique($href);
		foreach ($href as $key => $value) {
		$value = chop($value,'"');
		$ext = pathinfo($value, PATHINFO_EXTENSION);
		
		if($ext == 'DOC' || $ext == 'PPT' || $ext == 'XLS' || $ext == 'doc' || $ext == 'ppt' || $ext == 'xls' || $ext == 'pdf' || $ext == 'PDF'){
		
		$pieces = explode("\\", $value );
		foreach ($pieces as $k => $v) {
			if($v == '..' || $v == "Figures with 'Run File' .." || strpos($v,'Figures with') !== false)
			unset($pieces[$k]);
		}
		$pieces  	= 	array_values($pieces);

		$count 		= 	count($pieces);
	 	$match_key 	= 	$pieces[$count-1];
	 	$match_key 	= 	ltrim($match_key, '.');
	 	$match_key 	= 	str_replace('Production', '', $match_key);
	 	$match_key 	= 	str_replace('Forms', '', $match_key);
	 		//Get corresponding parent ID
			if($count == 2){
				$onlyParent = 	$pieces[$count-2];
				if ( preg_match("~\bForms\b~",$onlyParent) ){
					$content = str_replace($value, '/download/attachments/'.$IDs['form_id'].'/'.$match_key.'?api=v'.$version, $content);
				}
				if ((preg_match("~\bPPM's\b~",$onlyParent)) || 
					(preg_match("~\bPPMs\b~",$onlyParent))){
					$content = str_replace($value, '/download/attachments/'.$IDs['ppm_id'].'/'.$match_key.'?api=v'.$version, $content);
				}
				if ( (preg_match("~\bStandard work\b~",$onlyParent) ) || (preg_match("~\bStandard Work\b~",$onlyParent))){
					$content = str_replace($value, '/download/attachments/'.$IDs['std_id'].'/'.$match_key.'?api=v'.$version, $content);
				}
				if ( (preg_match("~\bPowerpoint\b~",$onlyParent) ) || (preg_match("~\bPower Point\b~",$onlyParent))){
					$content = str_replace($value, '/download/attachments/'.$IDs['pow_id'].'/'.$match_key.'?api=v'.$version, $content);
				}
				if ( preg_match("~\bStandard Work Templates\b~",$onlyParent) ){
					$content = str_replace($value, '/download/attachments/'.$IDs['std_tmp_id'].'/'.$match_key.'?api=v'.$version, $content);
				}
				
				}elseif($count > 2){
				//Link Parent ID's from another folder by calling getPageID API
				//Create title of corresponsing link

				$parent1 =  $pieces[$count-2];
				$parent2 =  $pieces[$count-3];
				$parent1 = explode(" ", $parent1 );
				if(count($parent1) > 1){
					$parentNew1 = implode('+', $parent1);
				}else{
					$parentNew1 = $parent1[0];
				}

				$parent2 = explode(" ", $parent2 );
				if(count($parent2) > 1){
					$parentNew2 = implode('+', $parent2);
				}else{
					$parentNew2 =$parent2[0];
				}

				$title =	$parentNew2."+".$parentNew1;
				echo "title in href:			".$title."<br>";
					
				$anothrParentId 	= 	getPageID($title ,$workspaceKey);
					
				$content = str_replace($value, '/download/attachments/'.$anothrParentId.'/'.$match_key.'?api=v'.$version, $content);
			}
		}
	}

	//Get body content from Html file
  	$pattern="/<p>(.+?)<\/p>/i";
    preg_match_all($pattern,$content,$matches,PREG_PATTERN_ORDER);

    if(!empty($matches)){
		foreach ($matches[1] as $value) {
		$value  =  chop($value,'"');
		$ext = pathinfo($value, PATHINFO_EXTENSION);	
		//$ext = trim($ext, '"');
		if($ext == 'DOC' || $ext == 'PPT' || $ext == 'XLS' || $ext == 'doc' || $ext == 'ppt' || $ext == 'xls' || $ext == 'PDF' || $ext == 'pdf'){	

			$pieces = explode("/", $value );
			foreach ($pieces as $k => $v) {
				if($v == '..' || $v == "Figures with 'Run File' .." || strpos($v,'Figures with') !== false)
				unset($pieces[$k]);
			}
			$pieces  	  = 	array_values($pieces);
			$count 		  =  	count($pieces);
			$match_key    = 	$pieces[$count-1];
		 	$match_key    =     str_replace('NASA', '',$match_key);
		 	$match_key    =     str_replace('WIOP', '',$match_key);
		
			if($count == 2){
					$onlyParent = 	$pieces[$count-2];
					if ( preg_match("~\bForms\b~",$onlyParent) ){
						$content =  str_replace($value,'Figures with "Run File" <A class="filename" HREF="/download/attachments/'.$IDs['form_id'].'/'.$match_key.'?api=v'.$version.'"'.' title="'.$match_key.'">'.$match_key.'</A>', $content);
					}
					if ((preg_match("~\bPPM's\b~",$onlyParent)) || 
						(preg_match("~\bPPMs\b~", $onlyParent))){
						$content =  str_replace($value,'Figures with "Run File" <A class="filename" HREF="/download/attachments/'.$IDs['ppm_id'].'/'.$match_key.'?api=v'.$version.'"'.' title="'.$match_key.'">'.$match_key.'</A>', $content);
					}
					if ( (preg_match("~\bStandard work\b~",$onlyParent) ) || (preg_match("~\bStandard Work\b~",$onlyParent))){
						$content =  str_replace($value,'Figures with "Run File" <A class="filename" HREF="/download/attachments/'.$IDs['std_id'].'/'.$match_key.'?api=v'.$version.'"'.' title="'.$match_key.'">'.$match_key.'</A>', $content);
					}
					if (( preg_match("~\bPowerpoint\b~",$onlyParent) )|| (preg_match("~\bPower Point\b~",$onlyParent))){
						$content =  str_replace($value,'Figures with "Run File" <A class="filename" HREF="/download/attachments/'.$IDs['pow_id'].'/'.$match_key.'?api=v'.$version.'"'.' title="'.$match_key.'">'.$match_key.'</A>', $content);
					}
					if ( preg_match("~\bStandard Work Templates\b~",$onlyParent) ){
						$content =  str_replace($value,'Figures with "Run File" <A class="filename" HREF="/download/attachments/'.$IDs['std_tmp_id'].'/'.$match_key.'?api=v'.$version.'"'.' title="'.$match_key.'">'.$match_key.'</A>', $content);
					
					}
				
				}elseif($count > 2){
					//Link Parent ID's from another folder by calling getPageID API
					//Create title of corresponsing link
					$parent1 =  $pieces[$count-2];
					$parent2 =  $pieces[$count-3];
					$parent1 = explode(" ", $parent1 );
					if(count($parent1) > 1){
						$parentNew1 = implode('+', $parent1);
					}else{
						$parentNew1 = $parent1[0];
					}

					$parent2 = explode(" ", $parent2 );
					if(count($parent2) > 1){
						$parentNew2 = implode('+', $parent2);
					}else{
						$parentNew2 =$parent2[0];
					}
					
					$title =	$parentNew2."+".$parentNew1;
					echo "title in para:			".$title."<br>";
					
					$anothrParentId 	= 	getPageID($title ,$workspaceKey);
					$content =  str_replace($value,'Figures with "Run File" <A class="filename" HREF="/download/attachments/'.$anothrParentId.'/'.$match_key.'?api=v'.$version.'"'.' title="'.$match_key.'">'.$match_key.'</A>', $content);
				}
			}
		}
    }
    
    //replace & with &amp; quotes and <BR> with <BR/>
	$content      = 	str_replace('&','&amp;', $content);
	$content      = 	str_replace('<BR>','<BR/>', $content);
	$content      = 	str_replace('<COL WIDTH="60%">','', $content);
	$content      = 	str_replace('<COL WIDTH="40%">','', $content);
	$content      = 	str_replace('/ >','/>', $content);
	$content  	  = 	preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $content); 
	return $content;
}

/****************** Function to get content of specific ID *****************/
function getPageID($title ,$workspaceKey){
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, "http://localhost:3000/rest/api/content?title=".$title."&spaceKey=".$workspaceKey."&expand=history");
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
	if(isset($result->results[0])){
		return $result->results[0]->id;
	}
}
/****************** Function to convert WMF to JPG *************************/
function convertWmfToJpg($ext,$filename){
	$dir = substr($filename, 0, strrpos( $filename, '/'));
	try{ 
		    preg_match("/[^\/]+$/", $filename, $matches);
		    $match_key = $matches[0];
		    $file = str_replace($ext, 'JPG',  $match_key);
		    $image = new Imagick(); 
		    $h = fopen($filename, 'rw'); 
		    $image->readImageFile($h); 
		    $image->writeImage($dir."/".$file);
		}catch(Exception $e){ 	} 
    return $file;
}

/****************** Function to get body content of HTML *******************/
function get_string_between($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}
?>