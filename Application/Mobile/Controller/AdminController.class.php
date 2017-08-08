<?php
namespace Mobile\Controller;
use Common\Controller\ConfigbaseController;
class AdminController extends ConfigbaseController{
	public function _initialize() {
        parent::_initialize();
    }
    public function edit(){
        if(IS_POST){
            if($_POST['wap_domain'] && false === strpos(strtolower($_POST['wap_domain']),'http://')) $_POST['wap_domain'] = 'http://'.$_POST['wap_domain'];
            foreach (I('post.') as $key => $val) {
                $val = is_array($val) ? serialize($val) : $val;
                D('Config')->where(array('name' => $key))->save(array('value' => $val));
            }
            if($_POST['wap_domain']){
                $domain = D('Config')->sub_domain();
                $this->update_config(array('APP_SUB_DOMAIN_RULES'=>$domain),CONF_PATH.'sub_domain.php');
            }
            $this->success(L('operation_success'));
        }else{
            $this->display();
        }
    }
}
?>