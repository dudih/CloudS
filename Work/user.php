<?php
class_exists('Payment') || require('payment.php');

class User{
	public $id;
	public $fname;
	public $lname;
	public $amount;
	public $prefixArray;

    public function __construct($userid) {
        $this->id = $userid;
		$prefixArray = array();
    }

    public static function withUR($userid, $fname, $lname) {
    	$instance = new self($userid);
    	$instance->fname = $fname;
        $instance->lname = $lname;
        $instance->amount = 0;
        return $instance;
    }

    public static function withPR($userid, $paymentid, $amount) {
        $instance = new self($userid);
        $instance->amount = intval($amount);
        $paymentPrefix = $paymentid[0] . $paymentid[1];
        $payment = new Payment($paymentid, $userid, $amount);
    	$instance->prefixArray[$paymentPrefix][$paymentid] = $payment;
    	return $instance;    	
    }

}
?>