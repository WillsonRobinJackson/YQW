<?php
namespace Mobile\Controller;
use Mobile\Controller\MobileController;
class jobsController extends MobileController
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
        $this->assign('search_type','jobs');
        $this->display();
    }

    /**
     * 公司详情
     */
    public function comshow()
    {
        $this->display();
    }

    /**
     * 职位详情
     */
    public function show()
    {
        $this->display();
    }
}

?>
        
        
        