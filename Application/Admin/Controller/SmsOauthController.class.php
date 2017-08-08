<?php
namespace Admin\Controller;
use Common\Controller\ConfigbaseController;
class SmsOauthController extends ConfigbaseController{
	public function _initialize() {
        parent::_initialize();
        $this->_mod = D('SmsOauth');
    }
    public function index(){
    	$id = I('get.id',0,'intval');
    	!$id && $this->error('请先择正确的服务商！');
    	$db_pre = C('DB_PREFIX');
    	$oauth = $db_pre.'sms_oauth';
    	$list = $this->_mod->field($oauth.'.id,sid,tpl_id,'.$oauth.'.update_time,'.$oauth.'.status,t.id as tid,t.name,t.alias')->join('right join '.$db_pre.'sms_templates t ON t.id='.$oauth.'.tid and '.$oauth.'.sid='.$id)->order('t.id')->select();
    	$this->assign('list',$list);
    	$this->assign('sid',$id);
    	$this->display();
    }
    public function _after_select($data){
    	if(false === $tpl_list = F('sms_tpl')){
    		$tpl_list = D('SmsTemplates')->sms_tpl_cache();
    	}
    	if(false === $sms = F('sms_replace')){
    		$sms = D('Sms')->sms_replace_cache();
    	}
    	$data['name'] = $tpl_list[$data['tid']]['name'];
    	$data['alias'] = $tpl_list[$data['tid']]['alias'];
    	$data['value'] = $tpl_list[$data['tid']]['value'];
    	$sms[$data['sid']]['replace'] && $data['value'] = preg_replace('/\{(.*?)\}/i',$sms[$data['sid']]['replace'],$data['value']);
    	return $data;
    }
    public function _before_add(){
    	if(!IS_POST){
	    	$id = I('get.id',0,'intval');
	    	$sid = I('get.sid',0,'intval');
	    	!$id && $this->error('请选择正确的模板！');
	    	!$sid && $this->error('请选择正确的短信服务商！');
	    	if(false === $tpl_list = F('sms_tpl')){
	    		$tpl_list = D('SmsTemplates')->sms_tpl_cache();
	    	}
	    	if(false === $sms = F('sms_replace')){
	    		$sms = D('Sms')->sms_replace_cache();
	    	}
	    	$info = $tpl_list[$id];
	    	$sms[$sid]['replace'] && $info['value'] = preg_replace('/\{(.*?)\}/i',$sms[$sid]['replace'],$info['value']);
	    	if($oauth = $this->_mod->field('id,tid,sid,tpl_id,alias,value')->where(array('sid'=>$sid,'tid'=>$id))->find()){
	    		$info = array_merge($info,$oauth);
	    	}else{
	    		unset($info['id']);
	    		$info['tid'] = $id;
	    		$info['sid'] = $sid;
	    	}
	    	$this->assign('info',$info);
	    }
    }
}
?>