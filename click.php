<?php
$query_array = explode('/',@$_GET['string']);
//set value
list($option, $author, $type, $download,$filename) = $query_array;
// get file name
$filename=empty($filename)?'Snapshot_20110905_1.jpg':$filename;
$fileInfo = pathinfo($filename); 
//return extension of file
$ext = strtolower($fileInfo["extension"]); 

switch ($ext) {
        case "flv": $ctype = "flv-application/octet-stream"; break;
        case "mp4": $ctype = "video/mp4"; break;
        case "swf": $ctype = "application/x-shockwave-flash"; break;
        case "pdf": $ctype = "application/pdf"; break;
        case "exe": $ctype = "application/octet-stream"; break;
        case "zip": $ctype = "application/zip"; break;
        case "doc": $ctype = "application/msword"; break;
        case "xls": $ctype = "application/vnd.ms-excel"; break;
        case "ppt": $ctype = "application/vnd.ms-powerpoint"; break;
        case "gif": $ctype = "image/gif"; break;
        case "png": $ctype = "image/png"; break;
        case "jpeg":
        case "jpg": $ctype="image/jpg"; break;
        default: $ctype="application/force-download";
    }

@header ('content-type: '.$ctype);
@readfile($filename); 
?>