<?php

$fileName = '/var/www/html/Warburn/Winemaking/Processes/WINPRO005_VintageOperations.HTML';
$content = 	file_get_contents($fileName);
$IDs['pow_id']= 232323;
$IDs['ppm_id']= 232323;
$IDs['std_id']= 232323;
$IDs['form_id']= 2323;
$IDs['form_id']= 2323;
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
	
	// remove line breaks and whitespace
	/*$content  	  =  	preg_replace('/[ \t]+/', ' ', preg_replace('/[\r\n]+/', "\n", $content));*/

	$page = array(
        "type"=>"page",
        "title"=>"as",
        "space"=>array("key"=>"DEMO"),
        "body"=>array(
                "storage"	=> array(
                "value"		=> 	"$content",
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

	function get_string_between($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}
	?>