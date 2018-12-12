<?php
$pathLen = 0;

function prePad($level)
{
  $ss = "";

  for ($ii = 0;  $ii < $level;  $ii++)
  {
    $ss = $ss . "|&nbsp;&nbsp;";
  }

  return $ss;
}
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
            //header("Content-Type: image/jpg"); 
        }catch(Exception $e){   } 
    return $file;
}
function myScanDir($dir, $level, $rootLen)
{
  global $pathLen;

  if ($handle = opendir($dir)) {

    $allFiles = array();

    while (false !== ($entry = readdir($handle))) {
      if ($entry != "." && $entry != "..") {
        if (is_dir($dir . "/" . $entry))
        {
            preg_match("/[^\/]+$/", $dir, $matches);
            $match_key = $matches[0]; 
            //Rempve XLIST folder and files
            if($entry != "XLIST" && $match_key != "XLIST")
            $allFiles[] = "D: " . $dir . "/" . $entry;
        }
        else
        {
            //Conver WMF format to the JPG format
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

   // natsort($allFiles);
    echo "<pre>";
    print_r($allFiles);
    foreach($allFiles as $value)
    {
      $displayName = substr($value, $rootLen + 4);
      $fileName    = substr($value, 3);
      $linkName    = str_replace(" ", "%20", substr($value, $pathLen + 3));
     
      //echo file_get_contents("http://localhost/car/carinventory".$linkName);
       
      if (is_dir($fileName)) {
        //echo prePad($level) . $linkName . "<br>\n";
        myScanDir($fileName, $level + 1, strlen($fileName));
      } else {
        //echo prePad($level) . "<a href=" . $linkName ." style=\"text-decoration:none;\">" . $displayName . "</a><br>\n";
          //echo              "linkname: ".$linkName ."<br>\n".            "Display name: ". $displayName ."<br>\n".
           //"level: ". $level ."<br>\n".
          //  "fileName: ".$fileName."<br>\n";
      }
    }
  }
}

?><!DOCTYPE HTML>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Site Map</title>
</head>

<body>
<h1>Site Map</h1>
<p style="font-family:'Courier New', Courier, monospace; font-size:small;">
<?php
  $root = '/var/www/html/Warburn';

  $pathLen = strlen($root);

  myScanDir($root, 0, strlen($root)); ?>
</p>
</body>

</html>