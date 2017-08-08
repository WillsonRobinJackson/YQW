<?php
/**
 * 触屏安装程序
 */
class MobileSetup{
	/**
	 * [setup_init 安装程序初始化程序]
	 */
	public function setup_init(){
		return true;
	}
	/**
	 * [setup 安装程序]
	 */
    public function setup(){}
    /**
     * [init 安装程序初始化程序]
     */
    public function unload_init(){
        if(false === $apply = F('apply_list')) $apply = D('Apply')->apply_cache();
        if($apply['Weixin']){
            $this->_error = '微信正在运行，请先卸载微信应用！';
            return false;
        }
        return true;
    }
    /**
     * [unload 卸载程序]
     */
    public function unload(){}
    /**
     * [getError 返回错误]
     */
    public function getError(){
    	return $this->_error;
    }
}
?>