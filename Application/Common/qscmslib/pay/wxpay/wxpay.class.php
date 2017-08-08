<?php
// +----------------------------------------------------------------------
// | 74CMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://www.74cms.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 
// +----------------------------------------------------------------------
// | ModelName: 微信支付类
// +----------------------------------------------------------------------
require_once dirname(__FILE__) . '/WxPay.Data.php';
require_once dirname(__FILE__) . '/WxPay.NativePay.php';
require_once dirname(__FILE__) . '/WxPay.JsApiPay.php';
class wxpay_pay{
	protected $_error = 0;
	public function __construct($data) {
		WxPayConfig::$appid = C('qscms_weixin_appid');
		WxPayConfig::$mchid = $data['partnerid'];
		WxPayConfig::$key = $data['ytauthkey'];
		WxPayConfig::$appsecret = C('qscms_weixin_appsecret');
	}
	/**
	 * [微信支付订单二维码生成]
	 * @param  string $type   [description]
	 * @param  [type] $option [description]
	 * @return [type]         [description]
	 */
	public function dopay($option){
		$return = '';
		switch($option['pay_from']){
			case 'pc':
				$return = $this->_pay_from_pc($option);
				break;
			case 'wap':
				$return = $this->_pay_from_wap($option);
				break;
		}
		return $return;
	}
	/**
	 * 网页版支付
	 */
	protected function _pay_from_pc($option){
		$wxpay = new WxPayUnifiedOrder();
		$notify = new NativePay();
		$wxpay->SetBody($option['ordbody']);//描述
		$wxpay->SetAttach("test");//回调附加参数
		$wxpay->SetOut_trade_no($option['oid']);//商户订单号
		$wxpay->SetTotal_fee($option['ordtotal_fee']*100);//支付金额
		$wxpay->SetTime_start(date("YmdHis"));//交易起始时间
		$wxpay->SetTime_expire(date("YmdHis", time() + 600));//交易结束时间
		$wxpay->SetGoods_tag($option['ordsubject']);//商品标记
		$wxpay->SetNotify_url($option['site_dir'].'Home/Callback/wxpay');//支付通知回调地址
		$wxpay->SetTrade_type("NATIVE");//交易类型
		$wxpay->SetProduct_id("123456789");
		$result = $notify->GetPayUrl($wxpay);
        return $result["code_url"];
	}
	/**
	 * 触屏支付
	 */
	protected function _pay_from_wap($option){
		$wxpay = new WxPayUnifiedOrder();
		$notify = new JsApiPay();
		$openId = $notify->GetOpenid();
		$wxpay->SetBody($option['ordbody']);//描述
		$wxpay->SetAttach("test");//回调附加参数
		$wxpay->SetOut_trade_no($option['oid']);//商户订单号
		$wxpay->SetTotal_fee($option['ordtotal_fee']*100);//支付金额
		$wxpay->SetTime_start(date("YmdHis"));//交易起始时间
		$wxpay->SetTime_expire(date("YmdHis", time() + 600));//交易结束时间
		$wxpay->SetGoods_tag($option['ordsubject']);//商品标记
		$wxpay->SetNotify_url($option['site_dir'].'Home/Callback/wxpay');//支付通知回调地址
		$wxpay->SetTrade_type("JSAPI");//交易类型
		$wxpay->SetOpenid($openId);//用户标识
		$order = WxPayApi::unifiedOrder($wxpay);//创建统一支付表单信息
		$jsApiParameters = $notify->GetJsApiParameters($order);
		//获取共享收货地址js函数参数
		$editAddress = $notify->GetEditAddressParameters();
        return array('jsApiParameters'=>$jsApiParameters,'editAddress'=>$editAddress);
	}
    public function getError(){
    	return $this->_error;
    }
}
?>