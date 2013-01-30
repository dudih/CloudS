<?php

class Payment{
	public $id;
	public $userid;
	public $amount;

	public function __construct($paymentid, $userid, $amount) {
		 $this->id = $paymentid;
		 $this->userid = $userid;
		 $this->amount = intval($amount);
	}

}
?>