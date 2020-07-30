<?php
require_once('vendor/autoload.php');
if(hcgrzh\upload\Upload::uploads()===false){
	print_r(hcgrzh\upload\Upload::getError());
}
?>