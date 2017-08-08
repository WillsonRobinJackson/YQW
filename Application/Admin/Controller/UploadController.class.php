<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class UploadController extends BackendController{
	public function _initialize() {
		parent::_initialize();
	}
	/**
	 * [ajaxReturn description]
	 * @param  integer $status [0:正确]
	 * @param  string  $msg    [description]
	 * @return [type]          [description]
	 */
	protected function ajaxReturn($status=1, $msg='', $url='', $dialog=''){
		// 返回JSON数据格式到客户端 包含状态信息
        $data = array(
            'error' => $status,
            'message' => $msg,
            'url' => $url
        );
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($data));
	}
	/**
	 * [attach 附件上传]
	 * @return [type] [description]
	 */
	public function index(){
		if(IS_POST){
			$dir = I('request.dir','image','trim');
			if(!in_array($dir,array('file','flash','image','word_resume','company_logo','company_img','certificate_img'))) return false;
			if (!empty($_FILES['imgFile']['name'])) {
				$this->$dir();
			}else{
				$this->ajaxReturn(1, L('illegal_parameters'));
			}
		}
	}
	/**
	 * [image 个人简历图片上传]
	 * @return [type] [description]
	 */
	protected function image(){
		$date = date('ym/d/');
		$result = $this->_upload($_FILES['imgFile'], 'images/' . $date, array(
				'maxSize' => C('qscms_resume_photo_max'),//图片大小上限
				'uploadReplace' => true,
				'attach_exts' => 'bmp,png,gif,jpeg,jpg'
		));
		if ($result['error']) {
			// 保存
			$img = $date.$result['info'][0]['savename'];
			$data = attach($img,'images');
			$this->ajaxReturn(0, L('upload_success'), $data);
		} else {
			$this->ajaxReturn(1, $result['info']);
		}
	}
	protected function flash(){
		$date = date('ym/d/');
		$result = $this->_upload($_FILES['imgFile'], 'flash/' . $date, array(
				'maxSize' => 5*1024,//flash大小上限
				'uploadReplace' => true,
				'attach_exts' => 'swf,flv'
		));
		if ($result['error']) {
			// 保存
			$img = $date.$result['info'][0]['savename'];
			$data = attach($img,'flash');
			$this->ajaxReturn(0, L('upload_success'), $data);
		} else {
			$this->ajaxReturn(1, $result['info']);
		}
	}
	protected function file(){
		$date = date('ym/d/');
		$result = $this->_upload($_FILES['imgFile'], 'file/' . $date, array(
				'maxSize' => 5*1024,//文件大小上限
				'uploadReplace' => true,
				'attach_exts' => 'doc,docx,xls,xlsx,ppt,htm,html,txt,zip,rar,gz,bz2'
		));
		if ($result['error']) {
			// 保存
			$img = $date.$result['info'][0]['savename'];
			$data = attach($img,'file');
			$this->ajaxReturn(0, L('upload_success'), $data);
		} else {
			$this->ajaxReturn(1, $result['info']);
		}
	}
}
?>