<?php
$html = "<!DOCTYPE html>
<html>
<head>
    <title>
        My details
    </title>
</head>
<BODY>
<table border=\"1\">
    <thead>
        <th>Name</th>
        <th>Mobile</th>
        <th>Email</th>
        <th>image</th>
    </thead>
    <tbody>
        <td>Vidya
        <td>9856<BR/>325698</td>
        <td>vidya.shevale24@gmail.com</td>
        <td><img src=\"/home/neosoft/Pictures/node.png\"></td>
    </tbody>
</table>


</BODY>
</html>";
$htmlRes = closetags($html);
echo $htmlRes;

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
?>