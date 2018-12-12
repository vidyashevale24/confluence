<?php 
try{ 
    $filename = '/home/neosoft/Desktop/SALPRO006_ProductSpecNewOrUpdated-SCALED.WMF'; 
    preg_match("/[^\/]+$/", $filename, $matches);
    $match_key = $matches[0];
    $file = str_replace("WMF", "JPG",  $match_key);
    $image = new Imagick(); 
    $h = fopen($filename, 'rw'); 
    $image->readImageFile($h); 
    $image->writeImage("/var/www/html/confluence/".$file);
    header("Content-Type: image/jpg"); 
    print($image);
 }catch(Exception $e){ 
    echo $e->getMessage(); 
} 
?>