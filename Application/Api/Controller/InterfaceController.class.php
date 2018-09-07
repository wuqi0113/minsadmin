<?php
namespace Api\Controller;
use Think\Controller;
use Api\Controller\BaseController;
class InterfaceController extends BaseController 
{
    /**
     * 获取验证码
     */
    public function getCode()
    {
        if(IS_POST){
            $phone = I('phone');
            $phone = (string)$phone;
            if(!$phone || strlen($phone) != 11){
                $ajax['code'] = 0;
                $ajax['msg'] = '手机号有误';
                $this->ajaxReturn($ajax);
            }

            $model = D('Code');
            $where['phone'] = $phone;
            $where['old_time'] = ['gt', time()];
            $where['status'] = 1;

            $result = $model->where($where)->find();

            if($result){
                $re = send_sms($phone,array($result['code'],'2分钟'),"318497");
                if($re == 'success'){
                    $ajax['code'] = 1;
                    $ajax['msg'] = '发送成功';
                }else{
                    $ajax['code'] = 0;
                    $ajax['msg'] = $re;
                }
            }else{
                $data['code'] = rand(1000, 9999);
                $data['phone'] = $phone;
                $data['old_time'] = strtotime('+2 minute');
                $res = $model->add($data);
                if($res){
                    $re = send_sms($phone,array($data['code'],'2分钟'),"318497");
                    if($re == 'success'){
                        $ajax['code'] = 1;
                        $ajax['msg'] = '发送成功';
                    }else{
                        $ajax['code'] = 0;
                        $ajax['msg'] = $re;
                    }
                }else{
                    $ajax['code'] = 0;
                    $ajax['msg'] = '验证码发送失败';
                }
            }
            $this->ajaxReturn($ajax);
        }
    }

    /**
     * 注册
     */
    public function register()
    {
        if(IS_POST){

            $phone = I('phone');
            $code = I('code');
            $password = I('password');
            $repassword = I('repassword');

            $phone = (string)$phone;
            if(!$phone || strlen($phone) != 11){
                $ajax['code'] = 0;
                $ajax['msg'] = '手机号有误';
            }

            $model = D('User');
            $where['login_phone'] = $phone;
            $is_phone = $model->where($where)->find();
            if($is_phone){
                $ajax['code'] = 0;
                $ajax['msg'] = '手机号已存在';
            }

            $codeModel = D('Code');
            $map['phone'] = $phone;
            $map['old_time'] = ['gt', time()];
            $map['code'] = $code;
            $map['status'] = 1;

            $result = $codeModel->where($map)->find();

            if(!$result){
                $ajax['code'] = 0;
                $ajax['msg'] = '验证码有误,请重新获取';
            }

            if($password !== $repassword){
                $ajax['code'] = 0;
                $ajax['msg'] = '两次密码不一致';
            }

            if($ajax){
                $this->ajaxReturn($ajax);
            }

            $data['login_phone'] = $phone;
            $data['password'] = md5($password);
            $data['create_time'] = time();
            $data['login_time'] = time();

            $res = $model->add($data);
            if($res){
                $ajax['code'] = 1;
                $ajax['msg'] = '注册成功';
                
                $codeW['phone'] = $phone;
                $codeW['status'] = 1;
                $codeModel->where($codeW)->save(['status' => 2]);

            }else{
                $ajax['code'] = 0;
                $ajax['msg'] = '注册失败';
            }
            $this->ajaxReturn($ajax);
        }
    }

    /**
     * 密码登录
     */
    public function pwdLogin()
    {
        if(IS_POST){
            $phone = I('phone');
            $password = I('password');

            if(!$phone || strlen($phone) != 11){
                $ajax['code'] = 0;
                $ajax['msg'] = '手机号有误';
            }

            if(!$password){
                $ajax['code'] = 0;
                $ajax['msg'] = '请填写密码';
            }

            if($ajax){
                $this->ajaxReturn($ajax);
            }

            $model = D('User');
            $where['login_phone'] = $phone;
            $where['password'] = md5($password);

            $res = $model->where($where)->find();
            if (!$res) {
                $ajax['code'] = 0;
                $ajax['msg'] = '账号密码有误';

                $save['login_number'] = $res['login_number'] + 1;
                $save['login_time'] = time();
                $model->where($where)->save($save);
            } else {
                $ajax['uid'] = lock_uid($res['id']);
                $ajax['code'] = 1;
                $ajax['msg'] = '登陆成功';
            }
            $this->ajaxReturn($ajax);
        }
    }

    /**
     * 验证码登录
     */
    public function codeLogin()
    {
        if(IS_POST){
            $phone = I('phone');
            $code = I('code');

            if(!$phone || strlen($phone) != 11){
                $ajax['code'] = 0;
                $ajax['msg'] = '手机号有误';
            }

            $codeModel = D('Code');
            $map['phone'] = $phone;
            $map['old_time'] = ['gt', time()];
            $map['code'] = $code;
            $map['status'] = 1;

            $result = $codeModel->where($map)->find();

            if(!$result){
                $ajax['code'] = 0;
                $ajax['msg'] = '验证码有误,请重新获取';
                $this->ajaxReturn($ajax);
            }

            $model = D('User');
            $where['login_phone'] = $phone;

            $res = $model->where($where)->find();
            if (!$res) {
                $ajax['code'] = 0;
                $ajax['msg'] = '没有该手机号,请注册';
            } else {
                $ajax['code'] = 1;
                $ajax['msg'] = '登陆成功';
                $ajax['uid'] = lock_uid($res['id']);

                $save['login_number'] = $res['login_number'] + 1;
                $save['login_time'] = time();
                $model->where($where)->save($save);

                $codeW['phone'] = $phone;
                $codeW['status'] = 1;
                $codeModel->where($codeW)->save(['status' => 2]);
            }
            $this->ajaxReturn($ajax);
        }
    }

    /**
     * 获取Logo
     */
    public function getLogo()
    {
        if(IS_POST){
            $model = D('Logo');
            $where['status'] = 1;
            $data = $model->where($where)->select();
            if($data){
                $ajax['code'] = 1;
                $ajax['msg'] = 'success';
                $ajax['data'] = $data;
            }else{
                $ajax['code'] = 0;
                $ajax['msg'] = '暂无Logo';
            }
            $this->ajaxReturn($ajax);
        }
    }

    /**
     * 获取首页保险信息
     */
    public function getIndex()
    {
        if(IS_POST){
            $model = D('Insurance');
            $ubiModel = D('UserBuyInsurance');

            //购买保险总人数
            $ubiNumber = $ubiModel->count();

            //保险列表
            $where['status'] = 1;
            $data = $model->where($where)->select();

            $ajax['code'] = 1;
            $ajax['msg'] = 'success';
            $ajax['data']['user_number'] = $ubiNumber;
            $ajax['data']['insurance'] = $data;
            $this->ajaxReturn($ajax);
        }
    }

    /**
     * 保险详情
     */
    public function getInsuranceDetails()
    {
        if(IS_POST) {
            $id = I('insurance_id');
            if (!$id) {
                $ajax['code'] = 0;
                $ajax['msg'] = '参数有误';
                $this->ajaxReturn($ajax);
            }

            //资金池总额
            $orderModel = D('Order');
            $orderWhere['insurance_id'] = $id;
            $orderWhere['status'] = 4;
            $all_money = $orderModel->where($orderWhere)->Sum('order_money');

            //加入计划总人数
            $ubiModel = D('UserBuyInsurance');
            $ubiWhere['insurance_id'] = $id;
            $all_user_buy = $ubiModel->where($ubiWhere)->count();

            //近7日加入计划人数
            $sevenWhere['insurance_id'] = $id;
            $time = strtotime(date("Y-m-d", strtotime("-7 day")));
            $sevenWhere['create_time'] = ['EGT', $time];
            $seven_user_buy = $ubiModel->where($sevenWhere)->count();

            //年龄金额
            $insuredAmountModel = D('InsuredAmount');
            $insuredAmountWhere['insurance_id'] = $id;
            $ageMoney = $insuredAmountModel->where($insuredAmountWhere)->select();

            $model = D('Insurance');
            $where['id'] = $id;
            $data = $model->where($where)->find();
            $data['all_money'] = $all_money;
            $data['all_user_buy'] = $all_user_buy;
            $data['seven_user_buy'] = $seven_user_buy;
            $data['age_money'] = $ageMoney;

            $ajax['code'] = 1;
            $ajax['msg'] = 'success';
            $ajax['data'] = $data;
            $this->ajaxReturn($ajax);
        }
    }
}