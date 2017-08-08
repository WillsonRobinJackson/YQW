<?php
namespace Mobile\Controller;
use Mobile\Controller\MobileController;
class CompanyController extends MobileController{
    public function _initialize(){
        parent::_initialize();
        //访问者控制
        if(I('get.code','','trim') && !in_array(ACTION_NAME,array('order_detail'))){
            $reg = $this->get_weixin_openid(I('get.code','','trim'));
            $reg && $this->redirect('members/apilogin_binding');
        }
        if (!$this->visitor->is_login) {
            IS_AJAX && $this->ajaxReturn(0, L('login_please'),'',1);
            //非ajax的跳转页面
            $this->redirect('members/login');
        }
        if(!IS_AJAX){
            $this->_global_variable();
        }else{
            $this->_cominfo_flge();
        }
    }
    protected function _global_variable() {
        C('visitor.utype') !=1 && $this->redirect('members/login');
        // 顾问信息
        if(C('visitor.consultant')>0){
            $consultant = M('Consultant')->where(array('id'=>C('visitor.consultant')))->find();
            $this->assign('consultant',$consultant);
        }
        // 帐号状态 为暂停
        if (C('visitor.status') == 2 && !in_array(ACTION_NAME, array('index'))){
            $this->_404('您的账号处于暂停状态，请联系管理员设为正常后进行操作！',U('Company/index'));
        }
        // 短信必须验证
        if (C('qscms_sms_open')==1 && C('qscms_login_com_audit_mobile')==1 && C('visitor.mobile_audit') == 0 && !in_array(ACTION_NAME, array('com_security_tel'))){
            $this->_404('您的手机未认证，认证后才能进行其他操作！',U('Company/com_security_tel'));
        }
        $this->_cominfo_flge();
        // 强制认证营业执照
        if (C('qscms_login_com_audit_certificate')==1 && $this->company_profile['audit'] !=1 && !in_array(ACTION_NAME, array('com_auth'))){
            $this->_404('您的营业执照未认证，认证后才能进行其他操作！',U('Company/com_auth'));
        }
        $this->assign('company_profile',$this->company_profile);
        $this->assign('cominfo_flge',$this->cominfo_flge);
        // 第一次登录
        if(!S('personal_login_first_'.C('visitor.uid'))){
            S('personal_login_first_'.C('visitor.uid'),1,86400-(time()-strtotime("today")));
            //快到期提醒
            $my_setmeal = D('MembersSetmeal')->get_user_setmeal(C('visitor.uid'));
            if($my_setmeal['endtime']>0){
                if(C('qscms_meal_min_remind')==0){
                    $confirm_setmeal = 0;
                }else{
                    if($my_setmeal['endtime'] - time()>C('qscms_meal_min_remind')){
                        $confirm_setmeal = 0;
                    }else{
                        $confirm_setmeal = 1;
                    }
                }
                $this->assign('confirm_setmeal',$confirm_setmeal);
            }
        }
        $this->assign('company_nav',ACTION_NAME);
    }
    protected function _cominfo_flge(){
        //当前用户的企业信息 
        $this->company_profile = M('CompanyProfile')->where(array('uid'=>C('visitor.uid')))->find();
        if($this->company_profile)
        {
            // 判断是否需要完善信息
            $this->cominfo_flge=true;
            $array=array("companyname","nature","trade","district","scale","address","contact","email","contents");
            foreach ($this->company_profile as $key => $value){
                if(in_array($key,$array) && empty($value))
                {
                    $this->cominfo_flge=false;
                    break;
                }
            }
        }
        else
        {   
            $this->cominfo_flge=false;
        }
        $this->assign('cominfo_flge',$this->cominfo_flge);
    }
    /**
     * 检测当前发布职位数是否足够
     */
    public function check_jobs_num(){
        $upper_limit = 0;
        $setmeal = D('MembersSetmeal')->get_user_setmeal(C('visitor.uid'));
        $jids = M('Jobs')->where(array('uid'=>C('visitor.uid')))->getField('id',true);
        $jids_tmp = M('JobsTmp')->where(array('uid'=>C('visitor.uid'),'display'=>1))->getField('id',true);
        if(count($jids)+count($jids_tmp)>=$setmeal['jobs_meanwhile']) $upper_limit = 1;
        $this->ajaxReturn($upper_limit,'',$setmeal['is_free']);
    }
    public function index(){
        $setmeal = D('MembersSetmeal')->get_user_setmeal(C('visitor.uid'));
        $is_apply = M('PersonalJobsApply')->where(array('company_uid'=>C('visitor.uid'),'personal_look'=>1))->find();
        $upper_limit = 0;
        $jids = M('Jobs')->where(array('uid'=>C('visitor.uid')))->getField('id',true);
        $jids_tmp = M('JobsTmp')->where(array('uid'=>C('visitor.uid'),'display'=>1))->getField('id',true);
        if(count($jids)+count($jids_tmp)>=$setmeal['jobs_meanwhile']) $upper_limit = 1;
        $this->assign('is_apply',$is_apply ? 1 : 0);
        $this->assign('setmeal',$setmeal);
        $this->assign('upper_limit',$upper_limit);
        $this->assign('points',D('MembersPoints')->get_user_points(C('visitor.uid')));//当前用户积分数
        $this->_config_seo(array('title'=>'企业会员中心 - '.C('qscms_site_name'),'header_title'=>'企业会员中心'));
        $this->display();
    }
	public function com(){
        $this->_config_seo(array('title'=>'企业管理 - 企业会员中心 - '.C('qscms_site_name'),'header_title'=>'企业管理'));
        $this->display();
    }
    public function com_info(){
        $company_profile = $this->company_profile;
        if(!IS_POST){
            //对座机进行分隔
            $telarray = explode('-',$company_profile['landline_tel']);
            if(intval($telarray[0]) > 0)
            {
                $company_profile['landline_tel_first'] = $telarray[0];
            }
            if(intval($telarray[1]) > 0)
            {
                $company_profile['landline_tel_next'] = $telarray[1];
            }
            if(intval($telarray[2]) > 0)
            {
                $company_profile['landline_tel_last'] = $telarray[2];
            }
            $comtag = $this->company_profile['tag']?explode(",",$this->company_profile['tag']):array();
            $tagArr = array('id'=>array(),'cn'=>array());
            if(!empty($comtag)){
                foreach ($comtag as $key => $value) {
                    $arr = explode("|",$value);
                    $tagArr['id'][] = $arr[0];
                    $tagArr['cn'][] = $arr[1];
                }
            }
            $tagStr = array('id'=>'','cn'=>'');
            if(!empty($tagArr['id']) && !empty($tagArr['cn'])){
                $tagStr['id'] = implode(",",$tagArr['id']);
                $tagStr['cn'] = implode(",",$tagArr['cn']);
            }
            $this->assign('tagStr',$tagStr);
            /* 分类*/
            $category = D('Category')->get_category_cache();
            $tag_arr = array_chunk($category['QS_jobtag'],12,true);
            $company_profile['landline_tel'] = ltrim($company_profile['landline_tel'],'-');
            $this->assign('tag_arr',$tag_arr);
            $this->assign('category',$category);
            $this->assign('company_profile',$company_profile);
            $this->_config_seo(array('title'=>'企业资料 - 企业会员中心 - '.C('qscms_site_name'),'header_title'=>'企业资料'));
            $this->display();
        }else{//保存企业信息
            $setsqlarr['id']=I('post.id',0,'intval');
            $setsqlarr['uid']=C('visitor.uid');
            $setsqlarr['companyname']=$company_profile['audit']==1?$company_profile['companyname']:I('post.companyname',0,'trim,badword');
            // 判断企业名称是否重复
            if (C('qscms_company_repeat')=="0")
            {
                $info = M('CompanyProfile')->where(array('uid'=>array('neq',C('visitor.uid')),'companyname'=>$setsqlarr['companyname']))->getField('uid');
                if($info) $this->ajaxReturn(0,"{$setsqlarr['companyname']}已经存在，同公司信息不能重复注册");
            }

            $data = array('nature','trade','scale');
            foreach ($data as $val) {
                $setsqlarr[$val] = I('post.'.$val,0,'intval');
            }
            $setsqlarr['districtcategory'] = I('post.districtcategory','','trim');
            // 分类缓存
            $category = D('Category')->get_category_cache();
            $city = D('CategoryDistrict')->get_district_cache('all');
            $setsqlarr['nature_cn']=$category['QS_company_type'][$setsqlarr['nature']];
            $setsqlarr['trade_cn']=$category['QS_trade'][$setsqlarr['trade']];
            $district_arr = explode(".", $setsqlarr['districtcategory']);
            $setsqlarr['district'] = intval($district_arr[0]);
            $setsqlarr['sdistrict'] = intval($district_arr[1]);
            $setsqlarr['tdistrict'] = intval($district_arr[2]);
            $setsqlarr['district_cn']= $city[0][$setsqlarr['district']];
            $setsqlarr['sdistrict'] && $setsqlarr['district_cn'].= '/'.$city[$setsqlarr['district']][$setsqlarr['sdistrict']];
            $setsqlarr['tdistrict'] && $setsqlarr['district_cn'].= '/'.$city[$setsqlarr['sdistrict']][$setsqlarr['tdistrict']];
            // $setsqlarr['street_cn']=$category['QS_street'][$setsqlarr['street']];
            $setsqlarr['scale_cn']=$category['QS_scale'][$setsqlarr['scale']];
            // 字符串字段
            $setsqlarr['companyname']=I('post.companyname','','trim,badword'); 
            $setsqlarr['registered']=I('post.registered','','trim,badword'); 
            $setsqlarr['currency']=I('post.currency','','trim,badword'); 
            $setsqlarr['address']=I('post.address','','trim,badword'); 
            $setsqlarr['contact']=I('post.contact','','trim,badword');
            $setsqlarr['telephone'] = C('visitor.mobile_audit') ? C('visitor.mobile') : I('post.telephone','','trim,badword');
            $setsqlarr['email'] = C('visitor.email_audit') ? C('visitor.email') : I('post.email','','trim,badword');
            $setsqlarr['website']=I('post.website','','trim,badword');
            $setsqlarr['contents']=I('post.contents','','trim,badword');
            $setsqlarr['contact_show']=I('post.contact_show',1,'intval');
            $setsqlarr['telephone_show']=I('post.telephone_show',1,'intval');
            $setsqlarr['landline_tel_show']=I('post.landline_tel_show',1,'intval');
            $setsqlarr['email_show']=I('post.email_show',1,'intval');
            $setsqlarr['qq']=I('post.qq',0,'intval');
            $setsqlarr['map_x']=I('post.map_x',0,'trim,badword');
            !$setsqlarr['map_x'] && $setsqlarr['map_x'] = 0;
            $setsqlarr['map_y']=I('post.map_y',0,'trim,badword');
            !$setsqlarr['map_y'] && $setsqlarr['map_y'] = 0;
            $setsqlarr['map_zoom']=I('post.map_zoom',0,'intval');
            
            //座机
            $landline_tel_first=I('post.landline_tel_first',0,'trim,badword');
            $landline_tel_next=I('post.landline_tel_next',0,'trim,badword');
            $landline_tel_last=I('post.landline_tel_last',0,'trim,badword');
            $setsqlarr['landline_tel']=$landline_tel_first.'-'.$landline_tel_next.($landline_tel_last?('-'.$landline_tel_last):'');
            $posttag = I('post.tag','','trim,badword');

            if($posttag){
                $tagArr = explode(",",$posttag);
                $r_arr = array();
                foreach ($tagArr as $key => $value) {
                    $r_arr[] = $value.'|'.$category['QS_jobtag'][$value];
                }
                if(!empty($r_arr)){
                    $setsqlarr['tag'] = implode(",",$r_arr);
                }else{
                    $setsqlarr['tag'] = '';
                }
            }

            if($company_profile['contents']) 
            {
                $setsqlarr['id']=$company_profile['id'];
                C('qscms_audit_edit_com')<>"-1"?$setsqlarr['audit']=C('qscms_audit_edit_com'):$setsqlarr['audit']=$company_profile['audit'];
            }
            else
            {
                $setsqlarr['audit']=0;
            }
            $setsqlarr['sync'] = I('post.sync',0,'intval');
            // 插入数据
            $rst = D('CompanyProfile')->add_company_profile($setsqlarr,C('visitor'));
            $rst['state']==0 && $this->ajaxReturn(0,$rst['error']);
            $r = D('TaskLog')->do_task(C('visitor'),27);
            if($setsqlarr['map_x'] && $setsqlarr['map_y'] && $setsqlarr['map_zoom']){
                D('TaskLog')->do_task(C('visitor'),29);
            }
            if($setsqlarr['audit']!=$company_profile['audit']){
                switch($setsqlarr['audit']){
                    case 0:
                        $audit_str = '未认证';break;
                    case 1:
                        $audit_str = '认证通过';break;
                    case 2:
                        $audit_str = '认证中';break;
                    case 3:
                        $audit_str = '认证未通过';break;
                    default:
                        $audit_str = '';break;
                }
                if($audit_str){
                    $auditsqlarr['company_id']=$company_profile['id'];
                    $auditsqlarr['reason']='自动设置';
                    $auditsqlarr['status']=$audit_str;
                    $auditsqlarr['addtime']=time();
                    $auditsqlarr['audit_man']='系统';
                    M('AuditReason')->data($auditsqlarr)->add();
                }
            }
            
            if($rst['id']) {$success ="添加成功！";}else{$success ="保存成功！";}
            $this->ajaxReturn(1,$success,$r['points']);
        }
    }
    public function com_img(){
        $company_img = M('CompanyImg')->where(array('uid'=>C('visitor.uid')))->select();
        $this->assign('company_img',$company_img);
        $this->_config_seo(array('title'=>'企业风采 - 企业会员中心 - '.C('qscms_site_name'),'header_title'=>'企业风采'));
        $this->display();
    }
    /**
     * 企业认证
     */
    public function com_auth(){
        !$this->cominfo_flge && $this->_404('为了达到更好的招聘效果，请先完善您的企业资料！',U('company/com_info'));
        if(IS_POST && $this->company_profile['audit']!=1 && $this->company_profile['audit']!=2){
            $data['companyname_full'] = I('post.companyname','','trim,badword');
            $data['license'] = I('post.license','','trim,badword');
            $data['legal_person'] = I('post.legal_person','','trim,badword');
            $data['certificate_img'] = I('post.certificate_img_up','','trim,badword');
            $data['audit'] = 2;
            $rst = D('CompanyProfile')->add_certificate_img($data,C('visitor'));
            !$rst['state'] && $this->_404($rst['error']);
            $this->assign('company_profile',array_merge($this->company_profile,$data));
        }
        $this->_config_seo(array('title'=>'企业认证 - 企业会员中心 - '.C('qscms_site_name'),'header_title'=>'企业认证'));
        $this->display();
    }
    public function com_binding(){
        $uid=C('visitor.uid');
        $user_bind = M('MembersBind')->where(array('uid'=>$uid))->limit('10')->getfield('type,keyid,info');
        foreach($user_bind as $key=>$val){
            $user_bind[$key] = unserialize($val['info']);
        }
        if(false === $oauth_list = F('oauth_list')){
            $oauth_list = D('Oauth')->oauth_cache();
        }
        $this->assign('user_bind',$user_bind);
        $this->assign('oauth_list',$oauth_list);
        $this->_config_seo(array('title'=>'账号绑定 - 企业会员中心 - '.C('qscms_site_name'),'header_title'=>'账号绑定'));
        $this->display();
    }
    public function com_msg(){
        $this->_config_seo(array('title'=>'消息提醒 - 企业会员中心 - '.C('qscms_site_name'),'header_title'=>'消息提醒'));
        $this->display();
    }
    public function com_info_introduction(){
        $this->display();
    }
    public function com_info_tel(){
        $this->display();
    }
    public function com_info_adder(){
        $this->display();
    }
    public function com_security(){
        $this->_config_seo(array('title'=>'帐号安全 - 企业会员中心 - '.C('qscms_site_name'),'header_title'=>'帐号安全'));
        $this->display();
    }
    public function com_security_tel(){
        $this->_config_seo(array('title'=>'手机认证 - 企业会员中心 - '.C('qscms_site_name'),'header_title'=>'手机认证'));
        $this->display();
    }
    public function com_security_email(){
        $this->_config_seo(array('title'=>'邮箱认证 - 企业会员中心 - '.C('qscms_site_name'),'header_title'=>'邮箱认证'));
        $this->display();
    }
    public function com_security_user(){
        $this->_config_seo(array('title'=>'修改用户名 - 企业会员中心 - '.C('qscms_site_name'),'header_title'=>'修改用户名'));
        $this->display();
    }
    public function com_security_pwd(){
        $this->_config_seo(array('title'=>'修改密码 - 企业会员中心 - '.C('qscms_site_name'),'header_title'=>'修改密码'));
        $this->display();
    }
    public function com_security_log(){
        $where = array('log_uid'=>C('visitor.uid'),'log_type'=>1001);
        $loginlog = D('MembersLog')->get_members_log($where,15);
        if($loginlog['list']){
            foreach ($loginlog['list'] as $key => $val) {
                $t = strtotime(date('Y-m-d',$val['log_addtime']));
                $list[$t][] = $val;
            }
            $loginlog['list'] = $list;
        }
        $this->assign('loginlog',$loginlog);
        $this->_config_seo(array('title'=>'会员登录日志 - 企业会员中心 - '.C('qscms_site_name'),'header_title'=>'会员登录日志'));
        $this->display();
    }
    public function com_msg_consult(){
        $this->display();
    }
    public function com_msg_sys(){
        $this->display();
    }
    public function jobs_add(){
        if(!$this->cominfo_flge){
            if(IS_AJAX){
                $this->ajaxReturn(0,'为了达到更好的招聘效果，请先完善您的企业资料！');
            }else{
                $this->_404('为了达到更好的招聘效果，请先完善您的企业资料！',U('company/com_info'));
            }
        }
        //对座机进行分隔
        $telarray = explode('-',$this->company_profile['landline_tel']);
        $setmeal=D('MembersSetmeal')->get_user_setmeal(C('visitor.uid'));
        // 统计有效职位数
        $jobs_num = D('Jobs')->where(array('uid'=>C('visitor.uid')))->count();
        $jobs_num_tmp = D('JobsTmp')->where(array('uid'=>C('visitor.uid'),'display'=>1))->count();
        if($jobs_num+$jobs_num_tmp>=$setmeal['jobs_meanwhile'] && I('request.id',0,'intval')==0){
            if(IS_AJAX){
                $this->ajaxReturn(0,'当前显示的职位已经达到最大限制，请升级服务套餐!');
            }else{
                $this->_404('当前显示的职位已经达到最大限制，请升级服务套餐!',U('CompanyService/index'));
            }
        }else{
            IS_AJAX && IS_GET && $this->ajaxReturn(1);
        }
        if(IS_POST && IS_AJAX){
            // 保存 POST 数据
            // 插入职位信息
            $jobs_info = '';
            if($id = I('post.id',0,'intval')){
                $setsqlarr['id'] = $id;
                $jobs_info = D('Jobs')->find($id);
                if(!$jobs_info){
                    $jobs_info = D('JobsTmp')->find($id);
                }
                $name = 'edit_jobs';
            }else{
                $name = 'add_jobs';
            }
            $setsqlarr['setmeal_deadline']=$setmeal['endtime'];
            $setsqlarr['deadline']=$setsqlarr['setmeal_deadline'];
            $setsqlarr['setmeal_id']=$setmeal['setmeal_id'];
            $setsqlarr['setmeal_name']=$setmeal['setmeal_name'];
            $setsqlarr['uid']=C('visitor.uid');
            $setsqlarr['company_id']=$this->company_profile['id'];
            $setsqlarr['company_addtime']=$this->company_profile['addtime'];
            $setsqlarr['company_audit']=$this->company_profile['audit'];
            $setsqlarr['famous']=$this->company_profile['famous'];
            if(!$id){
                $setsqlarr['audit']= 2;
            }else{
                if($this->company_profile['audit']==1){
                    $setsqlarr['audit'] = C('qscms_audit_verifycom_editjob')=='-1'?$jobs_info['audit']:C('qscms_audit_verifycom_editjob');
                }else{
                    $setsqlarr['audit'] = C('qscms_audit_unexaminedcom_editjob')=='-1'?$jobs_info['audit']:C('qscms_audit_unexaminedcom_editjob');
                }
            }
            
            $array = array('companyname','trade','trade_cn','scale','scale_cn','tpl','map_x','map_y','map_zoom');
            if(I('post.basis_contact',0,'intval')){//与企业联系方式同步
                $array = array_merge($array,array('contact','telephone','landline_tel','address','email','contact_show','email_show','telephone_show','landline_tel_show'));
            }else{
                $setsqlarr['contact']=I('post.contact','','trim,badword');
                $setsqlarr['telephone']=I('post.telephone','','trim,badword');
                $setsqlarr['landline_tel']=I('post.landline_tel','','trim,badword');
                $setsqlarr['address']=I('post.address','','trim,badword');
                $setsqlarr['email']=I('post.email','','trim,badword');
                $setsqlarr['contact_show']=I('post.contact_show',1,'intval');
                $setsqlarr['email_show']=I('post.email_show',1,'intval');
                $setsqlarr['telephone_show']=I('post.telephone_show',1,'intval');
                $setsqlarr['landline_tel_show']=I('post.landline_tel_show',1,'intval');
            }
            foreach($array as $val){
                $setsqlarr[$val] = $this->company_profile[$val];
            }
            $array = array('nature','topclass','category','subclass','amount','district','sdistrict','tdistrict','minwage','maxwage','negotiable','sex','education','experience','graduate','minage','maxage','is_entrust','notify','notify_mobile');
            foreach ($array as $val) {
                $setsqlarr[$val] = I('post.'.$val,0,'intval');
            }
            $jobcategory = I('post.jobcategory');
            $jobcategory_arr = explode(".", $jobcategory);
            $setsqlarr['topclass']= $jobcategory_arr[0];
            $setsqlarr['category']= $jobcategory_arr[1];
            $setsqlarr['subclass']= $jobcategory_arr[2];
            $districtcategory = I('post.districtcategory');
            $districtcategory_arr = explode(".", $districtcategory);
            $setsqlarr['district']= $districtcategory_arr[0];
            $setsqlarr['sdistrict']= $districtcategory_arr[1];
            $setsqlarr['tdistrict']= $districtcategory_arr[2];
            $setsqlarr['jobs_name']= I('post.jobs_name','','trim,badword');
            $setsqlarr['tag']=I('post.tag','','trim,badword');// 标签
            $setsqlarr['contents']=I('post.contents','','trim,badword');
            $setsqlarr['department'] = I('post.department','','trim,badword');
            
            $rst = D('Jobs')->$name($setsqlarr,C('visitor'));
            if($rst['state']==0){
                if(IS_AJAX){
                    $this->ajaxReturn(0,$rst['error']);
                }else{
                    $this->_404($rst['error']);
                }
            }
            if($jobs_info){
                if($setsqlarr['audit']!=$jobs_info['audit']){
                    switch($setsqlarr['audit']){
                        case 1:
                            $audit_str = '审核通过';break;
                        case 2:
                            $audit_str = '审核中';break;
                        case 3:
                            $audit_str = '审核未通过';break;
                        default:
                            $audit_str = '';break;
                    }
                    if($audit_str){
                        $auditsqlarr['jobs_id']=$jobs_info['id'];
                        $auditsqlarr['reason']='自动设置';
                        $auditsqlarr['status']=$audit_str;
                        $auditsqlarr['addtime']=time();
                        $auditsqlarr['audit_man']='系统';
                        M('AuditReason')->data($auditsqlarr)->add();
                    }
                }
            }else{
                switch($setsqlarr['audit']){
                    case 1:
                        $audit_str = '审核通过';break;
                    case 2:
                        $audit_str = '';break;
                    case 3:
                        $audit_str = '审核未通过';break;
                    default:
                        $audit_str = '';break;
                }
                if($audit_str){
                    $auditsqlarr['jobs_id']=$rst['id'];
                    $auditsqlarr['reason']='自动设置';
                    $auditsqlarr['status']=$audit_str;
                    $auditsqlarr['addtime']=time();
                    $auditsqlarr['audit_man']='系统';
                    M('AuditReason')->data($auditsqlarr)->add();
                }
            }
            baidu_submiturl(url_rewrite('QS_jobsshow',array('id'=>$rst['id'])),'addjob');
            if(IS_AJAX){
                $this->ajaxReturn(1,$id?'修改成功！':'添加成功！',U('company/jobs_list'));
            }else{
                $this->redirect('company/jobs_list');
            }
        }else{
            $comtag = $this->company_profile['tag']?explode(",",$this->company_profile['tag']):array();
            $tagArr = array('id'=>array(),'cn'=>array());
            if(!empty($comtag)){
                foreach ($comtag as $key => $value) {
                    $arr = explode("|",$value);
                    $tagArr['id'][] = $arr[0];
                    $tagArr['cn'][] = $arr[1];
                }
            }
            $tagStr = array('id'=>'','cn'=>'');
            if(!empty($tagArr['id']) && !empty($tagArr['cn'])){
                $tagStr['id'] = implode(",",$tagArr['id']);
                $tagStr['cn'] = implode(",",$tagArr['cn']);
            }
            $this->assign('tagStr',$tagStr);
            $this->assign('jobs',$jobs);
            /* 分类*/
            $category = D('Category')->get_category_cache();
            $this->assign('category',$category);
            $this->assign('telarray',$telarray);
            $total=$setmeal['jobs_meanwhile'] - $jobs_num - $jobs_num_tmp;
            $this->assign('total',$total);
            $this->assign('setmeal',$setmeal);
            $tag_arr = array_chunk($category['QS_jobtag'],12,true);
            $this->assign('tag_arr',$tag_arr);
            $this->_config_seo(array('title'=>'发布职位 - 企业会员中心 - '.C('qscms_site_name'),'header_title'=>'发布职位'));
            $this->display();
        }
    }
    /**
     * 修改职位
     */
    public function jobs_edit(){
        $id = I('get.id',0,'intval');
        $jobs = D('Jobs')->get_jobs_one(array('id'=>$id,'uid'=>C('visitor.uid')));
        $jobs['age'] = explode('-',$jobs['age']);
        $telarray = explode('-',$jobs['contact']['landline_tel']);
        $category = D('Category')->get_category_cache();
        $this->assign('category',$category);
        $this->assign('jobs',$jobs);
        $this->assign('company_profile',$jobs['contact']);
        $this->assign('telarray',$telarray);
        $tag_arr = array_chunk($category['QS_jobtag'],12,true);
        $this->assign('tag_arr',$tag_arr);
        // 统计有效职位数
        $setmeal=D('MembersSetmeal')->get_user_setmeal(C('visitor.uid'));
        $jobs_num = D('Jobs')->where(array('uid'=>C('visitor.uid')))->count();
        $jobs_num_tmp = D('JobsTmp')->where(array('uid'=>C('visitor.uid'),'display'=>1))->count();
        $total=$setmeal['jobs_meanwhile']-$jobs_num-$jobs_num_tmp;
        $total = $total<0?0:$total;
        $this->assign('total',$total);
        $this->_config_seo(array('title'=>'修改职位 - 企业会员中心 - '.C('qscms_site_name'),'header_title'=>'修改职位'));
        $this->display('jobs_add');
    }
    /**
     * 职位管理
     */
    public function jobs_list(){
        $this->check_params();
        $type = I('get.type',0,'intval');
        switch ($type) {
            case '1':
                $tabletype="jobs";
                break;
            case '2':
                $tabletype = "jobs_tmp";
                break;
            default:
                $tabletype = "all";
                break;
        }
        $jobs_list = D('Jobs')->get_jobs(array('uid'=>C('visitor.uid')),'audit asc, refreshtime desc',$tabletype,10,true,true,true,true);

        if(!APP_SPELL){
            if(false === $city_cate = F('city_cate_list')) $city_cate = D('CategoryDistrict')->city_cate_cache();
            foreach ($jobs_list as $key => $val) {
                $jobs_list[$key]['jobcategory'] = $city_cate[$val['subclass'] || $val['category'] || $val['topclass']];
            }
        }
        $upper_limit = 0;
        $my_setmeal = D('MembersSetmeal')->get_user_setmeal(C('visitor.uid'));
        $jids = M('Jobs')->where(array('uid'=>C('visitor.uid')))->getField('id',true);
        $jids_tmp = M('JobsTmp')->where(array('uid'=>C('visitor.uid'),'display'=>1))->getField('id',true);
        if(count($jids)+count($jids_tmp)>=$my_setmeal['jobs_meanwhile']) $upper_limit = 1;
        $this->assign('jobs_list',$jobs_list);
        $this->assign('total',$total);
        $this->assign('my_setmeal',$my_setmeal);
        $this->assign('promotion',$promotion);
        $this->assign('upper_limit',$upper_limit);
        $this->_config_seo(array('title'=>'管理职位 - 企业会员中心 - '.C('qscms_site_name'),'header_title'=>'管理职位'));
        $this->display();
    }
    public function jobs_display(){
        $yid =  I('request.id');
        if(!$yid){
            $this->_404('请选择职位！');
        }
        $upper_limit = 0;
        $my_setmeal = D('MembersSetmeal')->get_user_setmeal(C('visitor.uid'));
        $jids = M('Jobs')->where(array('uid'=>C('visitor.uid')))->getField('id',true);
        $jids_tmp = M('JobsTmp')->where(array('uid'=>C('visitor.uid'),'display'=>1))->getField('id',true);
        if(count($jids)+count($jids_tmp)>=$my_setmeal['jobs_meanwhile']) $upper_limit = 1;
        if($upper_limit){
            $this->ajaxReturn(2);
        }
        $perform_type = 'display';
        $rst = D('Jobs')->jobs_perform(array('yid'=>$yid,'perform_type'=>$perform_type,'user'=>C('visitor')));
        $return_url = I('request.list_type',0,'intval')==0?U('jobs_list'):U('jobs_list',array('type'=>1));
        if($rst['state']==1)
        {
            $this->ajaxReturn(1,'操作成功！',$return_url);
        }
        else
        {
            $this->ajaxReturn(0,$rst['error'],$return_url);
        }
    }
    public function jobs_close(){
        $yid =  I('request.id');
        if(!$yid){
            $this->_404('请选择职位！');
        }
        $perform_type = 'close';
        $rst = D('Jobs')->jobs_perform(array('yid'=>$yid,'perform_type'=>$perform_type,'user'=>C('visitor')));
        $return_url = I('request.list_type',0,'intval')==0?U('jobs_list'):U('jobs_list',array('type'=>1));
        if($rst['state']==1)
        {
            $this->ajaxReturn(1,'操作成功！',$return_url);
        }
        else
        {
            $this->ajaxReturn(0,$rst['error'],$return_url);
        }
    }
    public function jobs_delete(){
        $yid =  I('request.id');
        if(!$yid){
            $this->_404('请选择职位！');
        }
        $perform_type = 'delete';
        $rst = D('Jobs')->jobs_perform(array('yid'=>$yid,'perform_type'=>$perform_type,'user'=>C('visitor')));
        $return_url = I('request.list_type',0,'intval')==0?U('jobs_list'):U('jobs_list',array('type'=>1));
        if($rst['state']==1){
            $this->ajaxReturn(1,'操作成功！',$return_url);
        }else{
            $this->ajaxReturn(0,$rst['error'],$return_url);
        }
    }
    /**
     * 职位刷新
     */
    public function jobs_refresh(){
        $yid = I('request.yid');
        if(!$yid){
            $this->ajaxReturn(0,'请选择职位！');
        }
        /*$yid = is_array($yid)?$yid:explode(",", $yid);
        $jobs_num =count($yid);
        $user_jobs=D('Jobs')->count_auditjobs_num(C('visitor.uid'));
        if($user_jobs==0){
            $this->ajaxReturn(0,'没有可刷新的职位！');
        }*/
        $r = D('Jobs')->jobs_refresh(array('yid'=>$yid,'user'=>C('visitor')));
        if($r['state']==1){
            $this->ajaxReturn(1,'刷新成功！');
        }else{
            $this->ajaxReturn(0,$r['error']);
        }
    }
    /**
     * 职位刷新确认
     */
    public function jobs_refresh_confirm(){
        $yid = I('request.yid');
        if(!$yid){
            $this->ajaxReturn(0,'请选择职位！');
        }
        $job = D('Jobs')->get_jobs_one(array('id'=>$yid,'uid'=>C('visitor.uid')));
        if($job['audit'] == 3){
            $this->ajaxReturn(0,'审核未通过职位不可刷新！');
        } elseif ($job['audit'] == 2 && C('qscms_jobs_display') == 1){
            $this->ajaxReturn(0,'审核中职位不可刷新！');
        } elseif ($job['display'] == 2 || ($job['deadline']>0 && $job['deadline']<time())){
            $this->ajaxReturn(0,'已关闭职位不可刷新！');
        }
        $my_setmeal = D('MembersSetmeal')->get_user_setmeal(C('visitor.uid'));
        $my_points = D('MembersPoints')->get_user_points(C('visitor.uid'));
        $tip = '';
        $discount = D('Setmeal')->get_max_discount('auto_refresh_jobs');
        $refresh_time = D('RefreshLog')->get_today_refresh_times(array('uid'=>C('visitor.uid'),'type'=>1001,'mode'=>2));
        if($refresh_time>=$my_setmeal['refresh_jobs_free']){//免费刷新次数已到
            if($my_points>=C('qscms_refresh_jobs_price')*C('qscms_payment_rate') && C('qscms_refresh_jobs_by_points')==1){
                $tip = '<div class="dialog_notice">今天免费刷新次数已用完，本次刷新需要扣除 <span class="font_yellow">'.intval(C('qscms_payment_rate')*C('qscms_refresh_jobs_price')).'</span> '.C('qscms_points_byname').'，是否确定刷新？<br /><div class="dialog_tip font10 font_gray9">您当前拥有 <span class="font_yellow">'.$my_points.'</span> '.C('qscms_points_byname').'</div></div>';
                $mode = 'points';
            }else{
                $this->assign('discount',$discount);
                $this->assign('jobs_id',$yid);
                $tip = $this->fetch('Ajax_tpl/guide_pay_refresh_jobs');
                $mode = 'mix';
            }
        }else{
            $tip = '<div class="dialog_notice">你今天还可免费刷新 <span>'.($my_setmeal['refresh_jobs_free']-$refresh_time).'</span> 次，本次刷新免费，是否立即刷新？</div>';
            $mode = 'setmeal';
        }
        $this->ajaxReturn(1,$tip,$mode);
    }
    /**
     * [jobs_refresh_all 批量刷新]
     */
    public function jobs_refresh_all(){
        $user_jobs=D('Jobs')->count_auditjobs_num(C('visitor.uid'));
        if($user_jobs==0){
            $this->ajaxReturn(0,'没有可刷新的职位！');
        }
        $my_setmeal = D('MembersSetmeal')->get_user_setmeal(C('visitor.uid'));
        $refresh_time = D('RefreshLog')->get_today_refresh_times(array('uid'=>C('visitor.uid'),'type'=>1001,'mode'=>2));
        if($refresh_time>=$my_setmeal['refresh_jobs_free']){
            $this->ajaxReturn(2,'您当前共有 <span class="font_yellow">'.$user_jobs.'</span> 条在招职位，今天免费刷新次数已用完。请单条刷新。',1);
        }elseif($user_jobs+$refresh_time>$my_setmeal['refresh_jobs_free']){
            $surplus = $my_setmeal['refresh_jobs_free'] - $refresh_time;
            $this->ajaxReturn(2,'您当前共有 <span class="font_yellow">'.$user_jobs.'</span> 条在招职位，今天免费刷新次数剩余 <span class="font_yellow">'.$surplus.'</span> 次。请单条刷新。',1);
        }else{
            $condition['uid'] = C('visitor.uid');
            C('qscms_jobs_display') == 1 && $condition['audit'] = 1 ;
            $jobsid_arr = D('Jobs')->where($condition)->field('id')->select();
            $yid = array();
            foreach ($jobsid_arr as $key => $value) {
                $yid[] = $value['id'];
            }
            $r = D('Jobs')->jobs_refresh(array('yid'=>$yid,'user'=>C('visitor')));
            $this->ajaxReturn($r['state'],$r['error']);
        }
    }
    /**
     * 收到的简历
     */
    public function jobs_apply(){
        $this->check_params();
        $where['company_uid']=C('visitor.uid');
        $stop =I('get.stop',0,'intval');
        $settr =I('get.settr',0,'intval');
        $settr && $where['apply_addtime']=array('gt',strtotime("-{$settr} day")); //筛选简历
        $jobs_id =I('get.jobs_id',0,'intval');
        $jobs_id && $where['jobs_id']=$jobs_id; //筛选简历
        // 筛选项 -> 标签 (0->全部  1->合适  2->不合适  3->待定  4->未接通)
        $state=I('get.state',0,'intval');
        // 筛选项 -> 来源 (0->全部  1->委托投递  2->主动投递 )
        $is_apply=I('get.is_apply',0,'intval');
        $is_apply && $where['is_apply']=$is_apply; 
        $personal_apply_mod = D('PersonalJobsApply');
        $condition['uid'] = C('visitor.uid');
        (!$stop && C('qscms_jobs_display') == 1) && $condition['audit'] = 1;
        $jobs_list = M('Jobs')->where($condition)->getField('id,jobs_name');
        if($stop){
            $jobs_list_tmp = M('JobsTmp')->where(array('uid'=>C('visitor.uid')))->getField('id,jobs_name');
            if($jobs_list_tmp){
                $jobs_list = $jobs_list?($jobs_list+$jobs_list_tmp):$jobs_list_tmp;
            }
        }
        $jobsid_arr = array();
        foreach ($jobs_list as $key => $value) {
            $jobsid_arr[] = $key;
        }
        if(!empty($jobsid_arr) && !$where['jobs_id']){
            $where['jobs_id'] = array('in',$jobsid_arr);
        }
        $apply_list = $personal_apply_mod->get_apply_jobs($where,1,$state);
        $this->assign('jobs_id',$jobs_id);
        $this->assign('jobs_list',$jobs_list);
        $this->assign('apply_list',$apply_list);
        $this->assign('is_reply',$is_reply);
        $this->assign('state',$state);
        $this->assign('state_arr',$personal_apply_mod->state_arr);
        $this->_config_seo(array('title'=>'收到的简历 - 企业会员中心 - '.C('qscms_site_name'),'header_title'=>'收到的简历'));
        $this->display();
    }
    // 删除
    public function jobs_apply_del(){
        $yid= I('request.y_id');
        !$yid && $this->ajaxReturn(0,"你没有选择项目！");
        $reg=D('PersonalJobsApply')->del_jobs_apply($yid,C('visitor'));
        if($reg['state']==1){
            $this->ajaxReturn(1,"删除成功！");
        }else{
            $this->ajaxReturn(0,"删除失败！");
        }
    }
    /**
     * 面试邀请
     */
    public function jobs_interview(){
        $this->check_params();
        $where['company_uid']= C('visitor.uid');
        $stop =I('get.stop',0,'intval');
        $settr =I('get.settr',0,'intval');
        $settr && $where['interview_addtime']=array('gt',strtotime("-{$settr} day")); //筛选简历
        $look=I('get.look',0,'intval');
        $look>0 && $where['personal_look'] =$look;
        $jobs_id = I('get.jobs_id',0,'intval');
        $condition['uid'] = C('visitor.uid');
        (!$stop && C('qscms_jobs_display') == 1) && $condition['audit'] = 1;
        $jobs_list1 = M('Jobs')->where($condition)->getField('id,jobs_name');
        $jobs_list1 = $jobs_list1?$jobs_list1:array();
        if($stop==1){
            $jobs_list2 = M('JobsTmp')->where(array('uid'=>C('visitor.uid')))->getField('id,jobs_name');
            $jobs_list2 = $jobs_list2?$jobs_list2:array();
            $jobs_list = $jobs_list1 + $jobs_list2;
        }else{
            $jobs_list = $jobs_list1;
        }
        $jobs_id_arr = array();
        foreach ($jobs_list as $key => $value) {
            $jobs_id_arr[] = $key;
        }
        if($jobs_id>0){
            $where['jobs_id'] =$jobs_id;
        }
        else if(!empty($jobs_id_arr))
        {
            $where['jobs_id'] = array('in',$jobs_id_arr);
        }else{
            $where['jobs_id'] = 0;
        }
        $company_interview_mod = D('CompanyInterview');
        $interview = $company_interview_mod->get_invitation_pre($where,1);
        
        $this->assign('jobs_list',$jobs_list);
        $this->assign('interview',$interview);
        $this->assign('jobs_id',$jobs_id);
        $this->_config_seo(array('title'=>'面试邀请 - 企业会员中心 - '.C('qscms_site_name'),'header_title'=>'面试邀请'));
        $this->display();
    }
    // 删除面试邀请
    public function jobs_interview_del(){
        $yid= I('request.y_id','','trim');
        !$yid && $this->ajaxReturn(0,"你没有选择项目！");
        $rst = D('CompanyInterview')->del_interview($yid,C('visitor'));
        if($rst['state']){
            $this->ajaxReturn(1,"删除成功！");
        }else{
            $this->ajaxReturn(0,"删除失败！");
        }
    }
    /**
     * 已下载简历
     */
    public function resume_down(){
        $this->check_params();
        $where['company_uid']=C('visitor.uid');
        $settr=I('get.settr',0,'intval');
        $settr && $where['down_addtime']=array('gt',strtotime("-".$settr." day")); //筛选 下载时间
        $state = I('get.state',''); // 简历标记状态
        $down_list = D('CompanyDownResume')->get_down_resume($where,$state);
        $this->assign('down_list',$down_list);
        $this->assign('state',$state);
        $this->assign('state_arr',D('CompanyDownResume')->state_arr);
        $this->_config_seo(array('title'=>'已下载的简历 - 企业会员中心 - '.C('qscms_site_name'),'header_title'=>'已下载的简历'));
        $this->display();
    }
    /**
     * 收藏的简历
     */
    public function resume_favorites(){
        $this->check_params();
        $where['company_uid']=C('visitor.uid');
        $settr=I('get.settr',0,'intval');
        $education=I('get.education',0,'intval');
        $experience=I('get.experience',0,'intval');
        $education && $where['r.education'] = $education;
        $experience && $where['r.experience'] = $experience;
        $settr && $where['favorites_addtime']=array('gt',strtotime("-".$settr." day")); //筛选 收藏时间
        $favorites = D('CompanyFavorites')->get_favorites($where);
        $category = D('Category')->get_category_cache();
        $this->assign('education',$category['QS_education']);
        $this->assign('experience',$category['QS_experience']);
        $this->assign('favorites',$favorites);
        $this->_config_seo(array('title'=>'已收藏的简历 - 企业会员中心 - '.C('qscms_site_name'),'header_title'=>'已收藏的简历'));
        $this->display();
    }
    /**
     * [resume_favorites_del 删除收藏的简历]
     */
    public function resume_favorites_del(){
        $yid= I('request.y_id');
        !$yid && $this->ajaxReturn(0,"你没有选择项目！");
        $n=D('CompanyFavorites')->del_favorites($yid,C('visitor'));
        if($n['state']==1){
            $this->ajaxReturn(1,"删除成功！");
        }else{
            $this->ajaxReturn(0,"删除失败！");
        }
    }
	/**
     * 意见反馈
     */
	public function feedback(){
        if(IS_POST){
            $data = I('post.');
            $r = D('Feedback')->addeedback($data);
            $this->ajaxReturn($r['state'],$r['msg']);
        }
        if (C('qscms_wap_captcha_config.varify_suggest')==1 && C('qscms_mobile_captcha_open')==1){
            $varify_suggest = 1;
        }else{
            $varify_suggest = 0;
        }
        $this->assign('varify_suggest',$varify_suggest);
        $this->_config_seo(array('title'=>'意见反馈 - 企业会员中心 - '.C('qscms_site_name'),'header_title'=>'意见反馈'));
        $this->display();
	}
    /**
     * 消息提醒
     */
    public function pms_list(){
        if(I('get.type',0,'intval')){
            $msg_list = D('Msg')->msg_list(C('visitor'),false);
            $this->assign('msg_list',$msg_list);
            $this->_config_seo(array('title'=>'求职者咨询 - 企业会员中心 - '.C('qscms_site_name'),'header_title'=>'求职者咨询'));
        }else{
            $settr = I('get.settr',0,'intval');
            $new = I('get.new',0,'intval');
            $map = array();
            if($settr>0){
                $tmp_addtime = strtotime('-'.$settr.' day');
                $map['dateline'] = array('egt',$tmp_addtime);
            }
            if($new>0){
                $map['new'] = array('eq',$new);
            }
            $msg = D('Pms')->update_pms_read(C('visitor'),10,$map);
            $this->assign('msg_list',$msg);
            $this->_config_seo(array('title'=>'消息提醒 - 企业会员中心 - '.C('qscms_site_name'),'header_title'=>'消息提醒'));
        }
        $this->display();
    }
	 /**
     * 消息详细
     */
    public function pms_show(){
        $ids = I('request.id','','trim');
        $reg = D('Pms')->msg_check($ids,C('visitor'));
        if($reg['state']){
            $this->assign('msg',$reg['data']);
        }else{
            $this->_404($reg['error']);
        }
        $this->_config_seo(array('title'=>'系统信息 - 企业会员中心 - '.C('qscms_site_name'),'header_title'=>'系统信息'));
        $this->display();
    }
	 /**
     * 资讯详细
     */
    public function pms_consult_show(){
        $id = I('get.id',0,'intval');
        !$id && $this->_404('请选择求职咨询');
        $msg_list = D('Msg')->smsg_list($id,C('visitor'));
        $this->assign('msg_list',$msg_list);
        $this->_config_seo(array('title'=>'求职者咨询 - 企业会员中心 - '.C('qscms_site_name'),'header_title'=>'求职者咨询'));
        $this->display();
    }
    /**
     * [msg_feedback_send 发送咨询反馈]
     */
    public function pms_feedback_send(){
        if(IS_AJAX){
            $data['pid'] = I('post.pid',0,'intval');
            $data['touid'] = I('post.touid',0,'intval');
            $data['message'] = I('post.message','','trim');
            $reg = D('Msg')->msg_send($data,C('visitor'));
            if($reg['state']) $this->ajaxReturn(1,'消息发送成功！',$reg['data']);
            $this->ajaxReturn(0,$reg['error']);
        }
    }
}
?>