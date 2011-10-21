<?php
include('config.php');
include('DBObject.php');
$db=new DBObject();
//conect to mysql
$db->db_connect($conf_host,$conf_user,$conf_pass,$conf_db);

$query_array = explode('/',@$_GET['string']);
//set value
@list($option, $author, $type, $taget,$filename) = $query_array;
//echo base64_decode ($taget);die;
//insert to db
$db->query("INSERT INTO `medium` (`option`,  `author`,`type`, `taget`, `ip`, `time`) VALUES (".$option.", ".$author.", ".$type.", '".base64_decode ($taget)."', '".$_SERVER['REMOTE_ADDR']."', '".date('Y-m-d H:i:s')."')");
    if(empty($type)){
        if((int)$option==3){
            include('html/'.$conf_iframe_template);
        }else{
            // get file name
            $filename=empty($filename)?$conf_image_default:$filename;
            
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
            @readfile('images/'.$filename); 
        }
    }else{
        if(!empty($taget))
		{
			header("Location: ".base64_decode ($taget));
		}else{
			header("Location: $conf_default_link");
		}
    }
?>