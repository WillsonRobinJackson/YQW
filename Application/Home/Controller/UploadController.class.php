<?php
namespace Home\Controller;
use Common\Controller\FrontendController;
class UploadController extends FrontendController{
	public function _initialize() {
		parent::_initialize();
		//访问者控制
		if (!$this->visitor->is_login) {
			IS_AJAX && $this->ajaxReturn(0, L('login_please'),'',1);
			//非ajax的跳转页面
			$this->redirect('members/login');
		}
	}
	/**
	 * [attach 附件上传]
	 * @return [type] [description]
	 */
	public function attach(){
		if(IS_POST){
			$type = I('post.type','image','trim');
			if(!in_array($type,array('resume_img','word_resume','company_logo','company_img','certificate_img'))) return false;
			if (!empty($_FILES[$type]['name'])) {
				$this->$type();
			}else{
				$this->ajaxReturn(0, L('illegal_parameters'));
			}
		}
	}
	/**
	 * [resume_img 个人简历图片上传]
	 * @return [type] [description]
	 */
	protected function resume_img(){
		$date = date('ym/d/');
		$result = $this->_upload($_FILES['resume_img'], 'resume_img/' . $date, array(
				'maxSize' => C('qscms_resume_photo_max'),//图片大小上限
				'uploadReplace' => true,
				'attach_exts' => 'bmp,png,gif,jpeg,jpg'
		));
		if ($result['error']) {
			$pid = I('post.pid',0,'intval');
			$image = new \Common\ORG\ThinkImage();
			$path = $result['info'][0]['savepath'].$result['info'][0]['savename'];
			$size = explode(',',C('qscms_resume_img_size'));
			foreach ($size as $val) {
				$image->open($path)->thumb($val,$val,3)->save("{$path}_{$val}x{$val}.jpg");
			}
			// 保存
			$img = $date.$result['info'][0]['savename'];
			$img_mod = M('ResumeImg');
	        $setsqlarr['resume_id'] = $pid;
	        $setsqlarr['uid'] = C('visitor.uid');
	        $setsqlarr['title'] = '';
	        $setsqlarr['img'] = $img;
        	$setsqlarr['id'] = I('post.id',0,'intval');
	        if($setsqlarr['id']==0){
	        	$count = M('ResumeImg')->where(array('resume_id'=>$pid,'uid'=>C('visitor.uid')))->count('id');
            	if($count >= 6){
            		$this->ajaxReturn(0,'简历附件最多只可上传6张！');exit;
            	}
	        }
	        $rst = D('ResumeImg')->save_resume_img($setsqlarr);
			$data = array('path'=>attach($img,'resume_img'),'img'=>$img,'id'=>$rst['id']);
			$this->ajaxReturn(1, L('upload_success'), $data,'','HTML');
		} else {
			$this->ajaxReturn(0, $result['info']);
		}
	}
	/**
	 * [word_resume 上传word简历]
	 * @return [type] [description]
	 */
	protected function word_resume(){
		$date = date('ym/d/');
		$result = $this->_upload($_FILES['word_resume'], 'word_resume/' . $date, array(
				'maxSize' => 2*1024,//word最大2M
				'uploadReplace' => true,
				'attach_exts' => 'doc,docx'
		));
		if ($result['error']) {
			$pid = I('post.pid',0,'intval');
			$path = $result['info'][0]['savepath'].$result['info'][0]['savename'];
			$resume_mod = M('Resume');
			$where = array('id'=>$pid,'uid'=>C('visitor.uid'));
			if(false === $word = $resume_mod->where($where)->getfield('word_resume')) $this->ajaxReturn(0,'简历不存在或已经删除！');
			$save_arr['word_resume'] = $date.$result['info'][0]['savename'];
			$save_arr['word_resume_title'] = badword($_FILES['word_resume']['name']);
			$save_arr['word_resume_addtime'] = time();
			$rid = $resume_mod->where($where)->save($save_arr);
			@unlink(C('qscms_attach_path')."word_resume/".$word);
			$this->ajaxReturn(1, L('upload_success'),array('name'=>$save_arr['word_resume_title'],'path'=>attach($date.$result['info'][0]['savename'],'word_resume'),'time'=>date('Y-m-d H:i',time())),'','HTML');
		} else {
			$this->ajaxReturn(0, $result['info']);
		}
	}
	/**
	 * [company_logo 企业logo]
	 */
	protected function company_logo(){
		$company_id = I('post.company_id',0,'intval');
		$date = date('ym/d/');
		$result = $this->_upload($_FILES['company_logo'], 'company_logo/' . $date, array(
				'maxSize' => C('qscms_logo_max_size'),
				'uploadReplace' => true,
				'attach_exts' => 'bmp,png,gif,jpeg,jpg'
		),md5($company_id));
		if ($result['error']) {
			$image = new \Common\ORG\ThinkImage();
			$path = $result['info'][0]['savepath'].$result['info'][0]['savename'];
			$rst = M('CompanyProfile')->where(array('id'=>$company_id,'uid'=>C('visitor.uid')))->setfield('logo',$date.$result['info'][0]['savename']);
			$r = D('TaskLog')->do_task(C('visitor'),19);
			$data = array('path'=>attach($date.$result['info'][0]['savename'],'company_logo').'?'.time(),'points'=>$r['data']);
			$this->ajaxReturn(1, L('upload_success'), $data,'','HTML');
		} else {
			$this->ajaxReturn(0, $result['info']);
		}
	}
	protected function company_img(){
		$company_id = I('post.company_id',0,'intval');
		$date = date('ym/d/');
		$num = M('CompanyImg')->where(array('company_id'=>$company_id,'uid'=>C('visitor.uid')))->count();
		if($num>=8) $this->ajaxReturn(0, '企业风采不能超过8张！');
		$result = $this->_upload($_FILES['company_img'], 'company_img/' . $date, array(
				'maxSize' => C('qscms_company_img_max'),
				'uploadReplace' => true,
				'attach_exts' => 'bmp,png,gif,jpeg,jpg'
		));
		if ($result['error']) {
			$image = new \Common\ORG\ThinkImage();
			$path = $result['info'][0]['savepath'].$result['info'][0]['savename'];
			// 保存
			$setsqlarr['uid']=C('visitor.uid');
			$setsqlarr['company_id']=$company_id;
			$setsqlarr['img']=$date.$result['info'][0]['savename'];
			$rst = D('CompanyImg')->add_company_img($setsqlarr,C('visitor'));
			!$rst['state'] && $this->ajaxReturn(0, $rst['error']);
			$r = D('TaskLog')->do_task(C('visitor'),20);
			$data = array('path'=>attach($setsqlarr['img'],'company_img'),'date'=>$rst['date'],'id'=>$rst['id'],'deleteUrl'=>U('company/del_company_img',array('id'=>$rst['id'])),'remarkUrl'=>U('company/set_company_img_title',array('id'=>$rst['id'])),'points'=>$r['data']);
			$this->ajaxReturn(1, L('upload_success'), $data,'','HTML');
		} else {
			$this->ajaxReturn(0, $result['info']);
		}
	}
	// 企业营业执照 上传
	protected function certificate_img(){
		$date = date('ym/d/');
		$result = $this->_upload($_FILES['certificate_img'], 'certificate_img/' . $date, array(
				'maxSize' => C('qscms_certificate_max_size'),
				'uploadReplace' => true,
				'attach_exts' => 'bmp,png,gif,jpeg,jpg'
		),md5(C('visitor.uid')));
		if ($result['error']) {
			$image = new \Common\ORG\ThinkImage();
			// 保存
			$data['img'] = $date.$result['info'][0]['savename'];
			$data['url'] = attach($data['img'],'certificate_img');
			$this->ajaxReturn(1, L('upload_success'),$data,'','HTML');
		} else {
			$this->ajaxReturn(0, $result['info']);
		}
	}
	/**
	 * [avatar 头像上传保存]
	 */
	public function avatar(){
    	//日期路径
    	$date = date('ym/d/');
    	$save_avatar=C('qscms_attach_path').'avatar/'.$date;//图片存储路径
    	if(!is_dir($save_avatar)){
	    	mkdir($save_avatar,0777,true);
	    }
	    $uid = C('visitor.uid');
    	$savePicName = md5($uid.time());
		$filename = $save_avatar.$savePicName.".jpg";
		$pic=base64_decode($_POST['pic1']);
		file_put_contents($filename,$pic);
		$image= new \Common\ORG\ThinkImage();
		$size = explode(',',C('qscms_avatar_size'));
		foreach ($size as $val) {
			$image->open($filename)->thumb($val,$val,3)->save($filename."_".$val."x".$val.".jpg");
		}
		$setsqlarr['avatars']=$date.$savePicName.".jpg";
		$setsqlarr['photo'] = 0;
		$setsqlarr['photo_audit'] = 2;
		if(true !== $reg = D('Members')->update_user_info($setsqlarr,C('visitor'))) $this->ajaxReturn(0,$reg);
		$user_resume_list = D('Resume')->where(array('uid'=>$uid))->select();
		foreach ($user_resume_list as $key => $value) {
			D('Resume')->check_resume($uid,$value['id']);//更新简历完成状态
		}
		D('TaskLog')->do_task(C('visitor'),5);
		write_members_log(C('visitor'),2044);
		$rs['status'] = 1;
		$rs['picUrl'] = $savePicName.".jpg";
		print json_encode($rs);
	}
}
?>