<?php
/**
 * 合并加载JS和CSS文件
 *
 * @author brivio
 */
namespace Common\qscmstag;
defined('THINK_PATH') or exit();
class LoadTag {
    protected $jm;
    protected $options = array();
    function __construct($options) {
        $this->options = $options;
        import('Common.ORG.JSMin');
        $this->jm = new \JSMin();
    }
    /*
     * 生成默认JS文件(根据当前模型类名称)
    */
    public function def(){
    	$this->js(array('href'=>'__STATIC__/js/'.MODULE_NAME.'/'.__MODULE__.'.js'));
    }
    public function js() {
        $path = QSCMS_DATA_PATH . 'static/' . md5($this->options['href']) . '.js';
        $statics_url = C('qscms_statics_url') ? C('qscms_statics_url') : './static';
        if (!is_file($path)) {
            //静态资源地址
            $files = explode(',', $this->options['href']);
            $content = '';
            foreach ($files as $val) {
                $val = str_replace('__STATIC__', $statics_url, $val);
                $content.=file_get_contents($val);
            }
            file_put_contents($path, $this->jm->minify($content));
        }
        echo ( '<script type="text/javascript" src="' . __ROOT__ . '/data/static/' . md5($this->options['href']) . '.js?"></script>');
    }
    /**
     * [category 生成项目所需枚举类js缓存]
     * @return [js]                  [js文件]
     */
    public function category(){
        if($this->options['search'] && C('SUBSITE_VAL.s_id') > 0) $cache = '_'.C('SUBSITE_VAL.s_id');
        $path = QSCMS_DATA_PATH . 'static/' . md5('cache_classify') . $cache . '.js';
        $statics_url = C('qscms_statics_url') ? C('qscms_statics_url') : './static';
        if (!is_file($path)) {
            //静态资源
            $cates['QS_city'] = D('CategoryDistrict')->get_district_cache('all');
            if($this->options['search'] && C('SUBSITE_VAL.s_id') > 0){
                $city = C('SUBSITE_VAL.s_district');
                $cates['QS_city'][0] = $cates['QS_city'][$city];
            }
            $cates['QS_jobs'] = D('CategoryJobs')->get_jobs_cache('all');
            $cates['QS_major'] = D('CategoryMajor')->get_major_cache('all');
            if(false === $this->apply = F('apply_list')) $this->apply = D('Apply')->apply_cache();
            if($this->apply['Mall']){
                $cates['QS_shop'] = D('Mall/MallCategory')->get_mall_cache('all');
            }
            $content = '';
            foreach ($cates as $key => $cate) {
                $content.= "var {$key}_parent=new Array({$this->assembly($cate[0])});";
                $content.="var {$key}=new Array();";
                foreach ($cate[0] as $_key=>$val) {
                    $content.="{$key}[{$_key}]={$this->assembly($cate[$_key],'`','')};";
                    if(($key == 'QS_jobs' || $key == 'QS_city') && $cate[$_key]){
                        foreach ($cate[$_key] as $skey=>$sval) {
                            $content.="{$key}[{$skey}]={$this->assembly($cate[$skey],'`','')};";
                        }
                    }
                }
            }
            if(false === $spell['QS_jobs_spell'] = F('jobs_custom_cate')) $spell['QS_jobs_spell'] = D('CategoryJobs')->custom_jobs_cache();
            if(false === $spell['QS_city_spell'] = F('district_custom_cate')) $spell['QS_city_spell'] = D('CategoryDistrict')->custom_district_cache();
            if($this->options['search'] && C('SUBSITE_VAL.s_id') > 0){
                $spell['QS_city_spell'][0] = $spell['QS_city_spell'][$city];
            }
            foreach ($spell as $key => $cate) {
                $content.= "var {$key}_parent=new Array({$this->spell_assembly($cate[0])});";
                $content.="var {$key}=new Array();";
                foreach ($cate[0] as $_key=>$val) {
                    $content.="{$key}['{$val['spell']}']={$this->spell_assembly($cate[$_key],'`','')};";
                    if($cate[$_key]){
                        foreach ($cate[$_key] as $skey=>$sval) {
                            $content.="{$key}['{$sval['spell']}']={$this->spell_assembly($cate[$skey],'`','')};";
                        }
                    }
                }
            }
            $category = D('Category')->get_category_cache();
            foreach ($category as $key => $val) {
                $arr = array();
                foreach ($val as $_key=>$_val) {
                    $arr[] = '"'.$_key.','.$_val.'"';
                }
                $arr = implode(',',$arr);
                $content.="var {$key}=new Array({$arr});";
            }
            file_put_contents($path,$this->jm->minify($content));
        }
        echo ( '<script type="text/javascript" src="' . __ROOT__ . '/data/static/' . md5('cache_classify') . $cache . '.js?"></script>');
    }
    /**
     * [spell_assembly 数组转字符串]
     * @param  [array]     $data    [被转换的数组]
     * @param  string      $p       [间隔字符]
     * @return [string]             [处理结果]
     */
    public function spell_assembly($data,$p=',',$s='"'){
        foreach ($data as $key=>$val) {
            $arr[] = $s.$val['spell'].','.$val['categoryname'].$s;
        }
        $arr = implode($p,$arr);
        if(!$s) return '"'.$arr.'"';
        return $arr;
    }
    /**
     * [assembly 数组转字符串]
     * @param  [array]     $data    [被转换的数组]
     * @param  string      $p       [间隔字符]
     * @return [string]             [处理结果]
     */
    public function assembly($data,$p=',',$s='"'){
        foreach ($data as $key=>$val) {
            $arr[] = $s.$key.','.$val.$s;
        }
        $arr = implode($p,$arr);
        if(!$s) return '"'.$arr.'"';
        return $arr;
    }
}