<?php

function nvl($val, $replace) {

    if( is_null($val) || $val === '' )  
    	$ret = $replace;
    else 
    	$ret = $val;
	
	return $ret;
	
}


function cut_text( $string , $length, $continue_sign=" ...") {

	if ($length > 0) {
		$expl = explode('|', wordwrap($string, $length, '|')); 
		$ret = $expl[0] . $continue_sign;
	} else 
		$ret = '';

	return $ret; 	

}

function showAdminMessage($message, $errormsg = false)
{
	if ($errormsg) {
		echo '<div id="message" class="error">';
	}
	else {
		echo '<div id="message" class="updated fade">';
	}

	echo "<p><strong>$message</strong></p></div>";
}   

function var2text($var) {
		ob_start();
		print_r($var);
		$ret = ob_get_clean();
		return $ret;
}				

function logToFile($filename, $msg) { 

	if ( !isset($filename) or $filename=='nvl' or $filename==null) {
		$filename = untrailingslashit( plugin_dir_path( __FILE__ ) ).'/log.txt';
	}

	// open file
	$fd = fopen($filename, "a");
	// append date/time to message
	$str = "[" . date("Y/m/d h:i:s", time()) . "] " . $msg; 	
	// write string
	fwrite($fd, $str . "\n");
	// close file
	fclose($fd);
}

?>