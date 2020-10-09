<?php

if (!class_exists('ParsPal_API_Helper'))
{
	class ParsPal_API_Helper
	{
		private $apiKey = '';
		private $apiUrl = 'https://api.parspal.com/v1/payment/';

		public function __construct($apiKey)
		{
			$this->apiKey = trim($apiKey);
		}
        public function setApiUrl($apiUrl)
		{
		    if(substr($apiUrl, -1) != '/')
            {
                $apiUrl = $apiUrl.'/';
            }
			$this->apiUrl = trim($apiUrl);
		}
		public function setSandbox()
		{		    
			$this->apiUrl = str_replace('//api.','//sandbox.api.',$this->apiUrl);
		}
		public function paymentRequest($data)
		{
			$ch = curl_init($u = $this->apiUrl . 'request');
			curl_setopt($ch, CURLOPT_HTTPHEADER, $h = array('APIKEY: '.$this->apiKey, 'Content-Type: application/json'));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $j = json_encode($p = array(
					'amount'      => $data['amount'],
					'return_url'  => $data['return_url'],
					'description' => (isset($data['description'])?$data['description']:null),
   					'currency' => (isset($data['currency'])?$data['currency']:null),
					'reserve_id'  => (isset($data['reserve_id'])?$data['reserve_id']:null),
					'order_id'    => (isset($data['order_id'])?$data['order_id']:null),
					'payer'       => (isset($data['payer'])?$data['payer']:null)
				)));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			$response = curl_exec($ch);
			$error = curl_error($ch);
			$info = curl_getinfo($ch);
			curl_close($ch);
			$result = json_decode($response);

			if ($error) {
				$this->error = 'خطا در اتصال به پرداخت پارس پال : ' . $error;
			} elseif (!is_object($result)) {
				$this->error = 'خطا در ایجاد و ثبت پرداخت پارس پال : ' . json_last_error();
			} elseif (!isset($result->status)) {
				$this->error = 'خطا در اتصال به پرداخت پارس پال';
				if (isset($result->message)) $this->error .= ' (' . $result->message . ')';
			} elseif ($result->status != 'ACCEPTED') {
			    $this->status     = $result->status;
				$this->error = 'خطا در ثبت درخواست پرداخت پارس پال . وضعیت خطا : ' . $result->status . ' (' . $result->message . ')';
			} else {
			    $this->status     = $result->status;
				$this->paymentUrl = $result->link;
				return $result->payment_id;
			}
			return false;
		}

		public function redirect($auto_connect = false)
		{
			if ($auto_connect === true) @header('location: '.$this->paymentUrl);
			echo ('<form name="frmParsPalPayment" method="get" action="'.$this->paymentUrl.'"><input type="submit" value="پرداخت" /></form>');
			if ($auto_connect === true) echo ('<script>document.frmParsPalPayment.submit();</script>');
		}

		public function paymentVerify($receipt_number, $amount , $currency = null)
		{
            $ch = curl_init($u = $this->apiUrl . 'verify');
            //curl_setopt($ch, CURLOPT_HTTPHEADER, $h = array('Content-Type: application/json', 'Content-Length: ' . strlen($j)));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $h = array('APIKEY: '.$this->apiKey, 'Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $j = json_encode($p = array(
            		'amount'         => $amount,
            		'receipt_number' => $receipt_number,
   					'currency' => (isset($currency)?$currency:null),
            	)));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            $error = curl_error($ch);
            $info = curl_getinfo($ch);
            curl_close($ch);
            $result = json_decode($response);
            if ($error) {
            	$this->error = 'خطا در اتصال به پارس پال : ' . $error;
            } elseif (!is_object($result)) {
            	$this->error = 'خطا در دریافت رسید پرداخت پارس پال : ' . json_last_error();
            } elseif (!isset($result->status)) {
            	$this->error = 'خطا در اتصال به پرداخت پارس پال';
            	if (isset($result->Message)) $this->error .= ' (' . $result->Message . ')';
            } elseif ($result->status == 'SUCCESSFUL') {
                $this->receipt_number = $receipt_number;
            	$this->amount         = $result->paid_amount;
            	$this->payment_id     = $result->id;
               	$this->status     = $result->status;
            	return true;
            } else {
                $this->status     = $result->status;
                $this->error = 'خطا در تایید رسید پرداخت پارس پال . وضعیت خطا : ' . $result->status . ' (' . $result->message . ')';
            }
			return false;
		}

        public function checkStatusCode($status)
		{
			if ($status == '100') {
               return true;
            }else{
                $this->error = $this->getStatusByCode($status);
            }
            return false;
		}
		public function getStatusByCode($code)
		{
			if ($code == '77') return 'لغو پرداخت توسط کاربر';
			if ($code == '88') return 'پرداخت ناموفق';
			if ($code == '99') return ' انصراف کاربر از پرداخت';
			if ($code == '100') return 'کاربر عملیات پرداخت را انجام داده است';
			return 'وضعیت غیر منتظره! کد : ' . $code;
		}

	}
}

?>