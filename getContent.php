<?php

    $fileName = "/opt/atlassian/confluence/webapps/blog/CMS/Lab/Processes/LABPRO055_ProceduresRoutineTesting-MAIN.HTML";
    //Read File content 
    $content =  file_get_contents($fileName);

    //Remove script , style, map from the html content
    $content =  preg_replace('#<style(.*?)>(.*?)</style>#is', '', $content);
    $content =  preg_replace('#<script(.*?)>(.*?)</script>#is', '', $content);
    $content =  preg_replace('#<map(.*?)>(.*?)</map>#is', '', $content);
    $content =  str_replace('.GIF">','.GIF"/>', $content);
    $html =     str_replace('.gif">','.GIF"/>', $content);

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
    
        $newImageArr    =   array_combine($src, $imgName);
        $patterns       =   array();
        $replacements   =   array();
        foreach ($newImageArr as $key => $value ) {
           /* 
            $patterns[] = '<IMG USEMAP="#map1" SRC="'.$key.'">';
            $replacements[] = 'img style="max-width: 100%;max-height: 100%;" src ="/download/attachments/'.$parentID.'/'.$value.'?api=v'.$version.'"/';*/

            $patterns[] = '<IMG USEMAP="#map1" SRC="'.$key.'">';
            $replacements[] = "ac:image ac:width= '1024'><ri:attachment ri:fileName='$value' </ac:image>";
        }

        $content      =     preg_replace($patterns, $replacements, $content);
        $content      =     str_replace('</ac:image>/>', '/></ac:image>', $content );
        $content      =     str_replace('</ac:image>>', '/></ac:image>', $content );
    }
    
   
    //Convert all documents into downloadable links
    $href =array();
    preg_match_all( '@HREF="([^"]+)"@' , $content , $matchHref );
    $href = array_pop($matchHref);
    $href = array_unique($href);
    echo "<pre>";
    print_r($href);
        foreach ($href as $key => $value) {
        $value = chop($value,'"');
        $ext = pathinfo($value, PATHINFO_EXTENSION);
        
        if($ext == 'DOC' || $ext == 'PPT' || $ext == 'XLS' || $ext == 'doc' || $ext == 'ppt' || $ext == 'xls' || $ext == 'pdf' || $ext == 'PDF'){
        
        $pieces = explode("\\", $value );
        foreach ($pieces as $k => $v) {
            if($v == '..' || $v == "Figures with 'Run File' .." || strpos($v,'Figures with') !== false)
            unset($pieces[$k]);
        }
        $pieces     =   array_values($pieces);
        $count      =   count($pieces);
        $match_key  =   $pieces[$count-1];
        $match_key  =   ltrim($match_key, '.');
        $match_key  =   str_replace('Production', '', $match_key);
        $match_key  =   str_replace('Forms', '', $match_key);

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
            $pieces       =     array_values($pieces);
            $count        =     count($pieces);
            $match_key    =     $pieces[$count-1];
            $match_key    =     str_replace('NASA', '',$match_key);
            $match_key    =     str_replace('WIOP', '',$match_key);
        
           // $value   =  str_replace('"', '',$value);    echo "value".$value;
            $content =  str_replace($value,'<ac:link><ri:attachment ri:filename="'.$match_key.'" /><ac:plain-text-link-body><![CDATA['.$match_key.']]></ac:plain-text-link-body></ac:link>', $content);
            }
        }
    }
    
    //replace & with &amp; quotes and <BR> with <BR/>
    $content      =     str_replace('&','&amp;', $content);
    $content      =     str_replace('<BR>','<BR/>', $content);
    $content      =     str_replace('<COL WIDTH="60%">','', $content);
    $content      =     str_replace('<COL WIDTH="40%">','', $content);
    $content      =     str_replace('<COL WIDTH="15%">','', $content);
    $content      =     str_replace('<COL WIDTH="80%">','', $content);
    $content      =     str_replace('<HR>','', $content);
    $content      =     str_replace('<5000','less than 5000', $content);
    $content      =     str_replace('<=','less than equal to', $content);
    $content      =     str_replace('/ >','/>', $content);
    $content      =     preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $content); 
    echo $content;
    //Change your file title ,Parent ID , Space key here
    $page = array(
        "type"=>"page",
        "title"=>'hi',
        "space"=>array("key"=>"NEO"),
        "body"=>array(
                "storage"   => array(
                "value"     =>  "Home",
                "representation"=>"storage"
            )
        )
    );
    //Change your credentials here
    $username = 'admin';
    $password = 'Abc@1234';
    $host     = 'http://localhost:8090/';
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
    print_r($result);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close ($ch);

    /****************** Function to get body content of HTML *******************/
    function get_string_between($string, $start, $end){
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }
  

