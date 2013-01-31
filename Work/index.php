<?php
class_exists('User') || require('user.php');
class_exists('Payment') || require('payment.php');
class_exists('Prefix') || require('prefix.php');

$usersArray = array();
$namesArray = array();
$topUsersArray = array();
$prCounter = 0;
$doublePrCounter = 0;

function cmp($a, $b) {
	if ($a->amount == $b->amount)
	return 0;
	return ($a->amount < $b->amount) ? 1 : -1;
}

function handleUR($lineArray){
	if($lineArray[0] == "UR" && count($lineArray)>3){
		if( isset($GLOBALS['namesArray'][$lineArray[2]]) ){
			$GLOBALS['namesArray'][$lineArray[2]]++;
		}else{
			$GLOBALS['namesArray'][$lineArray[2]] = 1;
		}

		if( isset($GLOBALS['usersArray'][$lineArray[1]]) ){
			$GLOBALS['usersArray'][$lineArray[1]]->fname = $lineArray[2];
			$GLOBALS['usersArray'][$lineArray[1]]->lname = $lineArray[3];
		}else{
			$newUser = User:: withUR($lineArray[1], $lineArray[2], $lineArray[3]);
			$GLOBALS['usersArray'][$lineArray[1]] = $newUser;
		}
	}
}

function handlePR($lineArray){
	if($lineArray[0] == "PR" && count($lineArray)>3){
		$GLOBALS['prCounter']++;
		if( isset($GLOBALS['usersArray'][$lineArray[2]]) ){
			$prefixPRid = $lineArray[1][0] . $lineArray[1][1];
			if( isset($GLOBALS['usersArray'][$lineArray[2]]->prefixArray[$prefixPRid]) ){
				if( isset($GLOBALS['usersArray'][$lineArray[2]]->prefixArray[$prefixPRid][$lineArray[1]]) ){
					$GLOBALS['doublePrCounter']++;
				}else{
					$payment = new Payment($lineArray[1], $lineArray[2], $lineArray[3]);
					$GLOBALS['usersArray'][$lineArray[2]]->prefixArray[$prefixPRid][$lineArray[1]] = $payment;
					$GLOBALS['usersArray'][$lineArray[2]]->amount += intval($lineArray[3]);
				}
			}
			else{
				$payment = new Payment($lineArray[1], $lineArray[2], $lineArray[3]);
				$GLOBALS['usersArray'][$lineArray[2]]->prefixArray[$prefixPRid][$lineArray[1]] = $payment;
				$GLOBALS['usersArray'][$lineArray[2]]->amount += intval($lineArray[3]);
			}
		}
		else{
			$newUser = User:: withPR($lineArray[2], $lineArray[1], $lineArray[3]);
			$GLOBALS['usersArray'][$lineArray[2]] = $newUser;
		}
	}
}

function handleLine($line){
	if(!is_string ($line) || strlen($line)<3 )
	return;

	$line = trim($line);
	$prefixLine = $line[0] . $line[1] . $line[2];
	$prefixLine = strtoupper( $prefixLine );
	$lineArray = explode(",", $line);

	switch($prefixLine){
		case "UR,":
			handleUR($lineArray);
			break;
		case "PR,":
			handlePR($lineArray);
			break;
		default:
	}
}

$filesnames = glob("../Challenge/*_*_*.log");
$firstFilename = reset($filesnames);
$prefixFilename = $firstFilename[21].$firstFilename[22];
foreach ($filesnames as $filename) {
	$file = fopen($filename, "r") or exit("Unable to open file!");

	if($prefixFilename != ($filename[21].$filename[22])){
		foreach ($usersArray as $key => $value) {
			unset($value->prefixArray[$prefixFilename]);
		}
		$prefixFilename = ($filename[21].$filename[22]);
	}

	//Output a line of the file until the end is reached
	while(!feof($file))
	{
		$line = fgets($file);
		handleLine($line);
	}
	fclose($file);
}

printf("</br>Payments Ratio = %.3f (%.0f / %.0f)", (1000*($doublePrCounter/$prCounter)), $doublePrCounter, $prCounter);
echo '</br>';

arsort($namesArray);
$min_value = end($namesArray);
$max_value = reset($namesArray);
foreach ($namesArray as $key => $value) {
	if($max_value == $value)
	echo '</br>Most common name(s): ' . $key . ': ' . $value;
}
echo '</br>';
foreach ($namesArray as $key => $value) {
	if($min_value == $value)
	echo '</br>Least common name(s): ' . $key . ': ' . $value;
}
echo '</br>';
echo '</br>Top paying users:';
usort($GLOBALS['usersArray'], 'cmp');
$i=0;
foreach ($GLOBALS['usersArray'] as $key => $value) {
	printf("</br>%s %s :%.0f", $value->fname, $value->lname, $value->amount);

	$i++;
	if($i==10)
	break;
}

?>