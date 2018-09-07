<?php


class Sms{
	
	public function set_phone($tel,$content,$dz,$sms_config,$type){
		header("Content-type: text/html; charset=utf-8");
		date_default_timezone_set('PRC'); //设置默认时区为北京时间
		//短信接口用户名 $uid
		$uid = $sms_config['username'];
		//短信接口密码 $passwd
		$passwd = $sms_config['password'];

		if($type=='kc'){
			$message=str_replace('xx',$content,$sms_config['content']);
			$message=str_replace('dz',$dz,$message);
		}else{
			$sms='您好，您的验证码是【XXX】，请在5分钟内使用，请不要把验证码泄露给其他人。';
			$message=str_replace('XXX',$content,$sms);
		}
		
		$msg = rawurlencode(mb_convert_encoding($message, "gb2312", "utf-8"));
		$gateway = "https://sdk2.028lk.com/sdk2/BatchSend2.aspx?CorpID={$uid}&Pwd={$passwd}&Mobile={$tel}&Content={$msg}&Cell=&SendTime=";
//		echo $gateway;
		$result = file_get_contents($gateway);
//	 	echo 520;
//	 	echo $result;
 		if(  $result > 0 ){
	        return '发送成功';
		}else{
			switch($result){
				case -1:
					return '账号未注册';
					break;
				case -2:
					return '其他错误';
					break;
				case -3:
					return '帐号或密码错误';
					break;
				case -5:
					return '余额不足，请充值';
					break;		
				case -9:
					return '发送号码为空';
					break;		
			}
		}
	}
	
}
?>