<?php 

//Increase maximum execution time

ini_set('max_execution_time', 180000);
ini_set('post_max_size', '100M');
ini_set('upload_max_filesize', '50M');

//Add folder name with correct path here
$root = "{$_SERVER['DOCUMENT_ROOT']}/CMS";

//set your login details and port accordingly
$username = 'admin';
$password = 'Abc@1234';
$host     = 'http://localhost:8090/';

$pathLen = strlen($root);
myScanDir($root, 0, strlen($root),  $parentID = '', $workspaceKey = '', $version = '');
/******** Function to read directory and files in recursive order **********/
function myScanDir($dir, $level, $rootLen, $parentID = '', $workspaceKey = '', $version = '' )
{ 

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
		        	//Get only ppt,doc , xls jpg, pdf files in array
		            $ext = pathinfo($entry, PATHINFO_EXTENSION);
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
	}

	global $pathLen; 
	$version = 1; 
	global $workspaceKey; 
	if($level == 0){
		/*Change Space name and ParentID here*/
		$workspaceKey = 'NEO';
		$parentID = 'NEO';
		$parentID =  create_page($dir, $parentID , $level, $allFiles);
	}
	if($level >= 1){
		//Call to Create page child API to create folder name
		$parentID   = create_page_child($dir, $parentID, $workspaceKey , $content = '' ,$level , $ext = '', $allFiles);
	}
	if ($handle = opendir($dir)) {
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
						//Call to parse_HTML function to get html body in json format
						$data = parse_HTML($fileName, $parentID, $version , $workspaceKey );
						//Call to Create page child API to create HTML page
						create_page_child($fileName , $parentID, $workspaceKey ,$data ,$level ,$ext, $allFiles);
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
	      				if(!empty($parentID)){
	      					if(file_exists($fileName))
	      					upload_attachment($parentID , $fileName ,'FromFolder'); 
	      				}
	      			}
	      		}
			} 
 	    }
	}
}

/********************* Function to create page  ****************************/
function create_page($fileName, $parentID ,$level, $allFiles)
{
	preg_match("/[^\/]+$/", $fileName, $matches);
	$match_key = $matches[0]; 
	$page = array(
        "type"=>"page",
        "title"=>$match_key,
        "space"=>array("key"=>$parentID),
        "body"=>array(
                "storage"	=> array(
                "value"		=> 	$match_key." Home",
                "representation"=>"storage"
            )
        )
    );
    $qbody = json_encode($page);	
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL,$GLOBALS['host']."rest/api/content/");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $qbody);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	    
    $headers = array();
    $headers[] = "Content-Type: application/json";
    $headers[] = "Authorization: Basic ".base64_encode($GLOBALS['username'].":".$GLOBALS['password']);
    $headers[] = "Cache-Control: no-cache";
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    //echo "<pre>";
    //print_r($result);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close ($ch);
    $result = json_decode($result);
    if(isset($result->id))
    return $result->id;
}

/****************** Function to create child page **************************/
function create_page_child($fileName, $parentID, $workspaceKey ,$data,$level ,$ext, $allFiles)
{	
	//echo "file:".$fileName."</br>";

	if(!empty($data)){
		$content    = $data['html'];
	    $attachment = $data['attachment'];
	}
	else{
		$content 	=   '';
		$body 		=	'';
	}
	$pieces 		= 	explode("/", $fileName );
	$count 			= 	count($pieces);
	$match_key 		= 	$pieces[$count-1];
	$parent_key 	= 	$pieces[$count-2];
	$top_parent_key = 	$pieces[$count-3];
	if($level == 1){
		if(($ext == 'html' || $ext == 'htm' || $ext == 'HTML' || $ext == 'HTM' )){
			//Replace _ and -MAIN from html page name
			$title 	     =   $match_key;
			$title 	 	 = 	 str_replace('-MAIN','', $title);
			$title 	 	 = 	 str_replace('.'.$ext,'', $title);
			$title 	 	 = 	 str_replace('_',' ', $title);
			$body  		 =   $content;
			if($title == 'CMS'){
				$title = 'CMS-PAGE';
			}
		}else{
			$title 	     =   $match_key;
			if(!empty($allFiles)){
				$body  = '';
				foreach ($allFiles as $k => $v) {
					$file 			= 	explode("/", $v );
					$cnt 			= 	count($file);
					$fname 			= 	$file[$cnt-1];

					$extFile = pathinfo($fname, PATHINFO_EXTENSION);
					if(
						($extFile == 'DOC') || 
						($extFile == 'PPT') || 
						($extFile == 'XLS') || 
						($extFile == 'doc') || 
						($extFile == 'ppt') || 
						($extFile == 'xls') || 
						($extFile == 'pdf') || 
						($extFile == 'PDF')
					){
						//Create downloadable link for the documents
						$titleDoc = preg_replace("@\s+@",' ',htmlspecialchars(addslashes($fname))); 
						$body .= "<ac:link><ri:attachment ri:filename='$titleDoc' /><ac:plain-text-link-body><![CDATA[$titleDoc]]></ac:plain-text-link-body></ac:link><BR />";
					}
				}
			}
		}
	}
	if($level == 2 ){
		if(($ext == 'html' || $ext == 'htm' || $ext == 'HTML' || $ext == 'HTM' )){
			$title 	     =   $match_key;
			$title 	 	 = 	 str_replace('-MAIN','', $title);
			$title 	 	 = 	 str_replace('.'.$ext,'', $title);
			$title 	 	 = 	 str_replace('_',' ', $title);
			$body  		 =   $content;
		}else{
			$title 		 = $parent_key." ".$match_key;
			if(!empty($allFiles)){
				$body  = '';
				foreach ($allFiles as $k => $v) {
					$file 			= 	explode("/", $v );
					$cnt 			= 	count($file);
					$fname 			= 	$file[$cnt-1];
					$extFile = pathinfo($fname, PATHINFO_EXTENSION);
					if(($extFile == 'DOC') || 
						($extFile == 'PPT') || 
						($extFile == 'XLS') || 
						($extFile == 'doc') || 
						($extFile == 'ppt') || 
						($extFile == 'xls') || 
						($extFile == 'pdf') || 
						($extFile == 'PDF')
					){
						//Replace special characters in doc name
						$titleDoc = preg_replace("@\s+@",' ',htmlspecialchars(addslashes($fname))); 
						$body .= "<ac:link><ri:attachment ri:filename='$titleDoc' /><ac:plain-text-link-body><![CDATA[$titleDoc]]></ac:plain-text-link-body></ac:link><BR />";
					}
				}
			}
		}
	}
	if($level >= 3 ){
		if(($ext == 'html' || $ext == 'htm' || $ext == 'HTML' || $ext == 'HTM' )){
			$title 	     =   $match_key;
			$title 	 	 = 	 str_replace('-MAIN','', $title);
			$title 	 	 = 	 str_replace('.'.$ext,'', $title);
			$title 	 	 = 	 str_replace('_',' ', $title);
			$body  		 =   $content;
		}else{
			$title       =   $top_parent_key." ".$parent_key." ".$match_key;
			if(!empty($allFiles)){
				$body  = '';
				foreach ($allFiles as $k => $v) {
					$file 			= 	explode("/", $v );
					$cnt 			= 	count($file);
					$fname 			= 	$file[$cnt-1];
					$extFile = pathinfo($fname, PATHINFO_EXTENSION);
					if(
						($extFile == 'DOC') || 
						($extFile == 'PPT') || 
						($extFile == 'XLS') || 
						($extFile == 'doc') || 
						($extFile == 'ppt') || 
						($extFile == 'xls') || 
						($extFile == 'pdf') || 
						($extFile == 'PDF')
					){
						$titleDoc = preg_replace("@\s+@",' ',htmlspecialchars(addslashes($fname))); 
						$body .= "<ac:link><ri:attachment ri:filename='$titleDoc' /><ac:plain-text-link-body><![CDATA[$titleDoc]]></ac:plain-text-link-body></ac:link><BR />";
					}
				}
			}
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
					    "value"=>"$body",
                    	"representation"=>"storage"
                )
            )           
    );
    $qbodyChild = json_encode($requestChild);

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL,$GLOBALS['host']."rest/api/content/");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $qbodyChild);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

	$headers = array();
    $headers[] = "Content-Type: application/json";
    $headers[] = "Authorization: Basic ".base64_encode($GLOBALS['username'].":".$GLOBALS['password']);
    $headers[] = "Cache-Control: no-cache";
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	$result = curl_exec($ch);
	//echo "<pre>";
	//print_r($result);
	if (curl_errno($ch)) {
		echo 'Error:' . curl_error($ch);die;
	}
	curl_close ($ch);
	$result = json_decode($result);
	
	if(isset($result->id))
	{
		if(!empty($attachment)){
			//Call to upload file function for attach file in current page
			upload_file($result->id , $fileName ,$attachment ); 
		}
		return $result->id;
	}

}

/*********************** Function to upload a file  ************************/
function upload_attachment($contentID , $fileName , $FromName ){
	//echo "fileName:".$fileName."<br>";
	//echo "FromName:".$FromName."<br>";
	$request_url = $GLOBALS['host'].'rest/api/content/'.$contentID.'/child/attachment';
	
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

    $headers = array();
    $headers[] = "Authorization: Basic ".base64_encode($GLOBALS['username'].":".$GLOBALS['password']);
    $headers[] = "X-Atlassian-Token: no-check";
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    $info   = curl_getinfo($ch);
    
   /* echo "<pre>";
    print_r($result);
    print_r($info);*/
    
    if (curl_errno($ch)) {
    	echo 'Error :' . curl_error($ch);
	}
    curl_close ($ch);
}

/********************** Function to parse HTML file ************************/

function parse_HTML($fileName, $parentID, $version ,$workspaceKey){
	//Read File content 
	//echo "FileName HTML:".$fileName."<br>";
	$file_array = array();
	$content = 	file_get_contents($fileName);

	//Remove script , style, map from the html content
	$content = 	preg_replace('#<style(.*?)>(.*?)</style>#is', '', $content);
	$content = 	preg_replace('#<script(.*?)>(.*?)</script>#is', '', $content);
	$content = 	preg_replace('#<map(.*?)>(.*?)</map>#is', '', $content);
	$content =  str_replace('.GIF">','.GIF"/>', $content);
    $html 	 =  str_replace('.gif">','.GIF"/>', $content);

	//find all BODY with JS and CSS tags and replace with only BODY tag
	if (strpos($html, '<BODY ONUNLOAD="javascript:dounload();" LINK="#0000ff" VLINK="#800080">') !== false){
	    $html = str_replace('<BODY ONUNLOAD="javascript:dounload();" LINK="#0000ff" VLINK="#800080">',"<BODY>", $html);
	}

	if (strpos($html,'<BODY ONUNLOAD="javascript:dounload();">') !== false) {
	    $html = str_replace('<BODY ONUNLOAD="javascript:dounload();">',"<BODY>", $html);
	}

	if (strpos($html,'<BODY bgcolor="#FFFFFF">') !== false) {
        $html = str_replace('<BODY bgcolor="#FFFFFF">',"<BODY>", $html);
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
	if((!empty($src)) && (!empty($imgName))){
		$newImageArr   	= 	array_combine($src, $imgName);
		
		$patterns 		= 	array();
		$replacements 	=	array();
		foreach ($newImageArr as $key => $value ) {
			//Store doc,ppt,pdf,jpg data in $file_array for upload attachment 
			$file_array[] = $value;
			
			/*Using Html Image tag -- Method 1
			$patterns[] = '<IMG USEMAP="#map1" SRC="'.$key.'">';
	        $replacements[] = 'img style="max-width: 100%;max-height: 100%;" src ="/download/attachments/'.$parentID.'/'.$value.'?api=v'.$version.'"/';

	        */
	       	/*Using Confluence value storage format -- Method 2
	        $patterns[] = '<IMG USEMAP="#map1" SRC="'.$key.'">';
	        $replacements[] = "ac:image ac:width= '1024'><ri:url ri:value='/download/attachments/$parentID/$value?api=v$version' </ac:image>";
	        */

	        //Using Confluence filename storage format -- Method 3
	        $patterns[] = '<IMG USEMAP="#map1" SRC="'.$key.'">';
	        $replacements[] = "ac:image ac:width= '1024'><ri:attachment ri:fileName='$value' </ac:image>";

	  	}
		$content      =     preg_replace($patterns, $replacements, $content);
	    $content      =     str_replace('</ac:image>/>', '/></ac:image>', $content );
	    $content      =     str_replace('</ac:image>>', '/></ac:image>', $content );
	}
		//Convert all documents into downloadable links
		$href = array();
		preg_match_all( '@HREF="([^"]+)"@' , $content , $matchHref );
		$href = array_pop($matchHref);
		$href = array_unique($href);
		$href = array_values($href);
	
		foreach ($href as $key => $value) {
		$value = chop($value,'"');
		$ext = pathinfo($value, PATHINFO_EXTENSION);
		
		if($ext == 'DOC' || $ext == 'PPT' || $ext == 'XLS' || $ext == 'doc' || $ext == 'ppt' || $ext == 'xls' || $ext == 'pdf' || $ext == 'PDF'){

		
		$pieces = explode("\\", $value );
		foreach ($pieces as $k => $v) {
			if($v == '..' || $v == "Figures with 'Run File' .." || strpos($v,'Figures with') !== false)
			unset($pieces[$k]);
		}
		$pieces  		= 	array_values($pieces);
		$count 			= 	count($pieces);
	 	$match_key 		= 	$pieces[$count-1];
	 	/*
	 	$match_key    	=   str_replace('NASA', '',$match_key);
	 	$match_key    	=   str_replace('WIOP', '',$match_key);
	 	$match_key    	=   str_replace('NASU', '',$match_key);
	 	$match_key    	=   ltrim($match_key, '.');
        $match_key    	=   str_replace('Production', '', $match_key);
        $match_key    	=   str_replace('Forms', '', $match_key);*/

	 	$file_array[] 	= 	implode('/',$pieces);
	    $content        =   str_replace($value, '', $content);
        $content    	=   str_replace('<A HREF="" TARGET="_parent">'.$match_key.'</A>', '<ac:link><ri:attachment ri:filename="'.$match_key.'" /><ac:plain-text-link-body><![CDATA['.$match_key.']]></ac:plain-text-link-body></ac:link>', $content);
        //$content    	=   str_replace('<A HREF="" TARGET="_parent">', '<ac:link><ri:attachment ri:filename="'.$match_key.'" /><ac:plain-text-link-body><![CDATA['.$match_key.']]></ac:plain-text-link-body></ac:link>', $content);
		}
	}

	//Get body content from Html file
  	$pattern="/<p>(.+?)<\/p>/i";
    preg_match_all($pattern,$content,$matches,PREG_PATTERN_ORDER);

    if(!empty($matches)){
		foreach ($matches[1] as $value) {
		$value  =  chop($value,'"');
		$ext = pathinfo($value, PATHINFO_EXTENSION);	
		if($ext == 'DOC' || $ext == 'PPT' || $ext == 'XLS' || $ext == 'doc' || $ext == 'ppt' || $ext == 'xls' || $ext == 'PDF' || $ext == 'pdf'){	
			
				$pieces = explode("/", $value );
				foreach ($pieces as $k => $v) {
					if($v == '..' || $v == "Figures with 'Run File' .." || strpos($v,'Figures with') !== false)
					unset($pieces[$k]);
				}
				$pieces  	  = 	array_values($pieces);
				$count 		  =  	count($pieces);
				$match_key    = 	$pieces[$count-1];
			 	/*$match_key  =     str_replace('NASA', '',$match_key);
			 	$match_key    =     str_replace('WIOP', '',$match_key);
			 	$match_key    =     str_replace('NASU', '',$match_key);*/
				$file_array[] = 	implode('/',$pieces);
				$content =  str_replace($value,"Figures with 'Run File' <ac:link><ri:attachment ri:filename='".$match_key."' /><ac:plain-text-link-body><![CDATA[".$match_key."]]></ac:plain-text-link-body></ac:link>", $content);
			}
		}
    }
    
    //replace & with &amp; quotes and <BR> with <BR/> which are breaking json
	$content      = 	str_replace('&','&amp;', $content);
	$content      = 	str_replace('<BR>','<BR/>', $content);
	$content      = 	str_replace('<COL WIDTH="60%">','', $content);
	$content      = 	str_replace('<COL WIDTH="40%">','', $content);
	$content      =     str_replace('<COL WIDTH="15%">','', $content);
    $content      =     str_replace('<COL WIDTH="80%">','', $content);
    $content      =     str_replace('<HR>','', $content);
    $content      =     str_replace('<5000','less than 5000', $content);
    $content      =     str_replace('<=','less than equal to', $content);
	$content      = 	str_replace('/ >','/>', $content);
	$content      = 	str_replace('</ac:link>"</P>','</ac:link></P>', $content);
	$content  	  = 	preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $content); 

	$data['html'] 		= 	$content;
	$file_array 		=	array_unique($file_array);
	$file_array 		=	array_values($file_array);
	$data['attachment'] = 	$file_array ;
	return $data;
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

function upload_file($contentID , $filePath ,$data)
{

	/*echo "<pre>";
	echo "MyFile:".$filePath."<br>";
	print_r($data);*/
	foreach ( $data as $key => $value ) {
        $pieces     =   explode("/", $value );
        if(in_array('..', $pieces)){
            $title =   implode('/', $pieces);
            $title =   str_replace('../../', '', $title);
            $title =   "{$_SERVER['DOCUMENT_ROOT']}".'/'.$title;
            //echo "<BR>First IF<BR>:".$title;
            if(file_exists($title))
            upload_attachment($contentID , $title ,'fromHTML');
        }
        elseif(in_array('Business Rules', $pieces)){
            $title =   implode('/', $pieces);
            $title =   "{$_SERVER['DOCUMENT_ROOT']}/CMS".'/'.$title;
            //echo "<BR>First ELSE IF<BR>:".$title;
            if(file_exists($title))
            upload_attachment($contentID , $title ,'fromHTML');
        }else{
                $count        =   count($pieces);
                $match_key    =   $pieces[$count-1];
                /*$match_key  =   str_replace('NASA', '',$match_key);
                $match_key    =   str_replace('WIOP', '',$match_key);
                $match_key    =   str_replace('NASU', '',$match_key);*/
		        if($count == 1) {
		            $piecesFile     =   explode("/", $filePath );
		            $piecesCount    =   count($piecesFile);
		            $fileName       =   str_replace(
		                                                $piecesFile[$piecesCount-1], 
		                                                $match_key,
		                                                $filePath
		                                            );
		            //echo "<BR>IF 1<BR>:".$fileName;
		            if(file_exists($fileName))
		            upload_attachment($contentID , $fileName ,'fromHTML');
		       }
		        elseif( $count == 2 ) {
		            $piecesFile     =   explode("/", $filePath );
		            $piecesCount    =   count($piecesFile);
		            $fileName       =   str_replace(
		                                                $piecesFile[$piecesCount-2].'/'.
		                                                $piecesFile[$piecesCount-1],
		                                                $pieces[$count-2].'/'.
		                                                $match_key, 
		                                                $filePath
		                                            );
		            //echo "<BR>IF 2<BR>:".$fileName;
		            if(file_exists($fileName))
		            upload_attachment($contentID , $fileName ,'fromHTML');
		        }
		        elseif( $count == 3 ) {

		            $piecesFile     =   explode("/", $filePath );
		            $piecesCount    =   count($piecesFile);
		            if(  $pieces[$count-3] == 'Standard Work' ){
		                $fileName       =   str_replace(
		                                                
		                                                $piecesFile[$piecesCount-2].'/'.
		                                                $piecesFile[$piecesCount-1],
		                                                $pieces[$count-3].'/'.
		                                                $pieces[$count-2].'/'.
		                                                $match_key,
		                                                $filePath
		                                            );
		                //echo "<BR>IF 3<BR>:".$fileName;
		                if(file_exists($fileName))
		                upload_attachment($contentID , $fileName ,'fromHTML');
		            }else{
		                $fileName       =   str_replace(
		                                                $piecesFile[$piecesCount-3].'/'.
		                                                $piecesFile[$piecesCount-2].'/'.
		                                                $piecesFile[$piecesCount-1],
		                                                $pieces[$count-3].'/'.
		                                                $pieces[$count-2].'/'.
		                                                $match_key, 
		                                                $filePath
		                                            );
		                //echo "<BR>Else 3<BR>:".$fileName;
		                if(file_exists($fileName))
		            	upload_attachment($contentID , $fileName ,'fromHTML');
		            }
		        }
		        elseif( $count == 4 ) {
		            $piecesFile     =   explode("/", $filePath );
		            $piecesCount    =   count($piecesFile);
		            if(  $pieces[$count-4] == 'Standard Work'){
		                    $fileName       =   str_replace(
                                                    
                                                    $piecesFile[$piecesCount-2].'/'.
                                                    $piecesFile[$piecesCount-1],
                                                    $pieces[$count-4].'/'.
                                                    $pieces[$count-3].'/'.
                                                    $pieces[$count-2].'/'.
                                                    $match_key,
                                                    $filePath
                                                );
                    //echo "<BR>IF 4<BR>:".$fileName;
		            if(file_exists($fileName))        
                    upload_attachment($contentID , $fileName ,'fromHTML');
                    }else{
                    $fileName       =   str_replace(
                                                $piecesFile[$piecesCount-2].'/'.
                                                $piecesFile[$piecesCount-1],
                                                $pieces[$count-3].'/'.
                                                $pieces[$count-2].'/'.
                                                $match_key, 
                                                $filePath
                                            );
                    //echo "<BR>Else 4<BR>:".$fileName;
                    if(file_exists($fileName))
                    upload_attachment($contentID , $fileName ,'fromHTML');
                }
            }
        }
    }
}
?>
