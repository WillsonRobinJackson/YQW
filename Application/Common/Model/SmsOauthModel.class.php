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
// | ModelName: 短信服务商模板对应表模型
// +----------------------------------------------------------------------
namespace Common\Model;
use Think\Model;
class SmsOauthModel extends Model{
	protected $_validate = array(
		array('sid,tid,tpl_id','identicalNull','',1,'callback',1),
		array('tpl_id,id','_tpl_id','{%sms_oauth_null_error_tpl_id}',1,'callback',2),
		array('sid,tid,tpl_id','identicalEnum','',2,'callback',3),
	);
	protected $_auto = array (
	    array('create_time','time',1,'function'),
	    array('update_time','time',3,'function'),
	    array('ordid',255),
	    array('status',1,3)
	);
	protected function _tpl_id($data){
		$sid = $this->where(array('id'=>$data['id']))->getfield('sid');
		if(!$data['tpl_id']){
			if(false === $sms = F('sms_replace')) $sms = D('Sms')->sms_replace_cache();
			if($sms[$sid]['filing']) return false;
		}
		return true;
	}
	/**
	 * [oauth_tpl_cache 短信模板参数缓存生成]
	 * @return [array]
	 */
	public function oauth_tpl_cache($service=''){
		if(!$service) return false;
		$where['status'] = 1;
		if(false === $sms_list = F('sms_list')){
			$sms_list = D('Sms')->sms_cache();
		}
		$where['sid'] = $sms_list[$service]['id'];
		$tplList = $this->where($where)->getfield('alias,tpl_id,value');
		F('oauth_tpl/tpl_'.$service,$tplList);
		return $tplList;
	}
	/**
     * 后台有更新则删除缓存
     */
    protected function _before_write($data, $options) {
        if(false === $sms_list = F('sms_list')){
			$sms_list = D('Sms')->sms_cache();
		}
		foreach ($sms_list as $key => $val) {
			F('oauth_tpl/tpl_'.$key, NULL);
		}
    }
    /**
     * 后台有删除也删除缓存
     */
    protected function _after_delete($data,$options){
        $this->_before_write();
    }
}
?>