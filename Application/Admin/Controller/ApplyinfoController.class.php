<?php
namespace Admin\Controller;
use Common\Controller\BackendController;
class ApplyinfoController extends BackendController {
    public function _initialize() {
        parent::_initialize();
    }
    /**
     * 前台申请
     */
    public function index()
    {
        $count      = M('applyinfo')->where(array('state' => 1))->count();
        $Page       = new \Think\Page($count,12);
        $show       = $Page->show();
        $res = M('applyinfo')->where(array('state' => 1))->order('`view` ASC,`id` ASC')->limit($Page->firstRow.','.$Page->listRows)->select();
        $ve = array('0' => '未推送', '1' => '已推送');
        $sex = array('0' => '保密', '1' => '男', '2' => '女');
        foreach ($res as $k => $v) {
            $res[$k]['jobs_name'] = M('jobs')->where(array('id' => $v['jobid']))->getField('jobs_name');
            $res[$k]['view'] = $ve[$v['view']];
            $res[$k]['sex'] = $sex[$v['sex']];
        }
        
        $this->assign('res',$res);
        $this->assign('page',$show);
        $this->assign('total',$count);
        $this->display();
    }
    
    /**
     * 推送
     */
    public function apply()
    {
        header("Content-Type: text/html;charset=utf-8");
        
        $jobid = I('get.jobid', '', 'int');
        $id = I('get.id', '', 'int');
        if(!$id || !$jobid) {
            $this->error('请选择推送项');
        }
        
        M('applyinfo')->where(array('id' => $id))->save(array('view' => 1));
        
        $sql_one = M('jobs')->where(array('id' => $jobid))->find();
        $sql_two = M('company_profile')->where(array('id' => $sql_one['company_id']))->find();
        $sql_three = M('applyinfo')->where(array('id' => id))->find();
    
        if ($sql_three['sex'] == 1) {
            $sexe = '男';
        } else {
            $sexe = '女';
        }
        
        $name = $sql_three['name'];
        $companyname = $sql_two['companyname'];
        $category_cn = $sql_one['category_cn'];
        $address = $sql_two['address'];
        $postArr = json_encode(array ('header' => array('appId' => 'server-001', 'secret' => '538d479a3b338f128c076a9d0c45eb7c'),
            'body' => array(
                'mobile' => $sql_three['phone'],
                'name' => $name,
                'sex' => $sexe,
                'company' => $companyname,
                'position' => $category_cn,
                'time' => '10:00-17:00',
                'address' => $address
            ),
        ));
        $resa = $this->http_post_data('http://wjtest.ngrok.cc/sms/notifyInterview', $postArr);
        $this->success("操作成功！");
    }
    
    function http_post_data($url, $data_string) {
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($data_string))
        );
        ob_start();
        curl_exec($ch);
        return true;
        
        //$return_content = ob_get_contents();
        //ob_end_clean();
    
        //$return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        //return array($return_code, $return_content);
    }
    
    /* public function request_post($url = '', $param = '') {
        if (empty($url) || empty($param)) {
            return false;
        }
        
        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//运行curl
        curl_close($ch);

        return $data;
    } */
    
    /* public function do_post_request($url, $data, $optional_headers = null)
    {
        $params = array('http' => array(
            'method' => 'POST',
            'content' => $data
        ));
        if ($optional_headers !== null) {
            $params['http']['header'] = $optional_headers;
        }
        $ctx = stream_context_create($params);
        $fp = @fopen($url, 'rb', false, $ctx);
        if (!$fp) {
            throw new \Exception("000000");
        }
        $response = @stream_get_contents($fp);
        if ($response === false) {
            throw new \Exception("000000");
        }
        return $response;
    } */
    
    /**
     * 新增一条数据
     */
    public function addinfo()
    {
        header("Content-Type: text/html;charset=utf-8");
        
        /* $data = array(
            'name' => '前台申请',
            'pid' => 0,
            'spid' => '727|',
            'module_name' => 'Admin',
            'controller_name' => 'Applyinfo',
            'action_name' => 'index',
            'menu_type' => 1,
            'is_parent' => 1,
            'display' => 1,
            'sys_set' => 1
        );
        $res = M('menu')->add($data); */
        //$res = M('menu')->where(array('id' => array('in', '730,731')))->delete();
        //print_r($res);exit;
        $res = M('members')->select();
        print_r($res);exit;
        //$res = M('members')->where(array('uid' => array('in', '2')))->delete();
        if($res) {
            $this->success("成功！");
        } else {
            $this->error("失败！");
        }
    }
    
    /**
     * 删除
     */
    public function del_jobs()
    {
        $id = I('get.id', '', 'int');
        if(!$id){
            $this->error('请选择删除项');
        }
        $seek = M('applyinfo')->where(array('id' => $id))->save(array('state' => 2));
        if ($seek) {
            admin_write_log("删除职位id为".$id."的职位", C('visitor.username'),10);
            $this->success("删除成功！共删除".$seek."行");
        } else {
            $this->error("删除失败！");
        }
    }
}