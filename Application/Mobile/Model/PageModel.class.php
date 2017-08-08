<?php
namespace Common\Model;
use Think\Model;
class PageModel extends Model{
	/**
	 * 读取系统参数生成缓存文件
	 */
	public function page_seo_cache(){
		$page_list = F('mobile_page_config','',MODULE_PATH.'Conf/');
		foreach ($page_list as $key => $val) {
			$page[strtolower($val['module']).'_'.strtolower($val['controller']).'_'.strtolower($val['action'])] = array('title'=>$val['title'],'keywords'=>$val['keywords'],'description'=>$val['description'],'header_title'=>$val['header_title']);
		}
		F('Mobile_page_seo_list', $page);
		$time = filemtime(MODULE_PATH.'Conf/mobile_page_config.php');
		S(C('qscms_mobile_page'),$time);
		return $page;
	}
	/**
	 * 读取系统参数生成缓存文件
	 */
	public function page_cache() {
		if(C('SUBSITE_VAL')){
			$where['subsite_id'] = C('SUBSITE_VAL.s_id');
			$cache = '_'.C('SUBSITE_VAL.s_id');
		}
		if(false === $pageList = F('page_list'.$cache)){
			$pageList = $this->where($where)->getfield('alias,id,pname,module,controller,action,rewrite,url,tag');
			foreach ($pageList as $key => $val) {
				$pageList[$key]['module'] = strtolower($pageList[$key]['module']);
				$pageList[$key]['controller'] = strtolower($pageList[$key]['controller']);
				$pageList[$key]['action'] = strtolower($pageList[$key]['action']);
			}
			F('page_list'.$cache, $pageList);
		}
		return $pageList;
	}
	public function get_page(){
		$page_list = F('mobile_page_config','',MODULE_PATH.'Conf/');
		$time = filemtime(MODULE_PATH.'Conf/mobile_page_config.php');
		$time_cache = S(C('qscms_template_dir'));
		if($time_cache < $time) $this->_before_write();
		if(false === $page_seo = F('Mobile_page_seo_list')) $page_seo = $this->page_seo_cache();
		return $page_seo;
	}
	/**
     * 后台有更新则删除缓存
     */
    protected function _before_write($data, $options) {
        F('Mobile_page_seo_list', NULL);
    }
}
?>