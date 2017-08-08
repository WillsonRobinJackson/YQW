<?php
namespace Home\Controller;
use Common\Controller\FrontendController;
class IndexController extends FrontendController{
	public function _initialize() {
        parent::_initialize();
    }
	/**
	 * [index 首页]
	 */
	public function index(){
		if(!I('get.org','','trim') && C('PLATFORM') == 'mobile' && $this->apply['Mobile']){
            redirect(build_mobile_url());
		}
		if($this->apply['Subsite'] && $district = D('Subsite')->get_subsite_domain()){
			$ipInfos = GetIpLookup();
			foreach ($district as $key => $val) {
				if($ipInfos['district'] && ($val['s_districtname'] == $ipInfos['district'] || strpos($val['s_districtname'],$ipInfos['district']))){
					$temp = $val;
					$district_org = $ipInfos['district'];
					break;
				}
				if($ipInfos['city'] && ($val['s_districtname'] == $ipInfos['city'] || strpos($val['s_districtname'],$ipInfos['city']))){
					$temp = $val;
					$district_org = $ipInfos['city'];
					break;
				}
				if($ipInfos['province'] && ($val['s_districtname'] == $ipInfos['province'] || strpos($val['s_districtname'],$ipInfos['province']))){
					$temp = $val;
					$district_org = $ipInfos['province'];
					break;
				}
			}
			if(!cookie('_subsite_domain') && C('SUBSITE_VAL.s_id') != $temp['s_id']){
				unset($district[$temp['s_id']]);
				$this->assign('subsite_org',$temp);
				$this->assign('district_org',$district_org);
				$domain = C('PLATFORM')=='mobile' && C('SUBSITE_VAL.s_m_domain') ? C('SUBSITE_VAL.s_m_domain') : C('SUBSITE_VAL.s_domain');
				cookie('_subsite_domain','http://'.$domain);
			}
			unset($district[C('SUBSITE_VAL.s_id')]);
			$this->assign('district',$district);
		}
		if(false === $oauth_list = F('oauth_list')){
            $oauth_list = D('Oauth')->oauth_cache();
        }
        $this->assign('verify_userlogin',$this->check_captcha_open(C('qscms_captcha_config.user_login'),'error_login_count'));
		$this->assign('oauth_list',$oauth_list);
		$this->display();
	}
	/**
	 * [ajax_user_info ajax获取用户登录信息]
	 */
	public function ajax_user_info(){
		if(IS_AJAX){
			!$this->visitor->is_login && $this->ajaxReturn(0,'请登录！');
			$uid = C('visitor.uid');
			if(C('visitor.utype') == 1){
				$info = M('CompanyProfile')->field('companyname,logo')->where(array('uid'=>$uid))->find();
				$views = M('ViewJobs')->where(array('jobs_uid'=>C('visitor.uid')))->group('uid')->getfield('uid',true);
				$info['views'] = count($views);
				$join = 'join '.C('DB_PREFIX') .'jobs j on j.id='.C('DB_PREFIX').'personal_jobs_apply.jobs_id';
				$info['apply'] = M('PersonalJobsApply')->join($join)->where(array('company_uid'=>$uid,'is_reply'=>array('eq',0)))->count();
			}else{
				$info['realname'] = M('MembersInfo')->where(array('uid'=>$uid))->getfield('realname');
				$info['pid'] = M('Resume')->where(array('uid'=>$uid,'def'=>1))->getfield('id');
				$info['countinterview'] = M('CompanyInterview')->where(array('resume_uid'=>$uid))->count();
				//谁看过我
				$rids = M('Resume')->where(array('uid'=>$uid))->getField('id',true);
				if($rids){
					$info['views'] = M('ViewResume')->where(array('resumeid'=>array('in',$rids)))->count();
				}else{
					$info['views'] = 0;
				}
			}
			$issign = D('MembersHandsel')->check_members_handsel_day(array('uid'=>$uid,'htype'=>'task_sign'));
        	$this->assign('issign',$issign ? 1 : 0);
			$this->assign('info',$info);
			$hour=date('G');
			if($hour<11){
				$am_pm = '早上好';
	        }
	        else if($hour<13)
	        {
	        	$am_pm = '中午好';
	        }
	        else if($hour<17)
	        {
	        	$am_pm = '下午好';
	        }
	        else
	        {
	        	$am_pm = '晚上好';
	        }
	        $this->assign('am_pm',$am_pm);
			$data['html'] = $this->fetch('ajax_user_info');
        	$this->ajaxReturn(1,'',$data);
		}
	}
	/**
	 * [index 首页搜索跳转]
	 */
	public function search_location(){
		$act = I('get.act','','trim');
		$key = I('get.key','','trim');
		$this->ajaxReturn(1,'',url_rewrite($act,array('key'=>$key)));
	}
	/**
	 * 保存到桌面
	 */
	public function shortcut(){
		$Shortcut = "[InternetShortcut]
		URL=".C('qscms_site_domain').C('qscms_site_dir')."?lnk
		IDList= 
		IconFile=".C('qscms_site_domain').C('qscms_site_dir')."favicon.ico
		IconIndex=100
		[{000214A0-0000-0000-C000-000000000046}]
		Prop3=19,2";
		header("Content-type: application/octet-stream"); 
		$ua = $_SERVER["HTTP_USER_AGENT"];
		$filename=C('qscms_site_name').'.url';
		$filename = urlencode($filename);
		$filename = str_replace("+", "%20", $filename);
		if (preg_match("/MSIE/", $ua)) {
		    header('Content-Disposition: attachment; filename="' . $filename . '"');
		} else if (preg_match("/Firefox/", $ua)) {
		    header('Content-Disposition: attachment; filename*="utf8\'\'' . $filename . '"');
		} else {
		    header('Content-Disposition: attachment; filename="' . $filename . '"');
		}
		exit($Shortcut);
	}
}
?>