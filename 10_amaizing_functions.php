<?php 
/*FN.1--------------------------------------------------------------------------check_ac_email-------------------
*	filter_var — Filters a variable with a specified filter
*	The FILTER_VALIDATE_EMAIL filter validates an e-mail address.
*	strtolower — Make a string lowercase
*
*	substr — Return part of a string
*		return: Returns the portion of string specified by the start and length parameters.
*
*	trim — Strip whitespace (or other characters) from the beginning and end of a string
*/
$testMail = 'test@step.ac.uk';

function check_ac_email($email){
	// check if email is valid and is ended by '.ac.uk'
	if (filter_var($email, FILTER_VALIDATE_EMAIL) && strtolower(substr(trim($email), -6)) === '.ac.uk' ) {
		return true;
	}

	return false;
}
/* Usage */
//to display the result, uncomment the line below.
// var_dump(check_ac_email($testMail));


/*FN.2--------------------------------------------------------------------------list_dirs-------------------
*	scandir — List files and directories inside the specified path
*	in_array — Checks if a value exists in an array
*	is_dir — Tells whether the filename is a directory
*	DIRECTORY_SEPARATOR - Predefined Constants which separate path
*	print_r — Prints human-readable information about a variable
*/
function list_dirs($dir) {
	$result = [];

	$cdir = scandir($dir); 
	//loop the array
	foreach ($cdir as $key => $value){
		// exclude  (.) and (..)
		if (!in_array($value,[".",".."])){
			//we check if $value is directory. If it is directory we save list of files and directories of the specified path in array separately within array $result 
			if (is_dir($dir . DIRECTORY_SEPARATOR . $value)){
				// save the name of directory as key in result-array and as value we call function list_dir with another parameters (path) what we get from loop from $value. 
				$result[$value] = list_dirs($dir . DIRECTORY_SEPARATOR . $value); 
			}
			// else is not directory we simple save value in arr $result
			else{
				$result[] = $value; 
			}
		}
	}

	return $result; 
}
/* Usage */
//to display the result, uncomment the three line below.
// print'<pre>';
// print_r(list_dirs('C:\xampp\htdocs\cookie_listener\vendor'));
// print'</pre>';


/*FN.3--------------------------------------------------------------------------count_files_of_type-------------------
*	scandir — List files and directories inside the specified path, return array with value
*	trim — Strip whitespace (or other characters) from the beginning and end of a string
*	in_array — Checks if a value exists in an array
*	is_dir — Tells whether the filename is a directory
*	isset — Determine if a variable is set and is not NULL
*	pathinfo — Returns information about a file path
*		desc: mixed pathinfo ( string $path [, int $options = PATHINFO_DIRNAME | PATHINFO_BASENAME | PATHINFO_EXTENSION | PATHINFO_FILENAME ] *			)
*	DIRECTORY_SEPARATOR - Predefined Constants which separate path
*	print_r — Prints human-readable information about a variable
*/
function count_files_of_type($dir, $ext){
	$result = '';
	$cdir = scandir($dir);
	// remove whitespace
	$extention = trim($ext);
	// counter
	$count = 0;
	// loop array with values
	foreach ($cdir as $key => $value){
		// exclude  (.) and (..)
		if (!in_array($value,[".",".."])){
			if (!is_dir($dir . DIRECTORY_SEPARATOR . $value)){
				$pathinfo = pathinfo($value);

				// if extention exist
				if (isset($pathinfo['extension'])) {
					// checks if the extension that we set is equal to the value of the file 
					if ($pathinfo['extension'] == $extention) {
						$count++;
					}
				}
				//if extension of file does not exist and extension that we find is  equal to 'txt' set default value txt
				else if (!isset($pathinfo['extension']) && $extention == 'txt') {
						$count++;
				}

			}
		}
	}

	return $count;
}
/* Usage */
//to display the result, uncomment the line below.

// print count_files_of_type('C:\xampp\htdocs\cookie_listener\test', 'php');


/*FN.4--------------------------------------------------------------------------rand_array-------------------
*	rand — Generate a random integer
*	print_r — Prints human-readable information about a variable
*/
function rand_array($count, $lower, $upper){
	//arr with random numbers
	$result = [];
	//we loop as many times as indicated in variable $count
	for($i = 0; $i < $count; $i++){
		$result[] = rand($lower, $upper);
	}

	return $result;
}
/* Usage */
//to display the result, uncomment the three line below.
// print '<pre>';
// print_r(rand_array(20, 0, 20));
// print '</pre>';


/*FN.5--------------------------------------------------------------------------num_match_array-------------------
*	count — Count all elements in an array, or something in an object
*	print_r — Prints human-readable information about a variable
*/
$a = ['sadas', 'asd'];
$b = ['asd', 'asdas', 'sadas'];

function num_match_array ($firstArray, $secondArray){
	$count = 0;
	// loop first array
	foreach ($firstArray as $key => $firstArrValue) {
		// when iteration we compare value of first array with each values to second array
		foreach ($secondArray as $key => $secondArrValue) {
			// if value from first array meets coincidence, variable $count increases
			if ($firstArrValue == $secondArrValue) {
				$count++;
			}
		}
	}

	return $count;
}
/* Usage */
//to display the result, uncomment the line below.
// print_r(num_match_array($a, $b));


/*FN.6--------------------------------------------------------------------------rand_password-------------------
*	strlen — Get string length
*		desc: int strlen ( string $string )
*		return: the length of the given string.
*
*	rand — Generate a random integer
*		desc: int rand ( int $min , int $max )
*		return: A pseudo random value between min (or 0) and max (or getrandmax(), inclusive)
*
*	strtoupper — Make a string uppercase
*		desc: string strtoupper ( string $string )
*		return: Returns the uppercased string.
*
*	str_split Convert a string to an array
*		desc: array str_split ( string $string [, int $split_length = 1 ] )
*		return: array with all characters of string 
*
*	implode — Join array elements with a string
*		desc: string implode ( array $pieces )
*		return :  a string containing a string representation of all the array elements in the same order, with the glue string between each * 				element 
*
*	print_r — Prints human-readable information about a variable
*/
function rand_password($length){
	$result = '';

	$pass = []; //remember to declare $pass as an array
	$symbols = '!@#$%^&*()?';
	$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ'. $symbols;

	//below we generate random password and will save in array $pass
	$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
	for ($i = 0; $i < $length - 1; $i++) {
		$n = rand(0, $alphaLength);
		$pass[] = $alphabet[$n];
	}

	/*add in password at least one capital letter*/
	//make random character to capital letter
	$randPosPass = rand(0, $length - 2);
	$pass[$randPosPass] = strtoupper($pass[$randPosPass]);

	/*add in password at least one symbol*/
	$arrSymbols = str_split($symbols);
	$randPosSymbol = rand(0, strlen($symbols) - 1);
	$pass[] = $arrSymbols[$randPosSymbol];

	$passGenerated = implode($pass);

	return $passGenerated;
}
/* Usage */
//to display the result, uncomment the line below.
// print rand_password(10);


/*FN.7--------------------------------------------------------------------------clean_text-------------------
	preg_split — Split string by a regular expression
		desc: array preg_split ( string $pattern , string $subject [, int $limit = -1 [, int $flags = 0 ]] )
		return: Returns an array containing substrings of subject split along boundaries matched by pattern, or FALSE on failure

	str_replace — Replace all occurrences of the search string with the replacement string
		desc: mixed str_replace ( mixed $search , mixed $replace , mixed $subject [, int &$count ] )

	implode — Join array elements with a string
		desc: string implode ( array $pieces )
		return :  a string containing a string representation of all the array elements in the same order, with the glue string between each element 

	print_r — Prints human-readable information about a variable
*/

$text = 'daSHs as/ ]A~';
function clean_text($string){
	$result = '';

	//remove spaces
	$noSpaces = str_replace(' ', '', $string);

	//remove capital letters
	$noCapLet = preg_split('/[A-Z]/',$noSpaces);

	// join array elements with a string
	$result = implode($noCapLet);
	return $result;
}
/* Usage */
//to display the result, uncomment the line below.
// print_r( clean_text($text));


/*FN.8--------------------------------------------------------------------------times_table-------------------*/
// function not return anything
function times_table($upper):void{
	// counter for <tr> tag
	$i = 1;

	// start of table 
	if (1 % 2 == 1) {
		echo '<table><tr class="odd_row">';
	}

	do {
		// counter for <td> tag
		$j = 1;
		do {
			// every first tag <td> will be have class odd_col
			if ($j % 2 == 1) {
				echo '<td class="odd_col">' . ($i*$j) . "</td>";
			}
			// every second tag <td> will be have class even_col
			else {
				echo '<td class="even_col">' . ($i*$j) . "</td>";
			}
			$j++;
		} while ($j <= $upper);

		if($i <= $upper){
			if ($i % 2 == 1) {
				echo '</tr><tr class="even_col">';
			}else {
				echo '</tr><tr class="odd_row">';
			}
		}

		$i++;

	} while ($i <= $upper);

	echo"</tr></table>";

}
/* Usage */
//to display the result, uncomment the line below.
// times_table(20);


/*FN.9--------------------------------------------------------------------------rgb_to_cmyk-------------------
*	max — Find highest value
*	round — Rounds a float
*	(double) - Float (floating point numbers - also called double)
*	print_r — Prints human-readable information about a variable
*/
class CMYK
{
	public $C;
	public $M;
	public $Y;
	public $K;
}

function rgb_to_cmyk($r, $g, $b) {
	$cmyk = new CMYK();

	$dr = (double)$r / 255;
	$dg = (double)$g / 255;
	$db = (double)$b / 255;
	$cmyk->K = (1 - max(max($dr, $dg), $db));
	$cmyk->C = (1 - $dr - $cmyk->K) / (1 - $cmyk->K);
	$cmyk->M = (1 - $dg - $cmyk->K) / (1 - $cmyk->K);
	$cmyk->Y = (1 - $db - $cmyk->K) / (1 - $cmyk->K);

	$cmyk->C = round($cmyk->C * 100);
	$cmyk->M = round($cmyk->M * 100);
	$cmyk->Y = round($cmyk->Y * 100);
	$cmyk->K = round($cmyk->K * 100);

	return $cmyk;
}
/* Usage */
$r = 66; $g = 134; $b = 244;
// print_r (rgb_to_cmyk($r, $g, $b));



/*FN.10--------------------------------------------------------------------------anagram-------------------
*	count_chars — Return information about characters used in a string
*		desc: mixed count_chars ( string $string [, int $mode = 0 ] )
*		return: Counts the number of occurrences of every byte-value (0..255) in string and returns it in various ways.
*			0 - an array with the byte-value as key and the frequency of every byte as value.
*			1 - same as 0 but only byte-values with a frequency greater than zero are listed.
*			2 - same as 0 but only byte-values with a frequency equal to zero are listed.
*			3 - a string containing all unique characters is returned.
*			4 - a string containing all not used characters is returned.
*
*	var_dump — Dumps information about a variable
*/
function anagram($a, $b){
	// compare byte-value and the frequency of every bytein both arrays
	// if they is  equal return true
	if (count_chars($a, 1) == count_chars($b, 1)){
		return true;
	}
	// else return false
	return false;
}
/* Usage */
//to display the result, uncomment the four line below.

// print '<pre>';
// var_dump(anagram('anagram','nagaram'));
// var_dump(anagram('cat','rat'));
// print '</pre>';

