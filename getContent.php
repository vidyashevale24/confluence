<?php
$parentID = 111;
/*$content = 111;
$parentID = 111;
$data ="<ac:image><ri:url ri:value=\"/download/attachments/$parentID/$content?api=v$version\" /></ac:image>";
 $content = "<IMG USEMAP=\"#map1\" SRC=\"PROPRO001_ReceiveDryGoods.WMF\"/>";
    $content = preg_replace("/<img[^>]+\>/i", $data, $content); 
*/
   // echo $content;die;
//Get body content from Html file
$fileName = "/var/www/html/Warburn/dirConf2/Forms1/demo.html";
$content = file_get_contents($fileName);

//to remove script and style from page
$content = preg_replace('#<style(.*?)>(.*?)</style>#is', '', $content);
$html = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $content);

//find all BODY with JS and CSS tags and replace with only BODY tag

if (strpos($html, '<BODY ONUNLOAD="javascript:dounload();" LINK="#0000ff" VLINK="#800080">') !== false){
    $html = str_replace('<BODY ONUNLOAD="javascript:dounload();" LINK="#0000ff" VLINK="#800080">',"<BODY>", $html);
}

if (strpos($html,'<BODY ONUNLOAD="javascript:dounload();">') !== false) {
    $html = str_replace('<BODY ONUNLOAD="javascript:dounload();">',"<BODY>", $html);
}

//Get body content from Html file
$html  = get_string_between($html, '<BODY>', '</BODY>');

//$bodyContent = get_string_between($html, 'SRC="', '"');
preg_match_all( '@SRC="([^"]+)"@' , $html, $match );

$bodyContent = array_pop($match);

foreach ($bodyContent as $value) {
    $imgData = str_replace('WMF','jpg', $value);
    $data ="<ac:image><ri:url ri:value=\"/download/attachments/$parentID/$imgData?api=v$version\" /></ac:image>";
    echo $data;
}

// replace .WMF with .jpg

die;

//replace image tag with confluence format
$html = preg_replace_all("/<IMG[^>]+\>/i", $data, $html); 

//replace & with &amp; quotes
$content       = str_replace('&','&amp;', $html);

// remove line breaks and whitespace
$content  =  preg_replace('/[ \t]+/', ' ', preg_replace('/[\r\n]+/', "\n", $content));

echo $html;
function get_string_between($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

die;
function closetags($html) {
    preg_match_all('#<(?!meta|img|br|hr|input\b)\b([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
    $openedtags = $result[1];
    preg_match_all('#</([a-z]+)>#iU', $html, $result);
    $closedtags = $result[1];
    $len_opened = count($openedtags);
    if (count($closedtags) == $len_opened) {
        return $html;
    }
    $openedtags = array_reverse($openedtags);
    for ($i=0; $i < $len_opened; $i++) {
        if (!in_array($openedtags[$i], $closedtags)) {
            $html .= '</'.$openedtags[$i].'>';
        } else {
            unset($closedtags[array_search($openedtags[$i], $closedtags)]);
        }
    }
    return $html;
} 