<?php
namespace Common\Model;
use Think\Model;
class PersonalFocusCompanyModel extends Model{
	/**
	 * 获取个人关注企业列表
	 * $data  查询条件 为 personal_favorites 表中条件 
	 * 
	 * 返回值 数组
	 * $rst['list'] 数据列表 array
	 * $rst['page'] 分页
	 */
	public function get_focus_company($data,$pagesize=10,$is_jobs=false){
		$db_pre = C('DB_PREFIX');
		$this_t = $db_pre.'personal_focus_company';
		foreach ($data as $key => $val){
			$where[$this_t.'.'.$key] = $val;
		}
		$join = 'left join '.$db_pre .'company_profile c on c.id='.$this_t.'.company_id';
		$rst['count'] = $this->where($where)->join($join)->count();
		if($rst['count']){
			$pager =  pager($rst['count'], $pagesize);
			$rst['list'] = $this->field('c.id,c.companyname,c.logo,c.trade_cn,c.nature_cn,c.district_cn,c.scale_cn,c.address,c.tag')->where($where)->join($join)->order($this_t.'.id desc')->limit($pager->firstRow . ',' . $pager->listRows)->select();
			if(C('apply.Subsite')){
                if(false === $subsite_district = F('subsite_district')) $subsite_district = D('Subsite')->subsite_district_cache();
            }
			if($rst['list']){
				foreach ($rst['list'] as $key=>$val){
					$list_map['company_id'] = $val['id'];
					if(C('qscms_jobs_display')==1){
						$list_map['audit'] = 1;
					}
					if($val['tag'] && $comtag = explode(",",$val['tag'])){
						foreach ($comtag as $_key => $value) {
		                    $arr = explode("|",$value);
		                    $tagArr['id'][] = $arr[0];
		                    $tagArr['cn'][] = $arr[1];
		                }
		                $rst['list'][$key]['tag_id'] = $tagArr['id'];
		                $rst['list'][$key]['tag_cn'] = $tagArr['cn'];
					}
					$rst['list'][$key]['jobs_list'] = M('Jobs')->where($list_map)->limit('2')->getfield('id,jobs_name,minwage,maxwage,negotiable');
					$rst['list'][$key]['jobs_count'] = M('Jobs')->where($list_map)->count();
					if($rst['list'][$key]['jobs_count']){
						foreach ($rst['list'][$key]['jobs_list'] as $k => $v) {
							if($v['negotiable']==0){
					            $v['minwage'] = $v['minwage']%1000==0?(($v['minwage']/1000).'K'):(round($v['minwage']/1000,1).'K');
					            $v['maxwage'] = $v['maxwage']?($v['maxwage']%1000==0?(($v['maxwage']/1000).'K'):(round($v['maxwage']/1000,1).'K')):'';
					            $v['maxwage'] = $v['maxwage']?('-'.$v['maxwage']):'';
					            $v['wage_cn'] = $v['minwage'].$v['maxwage'].'/月';
					        }else{
					            $v['wage_cn'] = '面议';
					        }
				        	C('apply.Subsite') && $subsite_id = $subsite_district[$v['tdistrict']]?:($subsite_district[$v['sdistrict']]?:$subsite_district[$v['district']]);
				        	$v['jobs_url']=url_rewrite('QS_jobsshow',array('id'=>$v['id']),$subsite_id);
					        $rst['list'][$key]['jobs_list'][$k] = $v;
						}
					}
				}
			}
			$rst['page'] = $pager->fshow();
		}
		return $rst;
	}
	public function add_focus($company_id,$uid){
		$has = $this->check_focus($company_id,$uid);
		if($has){
			$this->where(array('company_id'=>$company_id,'uid'=>$uid))->delete();
			return array('state'=>1,'msg'=>'已取消关注！','data'=>array('html'=>'关注','op'=>2));
		}else{
			$this->add(array('company_id'=>$company_id,'uid'=>$uid,'addtime'=>time()));
			return array('state'=>1,'msg'=>'已关注！','data'=>array('html'=>'取消关注','op'=>1));
		}
	}
	public function check_focus($company_id,$uid){
		return $this->where(array('company_id'=>$company_id,'uid'=>$uid))->find();
	}
}
?>