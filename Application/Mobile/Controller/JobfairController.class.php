<?php
namespace Mobile\Controller;
use Mobile\Controller\MobileController;
class JobfairController extends MobileController{
    // 初始化函数
    public function _initialize()
    {
        parent::_initialize();
        if(I('get.code','','trim')){
            $reg = $this->get_weixin_openid(I('get.code','','trim'));
            $reg && $this->redirect('members/apilogin_binding');
        }
    }

    /**
     * 招聘会首页
     */
    public function index()
    {
        $this->display();
    }

    /**
     * 招聘会详情
     */
    public function show()
    {
        $this->display();
    }

    /**
     * 参会企业
     */
    public function comlist()
    {
        $this->display();
    }
}

?>