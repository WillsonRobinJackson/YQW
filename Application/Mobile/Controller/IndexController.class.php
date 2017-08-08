<?php
namespace Mobile\Controller;
use Mobile\Controller\MobileController;
class IndexController extends MobileController{
	// 初始化函数
	public function _initialize(){
		parent::_initialize();
	}
	public function index(){
		if($this->apply['Subsite'] && $district = D('Subsite')->get_subsite_domain()){
			$ipInfos = GetIpLookup();
			foreach ($district as $key => $val) {
				if(strpos($val['s_districtname'],$ipInfos['district'])){
					$temp = $val;
					$district_org = $ipInfos['district'];
					break;
				}
				if(strpos($val['s_districtname'],$ipInfos['city'])){
					$temp = $val;
					$district_org = $ipInfos['city'];
					break;
				}
				if(strpos($val['s_districtname'],$ipInfos['province'])){
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
		$jobs_count = M('Jobs')->count('id');
		$resume_count = M('Resume')->count('id');
		$this->assign('jobs_count',$jobs_count+intval(C('qscms_jobs_base')));
		$this->assign('resume_count',$resume_count+intval(C('qscms_resume_base')));
		$this->display();
	}
	public function compatibility(){
		$this->display();
	}
}
?>