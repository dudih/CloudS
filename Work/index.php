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
	//To Do: update freq of first names
	if($lineArray[0] == "UR" && count($lineArray)>3){	
		if( isset($GLOBALS['namesArray'][$lineArray[2]]) ){
			$GLOBALS['namesArray'][$lineArray[2]]++;
		}else{
			$GLOBALS['namesArray'][$lineArray[2]] = 1;			
		}

		if( isset($GLOBALS['usersArray'][$lineArray[1]]) ){
			$GLOBALS['usersArray'][$lineArray[1]]->fname = $lineArray[2];
			$GLOBALS['usersArray'][$lineArray[1]]->lname = $lineArray[3];
			//echo '</br>before: ' . $GLOBALS['usersArray'][$lineArray[1]];
		}else{
			$newUser = User:: withUR($lineArray[1], $lineArray[2], $lineArray[3]);
			$GLOBALS['usersArray'][$lineArray[1]] = $newUser;
			echo '</br>UR: ' . $GLOBALS['usersArray'][$lineArray[1]]->fname;
		}
	}
}

function handlePR($lineArray){
	if($lineArray[0] == "PR" && count($lineArray)>3){
		$GLOBALS['prCounter']++;
		if( isset($GLOBALS['usersArray'][$lineArray[2]]) ){
			//TO DO: Check if PR exists, and if not insert it and update variables
			$prefixPRid = $lineArray[1][0] . $lineArray[1][1];
			if( isset($GLOBALS['usersArray'][$lineArray[2]]->prefixArray[$prefixPRid]) ){
				if( isset($GLOBALS['usersArray'][$lineArray[2]]->prefixArray[$prefixPRid][$lineArray[1]]) ){
					$GLOBALS['doublePrCounter']++;					
					echo ' doublePrCounter=' .$GLOBALS['doublePrCounter'];					
				}else{
				        echo '</br>YYYYG: ';
						//To Do: insert pr (payment) and update vars.
	        			$payment = new Payment($lineArray[1], $lineArray[2], $lineArray[3]);
						$GLOBALS['usersArray'][$lineArray[2]]->prefixArray[$prefixPRid][$lineArray[1]] = $payment;
				        $GLOBALS['usersArray'][$lineArray[2]]->amount += intval($lineArray[3]);    	
				}
			}	//endif( isset($GLOBALS['usersArray'][$lineArray[1]]->prefixArray[$prefixPRid]) )
			else{
        			$payment = new Payment($lineArray[1], $lineArray[2], $lineArray[3]);
					$GLOBALS['usersArray'][$lineArray[2]]->prefixArray[$prefixPRid][$lineArray[1]] = $payment;
			        $GLOBALS['usersArray'][$lineArray[2]]->amount += intval($lineArray[3]);				
			}
		}	//end if( isset($GLOBALS['usersArray'][$lineArray[1]]) )
		else{
			$newUser = User:: withPR($lineArray[2], $lineArray[1], $lineArray[3]);
			$GLOBALS['usersArray'][$lineArray[2]] = $newUser;
			//To Do: update vars
			echo '</br>PR5: ' . $GLOBALS['usersArray'][$lineArray[2]]->id;			
		}	//end elseif( isset($GLOBALS['usersArray'][$lineArray[1]]) )
	}	//end if($lineArray[0] == "PR" && count($lineArray)>3)
	echo '</br>AMOUNTTT: ' . $GLOBALS['usersArray'][$lineArray[2]]->id . ' amount='. $lineArray[3]. ' TotalAmount ='. $GLOBALS['usersArray'][$lineArray[2]]->amount;
}	//end function handlePR

function handleLine($line){
	if(!is_string ($line) || strlen($line)<3 )
		return;

	$line = trim($line);
	$prefixLine = $line[0] . $line[1] . $line[2];
	$prefixLine = strtoupper( $prefixLine );
	$lineArray = explode(",", $line);
	// $lineArrayCounter = count($lineArray);
	// if($lineArrayCounter>1 && strlen($lineArray[$lineArrayCounter-1])>1){
	// 	echo '</br>'. $lineArray[1] . 'BeforeStr|'. $lineArray[$lineArrayCounter-1] . '|';
	// 	$lineArray[$lineArrayCounter-1] = substr($lineArray[$lineArrayCounter-1], 0, -1); 
	// 	echo 'AfterStr|' . $lineArray[$lineArrayCounter-1] . '|KLKL';
	// }

	switch($prefixLine){
		case "UR,":	
					echo "UR: " . $line . "</br>";
					handleUR($lineArray);
					break;	
		case "PR,":	
					echo "PR: " . $line . "</br>";
					handlePR($lineArray);
					break;
		default:
	}
}

    
    foreach (glob("../Challenge/*.log") as $filename) {
    	echo $filename . " dudi111111222224444</br>";

		$file = fopen($filename, "r") or exit("Unable to open file!");
		//Output a line of the file until the end is reached
		while(!feof($file))
		  {
		  	$line = fgets($file);//. "<br>";		  
		  	handleLine($line);		  	
		  }
		fclose($file);
    }

/*$user1 =  new User("1234");*/
//echo '</br>Payment Records: ' . $prCounter;
//echo '</br>Double Payment Records: ' . $doublePrCounter;
//echo '</br>Payments Ratio = '. (1000*($doublePrCounter/$prCounter)). ' ('. $doublePrCounter. ' / '. $prCounter. ')';
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
	//if(isset($value->fname)){
		printf("</br>%s %s :%.0f", $value->fname, $value->lname, $value->amount);	
	//}
		$i++;
		if($i==10)
			break;
}
/*$user2 =  User::withUR('1235', 'dudi2', 'halabi2');
echo '</br>Dudi2 ' . $user2->id . '  ' . $user2->fname .'  '. $user2->lname;*/

?>