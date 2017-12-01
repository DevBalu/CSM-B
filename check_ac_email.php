<?php 
/*FN1--------------------check_ac_email-------------------*/
/**/
$testMail = 'test@step.ac.uk';

function check_ac_email($email){
	if (filter_var($email, FILTER_VALIDATE_EMAIL) && strtolower(substr(trim($email), -6)) === '.ac.uk' ) {
		return true;
	}

	return false;
}
var_dump(check_ac_email($testMail));


/*FN2--------------------list_dirs-------------------*/
// is_dir -  Tells whether the filename is a directory
// in_array — Checks if a value exists in an array

function dirToArray($dir) {
	$result = [];

	// scandir — List files and directories inside the specified path
	$cdir = scandir($dir); 
	//loop the array
	foreach ($cdir as $key => $value){
		// exclude  (.) and (..)
		if (!in_array($value,[".",".."])){
			// is_dir — Tells whether the filename is a directory
			// DIRECTORY_SEPARATOR - Predefined Constants which separate path
			if (is_dir($dir . DIRECTORY_SEPARATOR . $value)){
				// save name of directory as key in result-array and as value We call function dirToArray with parametre be our value
				// will be repeated previous sequences but with another parametres. 
				$result[$value] = dirToArray($dir . DIRECTORY_SEPARATOR . $value); 
			}
			else{
				$result[] = $value; 
			}
		}
	}

	return $result; 
}

// print'<pre>';
// print_r(dirToArray('C:\xampp\htdocs\cookie_listener\vendor'));
// print'</pre>';


// by FUN7 use trim method
