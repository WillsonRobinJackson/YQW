<?php

require_once("Config.php");
class SmsApi {
	
	/**
	 * 发送短信
	 *
	 * @param string $mobile 手机号码
	 * @param string $msg 短信内容
	 * @param string $product 产品id
	 */
	public function sendSMS( $mobile, $msg, $product = '') {
		global $config;
		//接口参数
		/* $postArr = array (
				          'account' => $config['api_account'],
				          'pwd' => $config['api_password'],
				          'msg' => $msg,
				          'mobile' => $mobile,
				          'productId' => $config['api_productId'],
                     ); */
		$postArr = array (
		    'account' => 'HB20160418094236',
		    'pwd' => 'rcfu!1232',
		    'msg' => $msg,
		    'mobile' => $mobile,
		    'productId' => '3074255',
		);
		
		$result = $this->curlPost( 'http://111.47.110.68:80/api/SmSendServer' , $postArr);
		return $result;
	}

	/**
	 * 处理返回值
	 * 
	 */
	public function execResult($result){
		$result=preg_split("/[,\r\n]/",$result);
		return $result;
	}

	/**
	 * 通过CURL发送HTTP请求
	 * @param string $url  //请求URL
	 * @param array $postFields //请求参数 
	 * @return mixed
	 */
	private function curlPost($url,$postFields){
		$postFields = http_build_query($postFields);
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_POST, 1 );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $postFields );
		$result = curl_exec ( $ch );
		curl_close ( $ch );
		return $result;
	}
	
	//获取
	public function __get($name){
		return $this->$name;
	}
	
	//设置
	public function __set($name,$value){
		$this->$name=$value;
	}




}
?>