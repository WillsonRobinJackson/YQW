<?php
namespace Mobile\Controller;
use Mobile\Controller\MobileController;
class UploadController extends MobileController{
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
	 * [company_logo 企业logo]
	 */
	public function company_logo(){
		$company_id = I('post.company_id',0,'intval');
		//日期路径
    	$date = date('ym/d/');
    	$save_avatar=C('qscms_attach_path').'company_logo/'.$date;//图片存储路径
    	if(!is_dir($save_avatar)){
	    	mkdir($save_avatar,0777,true);
	    }
	    $filename = md5($company_id).'.jpg';
	    $uid = C('visitor.uid');
		$pic=base64_decode($_POST['base64_string']);
		file_put_contents($save_avatar.$filename,$pic);
		$path = $save_avatar.$filename;
		$rst = M('CompanyProfile')->where(array('id'=>$company_id,'uid'=>C('visitor.uid')))->setfield('logo',$date.$filename);
		$r = D('TaskLog')->do_task(C('visitor'),19);
		// 保存
		$img = $date.$filename;
		$data = array('path'=>attach($img,'company_logo').'?'.time(),'img'=>$img);
		$this->ajaxReturn(1, L('upload_success'), $data);
	}
	public function company_img(){
		$company_id = I('post.company_id',0,'intval');
		$date = date('ym/d/');
    	$save_avatar=C('qscms_attach_path').'company_img/'.$date;//图片存储路径
    	if(!is_dir($save_avatar)){
	    	mkdir($save_avatar,0777,true);
	    }
	    $uid = C('visitor.uid');
    	$savePicName = uniqid().'.jpg';
		$filename = $save_avatar.$savePicName;
		$pic=base64_decode($_POST['base64_string']);
		file_put_contents($filename,$pic);
		$setsqlarr['uid']=C('visitor.uid');
		$setsqlarr['company_id']=$company_id;
		$setsqlarr['img']=$date.$savePicName;
		$rst = D('CompanyImg')->add_company_img($setsqlarr,C('visitor'));
		!$rst['state'] && $this->ajaxReturn(0, $rst['error']);
		$r = D('TaskLog')->do_task(C('visitor'),20);
		$img = $date.$savePicName;
		$data = array('path'=>attach($img,'company_img'),'img'=>$img);
		$this->ajaxReturn(1, L('upload_success'), $data);
	}
	// 企业营业执照 上传
	public function certificate_img(){
		//日期路径
    	$date = date('ym/d/');
    	$save_avatar=C('qscms_attach_path').'certificate_img/'.$date;//图片存储路径
    	if(!is_dir($save_avatar)){
	    	mkdir($save_avatar,0777,true);
	    }
	    $filename = md5(C('visitor.uid')).'.jpg';
	    $uid = C('visitor.uid');
		$pic=base64_decode($_POST['base64_string']);
		file_put_contents($save_avatar.$filename,$pic);
		// 保存
		$img = $date.$filename;
		$data = array('path'=>attach($img,'certificate_img').'?'.time(),'img'=>$img);
		$this->ajaxReturn(1, L('upload_success'), $data);
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
    	$savePicName = md5($uid.time()).'.jpg';
		$filename = $save_avatar.$savePicName;
		$pic=base64_decode($_POST['base64_string']);
		file_put_contents($filename,$pic);
		$image= new \Common\ORG\ThinkImage();
		$size = explode(',',C('qscms_avatar_size'));
		foreach ($size as $val) {
			$image->open($filename)->thumb($val,$val,3)->save($filename."_".$val."x".$val.".jpg");
		}
		$img = $date.$savePicName;
		$setsqlarr['avatars']=$img;
		$old_avatar = D('Members')->where(array('uid'=>$uid))->getfield('avatars');
		$photo = M('MembersInfo')->field('photo_audit,photo_display')->where(array('uid'=>$uid))->find();
		if($photo['photo_display'] == 1){
			$setsqlarr['photo'] = 1;
		}else{
			$setsqlarr['photo'] = 0;
		}
		if(true !== $reg = D('Members')->update_user_info($setsqlarr,C('visitor'))) $this->ajaxReturn(0,$reg);
		$user_resume_list = D('Resume')->where(array('uid'=>$uid))->select();
		foreach ($user_resume_list as $key => $value) {
			D('Resume')->check_resume($uid,$value['id']);//更新简历完成状态
		}
		D('TaskLog')->do_task(C('visitor'),5);
		write_members_log(C('visitor'),2044);
		$data = array('path'=>attach($img,'avatar').'?'.time(),'img'=>$img);
		$this->ajaxReturn(1, L('upload_success'), $data);
	}
	/**
	 * [resume_img 个人简历图片上传]
	 * @return [type] [description]
	 */
	public function resume_img(){
		//日期路径
    	$date = date('ym/d/');
    	$save_avatar=C('qscms_attach_path').'resume_img/'.$date;//图片存储路径
    	if(!is_dir($save_avatar)){
	    	mkdir($save_avatar,0777,true);
	    }
	    $filename = uniqid().'.jpg';
	    $uid = C('visitor.uid');
		$pic=base64_decode($_POST['base64_string']);
		file_put_contents($save_avatar.$filename,$pic);
		$image= new \Common\ORG\ThinkImage();
		$path = $save_avatar.$filename;
		$size = explode(',',C('qscms_resume_img_size'));
		foreach ($size as $val) {
			$image->open($path)->thumb($val,$val,3)->save("{$path}_{$val}x{$val}.jpg");
		}
		// 保存
		$img = $date.$filename;
		$data = array('path'=>attach($img,'resume_img'),'img'=>$img);
		$this->ajaxReturn(1, L('upload_success'), $data);
	}
}
?>