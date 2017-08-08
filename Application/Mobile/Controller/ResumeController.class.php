<?php
namespace Mobile\Controller;

use Mobile\Controller\MobileController;

class ResumeController extends MobileController
{
    // 初始化函数
    public function _initialize()
    {
        parent::_initialize();
        if(I('get.code','','trim')){
            $reg = $this->get_weixin_openid(I('get.code','','trim'));
            $reg && $this->redirect('members/apilogin_binding');
        }
    }

    public function index()
    {
        $this->display();
    }
    /**
     * 简历详情
     */
    public function show()
    {
        $this->display();
    }
}

?>