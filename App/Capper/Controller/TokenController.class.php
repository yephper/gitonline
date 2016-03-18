<?php
namespace Capper\Controller;

/****商户* * * */
class TokenController extends CapperappController {
	
	/*** 获取token ***/
	public function get_token(){
		$appid = I('post.appid');
		$secret = I('post.secret');
		$result = D('TrApptoken')->getToken($appid,$secret);
		die(JSON($result));
	}

	/*** 从H5页面到app的地址 ***/
	public function viewToApp(){
		$linkurl = I('get.linkurl');//返回html的地址
		$direction = I('get.direction');//去app的地方
	}

	/*** 从app到H5页面的地址 ***/
	public function appToView(){
		$linkurl = I('get.linkurl');//返回html的地址
		$utoken = I('get.utoken');//去app的地方
		$skipurl = url_param_encrypt($linkurl,'D');
		if($skipurl){
			$tourl = $skipurl.'?utoken='.$utoken;
		}elseif($linkurl){
			$tourl =urldecode($linkurl) . '?utoken='.$utoken;
		
		}
		//exit(I('get.linkurl').'<br/>'.$linkurl);
		if($tourl)header("location:".$tourl);
	}



	/*** 邀请下载 ***/
	public function inviteDown(){
		$inviter = I('get.inviter');//邀请人Id
		$user_agent = I('server.HTTP_USER_AGENT');
		if(stristr($user_agent,'iPhone') || stristr($user_agent, 'ipa')){
			$msystem = 'ios';
		}else{
			$msystem = 'android';
		}
		$appid = 1;
		$app = D('System/App');
		$appinfo = $app->find($appid);
		$this->assign('appinfo', $appinfo);
		if(strpos($user_agent, "MicroMessenger")) {
			$this->display('App_down');
		}elseif(stristr($user_agent,'iPhone') || stristr($user_agent, 'ipa')){
			header("Location:https://itunes.apple.com/cn/app/id1020272189?ls=1&mt=8"); 
			exit;
			/*
			if(file_exists( APP_DIR.$appinfo['iosurl']) && $appinfo['iosurl']) {
				$app->where(array('id'=>$appid))->setInc('iosdownloads');
				$this->assign('app', $appinfo);
				$this->assign('apptype', 2);
				$this->display('App_iosdown');
				exit;
			}
			*/
		}else{
			if(file_exists( APP_DIR.$appinfo['androidurl']) && $appinfo['androidurl']) {
				$app->where(array('id'=>$appid))->setInc('androiddownloads');
				header('Location: '.$appinfo['androidurl']); exit;
			}
		}
		die('无APP下载文件');
	}


	/***客户端【安卓与苹果】支付宝支付的异步通知***/
	public function alipayNotify(){
		$alipay_config = array();
		$alipay_config['partner'] = '2088021961137624';
		$alipay_config['private_key_name'] = 'quanmingfanli_rsa_private_key';
		/*验证支付方式有效性*/
		$alipayclientnotify = new \Org\Util\pay\alipayclientnotify($alipay_config);
		$verify_result = $alipayclientnotify->verifyNotify();
		if($verify_result==false){
			$alipay_config = array();
			$alipayclientnotify = new \Org\Util\pay\alipayclientnotify($alipay_config);
			$verify_result = $alipayclientnotify->verifyNotify();
		}
		if($verify_result) {//验证成功

			$trade_status = I('post.trade_status');//交易状态
			$oid = I('post.out_trade_no');//支付宝订单号
			$pay_trade_no = I('post.trade_no');//支付宝交易号
			($trade_status=='TRADE_SUCCESS' || $trade_status=='TRADE_FINISHED') or die('success');//交易成功，否则直接退回，返回接受数据成功
			$pway = 'alipaywap';
			$orderinfo = D('TrOrder')->where(array('o_id'=>$oid))->find();
			if(!$orderinfo)die('fail');
			if($orderinfo['o_pstatus']!=1){
				$ainfo['o_pway'] = $pway;
				$ainfo['o_pstime'] = date('Y-m-d H:i:s');
				$ainfo['o_pstatus'] = '1';
				$ainfo['o_dstatus'] = '3';
			}
			D('TrOrder')->where(array('o_id'=>$oid))->setField($ainfo);
			$paylog = M('tr_paylog')->where(array('pay_trade_no'=>$pay_trade_no))->find();
			if(!$paylog){
				/***记录支付日志***/
				$paylog = array();
				$paylog['pay_uid'] = $orderinfo['o_uid'];
				$paylog['pay_type'] = $orderinfo['o_gtype'];//1代表购买支付
				$paylog['pay_price'] = $orderinfo['o_price'];
				$paylog['pay_time'] = date('Y-m-d H:i:s');
				$paylog['pay_oid'] = $oid;
				$paylog['pay_trade_no'] = $pay_trade_no;
				$paylog['pay_way'] = $pway;
				M('tr_paylog')->data($paylog)->add();
				//$mission = new \Common\Org\Commission;
				//$mission->insertInfo($oid);
				if($orderinfo['o_gtype']==3){
					D('FlUser')->where(array('u_userid'=>$orderinfo['o_uid']))->setField('u_usertype','1');//升级订单改变会员状态
					//对于此订单进行分钱操作
					$commission = \Common\Org\Commission::translation()->insertInfo( $oid );
					$commission = json_decode( $commission, true );
					if( $commission['erron']==0 ) M('tr_order')->where( array("o_id"=>$oid) )->setField( array("o_dstatus"=>4, "o_isback"=>1) );
				}
				
				//话费充值和流量充值
				if( $orderinfo['o_gtype']==4 || $orderinfo['o_gtype']==5 ):
					$ordergsnapshot = M("TrGsnapshot")->where( array("g_oid"=>$oid) )->find();
						
					if( $ordergsnapshot['g_name'] != 'success' ): //防止重复充值
						$Recharge	= new \Common\Org\Recharge();
						if( $orderinfo['o_gtype']==4 ) //话费充值
						{
							$s = $Recharge->PcallRecharge( $oid, (int)$orderinfo['o_price'], $ordergsnapshot['g_gdescription']);
						}
						elseif( $orderinfo['o_gtype']==5 ) //流量充值
						{
							$phoneInfo = getPhoneAddress( $ordergsnapshot['g_gdescription'] );
							$arsid = 3100;
							if( stripos($phoneInfo['data'], "联通") ) {
								$arsid = 2100;
							} elseif( stripos($phoneInfo['data'], "电信") ) {
								$arsid = 1100;
							}
							$s = $Recharge->PflowRecharge( $oid, (int)$ordergsnapshot['g_number'], $ordergsnapshot['g_gdescription'], $arsid);
						}
						if( !$s ) { //失败，但是不一定失败，由于网络可能返回数据慢，处理中，这种请联系平台
							$msg = $Recharge->getError();
							M("TrGsnapshot")->where( array("g_oid"=>$oid) )->setField("g_name", $msg);
							die(JSON(array('errcode'=>'81212', 'errmsg'=>'您的订单提交成功，充值失败，请联系客服')));
						} else {
							M("TrGsnapshot")->where( array("g_oid"=>$oid) )->setField("g_name", "success");
						}
						
						//对于此订单进行分钱操作
						$commission = \Common\Org\Commission::translation()->insertInfo( $oid );
						$commission = json_decode( $commission, true );
						if( $commission['erron']==0 ) M('tr_order')->where( array("o_id"=>$oid) )->setField( array("o_dstatus"=>4, "o_isback"=>1) );
					endif;
				endif;	
				
				
				
				
				
				
				
				
				die("success");
			}
			die("fail");
		}else {
			die("fail");
		}
	}

	public function test(){

		print_r(I('server.HTTP_HOST'));
		print_r(I('server.SERVER_NAME'));

		$this->display();
	}

}