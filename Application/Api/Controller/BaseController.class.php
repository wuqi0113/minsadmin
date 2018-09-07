<?php
namespace Api\Controller;
use Think\Controller;
class BaseController extends Controller {

	protected static $_userInfo;
    
    protected function _initialize()
    {
    	header('Access-Control-Allow-Origin:*');
    	$uid = I('uid');
    	if($uid){
    		if(!isset(self::$_userInfo)){
    			$model = D('User');
    			$uid = unlock_uid($uid);
    			$info = $model->where(['id' => $uid])->find();
    			self::$_userInfo = $info;
    		}
    	}
    }
}