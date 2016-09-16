<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {

	public static $log = null; //保存log

	private function checkSignature(){
		$signature = $_GET["signature"];
		$timestamp = $_GET["timestamp"];
		$nonce = $_GET["nonce"];
		        
		$token = "iacj";
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );

		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}

	public function index(){
		if (!self::checkSignature()){
			return false;
		}

		if(!empty($_GET['echostr']) ){
			//第一次接入weixin api接口的时候
			echo  $_GET['echostr'];
			exit;
		}else{
			self::$log = "IndexPHP:确认信息来自微信";
			$this->reponseMsg();
		}
	}

	public function reponseMsg(){
		$postArr = $GLOBALS['HTTP_RAW_POST_DATA'];
		//self::$log .= "收到消息:".$postArr;

		$postObj = simplexml_load_string( $postArr );

		// log
		self::$log .="\n接收者: ".$postObj->ToUserName ;
		self::$log .="\n发送者: ".$postObj->FromUserName ;
		self::$log .="\n时间: ".$postObj->CreateTime ;
		self::$log .="\n消息类型: ".$postObj->MsgType;
		if(isset($postObj->Event)){
			self::$log .="\n事件: ".$postObj->Event;
		}
		if(isset($postObj->Content)){
			self::$log .="\n内容: ".$postObj->Content;
		}

		// gh_e79a177814ed
		//判断该数据包是否是订阅的事件推送
		if( strtolower( $postObj->MsgType) == 'event'){
			//如果是关注 subscribe 事件
			if( strtolower($postObj->Event == 'subscribe') ){
				//回复用户消息(纯文本格式)	
				$toUser   = $postObj->FromUserName;
				$fromUser = $postObj->ToUserName;
				$time     = time();
				$msgType  =  'text';
				$content  = "欢迎登上瓦蓝雷空殆号舰桥！\n回复\"帮助\"得到更多内容";
				$template = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							</xml>";
				$info     = sprintf($template, $toUser, $fromUser, $time, $msgType, self::$log."
					\n\n".$content);
				echo $info;
			}
		}

/*<xml>
<ToUserName><![CDATA[toUser]]></ToUserName>
<FromUserName><![CDATA[fromUser]]></FromUserName>
<CreateTime>12345678</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[你好]]></Content>
</xml>*/

		//用户发送tuwen1关键字的时候，回复一个单图文
		if( strtolower($postObj->MsgType) == 'text' && trim($postObj->Content)=='图文消息' ){
			$toUser = $postObj->FromUserName;
			$fromUser = $postObj->ToUserName;
			$arr = array(
				array(
					'title'=>'scut',
					'description'=>"imooc is very cool",
					'picUrl'=>'http://www.scut.edu.cn/newimages/logo.jpg',
					'url'=>'http://www.scut.edu.cn/index.html',
				),
				array(
					'title'=>'baidu',
					'description'=>"baidu isn't very cool",
					'picUrl'=>'https://www.baidu.com/img/bdlogo.png',
					'url'=>'http://www.baidu.com',
				),
				array(
					'title'=>'qq',
					'description'=>"qq is very cool",
					'picUrl'=>'http://mat1.gtimg.com/www/images/qq2012/qqlogo_2x.png',
					'url'=>'http://www.qq.com',
				),
			);
			$template = "<xml>
						<ToUserName><![CDATA[%s]]></ToUserName>
						<FromUserName><![CDATA[%s]]></FromUserName>
						<CreateTime>%s</CreateTime>
						<MsgType><![CDATA[%s]]></MsgType>
						<ArticleCount>".count($arr)."</ArticleCount>
						<Articles>";
			foreach($arr as $k=>$v){
				$template .="<item>
							<Title><![CDATA[".$v['title']."]]></Title> 
							<Description><![CDATA[".$v['description']."]]></Description>
							<PicUrl><![CDATA[".$v['picUrl']."]]></PicUrl>
							<Url><![CDATA[".$v['url']."]]></Url>
							</item>";
			}
			
			$template .="</Articles>
						</xml> ";
			echo sprintf($template, $toUser, $fromUser, time(), 'news');

			//注意：进行多图文发送时，子图文个数不能超过10个
			return;
		}
		if( strtolower($postObj->MsgType) == 'text'){
			
			$get = trim($postObj->Content);
			if ($get == "帮助") {
				$content ="请尝试输入数字或字符进行测试。\n其中输入\"图文消息\"将调出图文消息。\n本demo仍在改进中。";
			}elseif ( is_numeric($get)) {
				$content = '您输入是数字 '.$get;
			}elseif(is_string($get)){
				$content = '您输入是字符串 '.$get;
			}

			
		$template = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[%s]]></MsgType>
<Content><![CDATA[%s]]></Content>
</xml>";
//注意模板中的中括号 不能少 也不能多
		$fromUser = $postObj->ToUserName;
		$toUser   = $postObj->FromUserName; 
		$time     = time();
		$msgType  = 'text';
		echo sprintf($template, $toUser, $fromUser, $time, $msgType,self::$log."\n\n".$content);
		}
	}

	// ---------------------------old index------------------------------
    public function notindex(){
        $this->show('<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} body{ background: #fff; font-family: "微软雅黑"; color: #333;font-size:24px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.8em; font-size: 36px } a,a:hover{color:blue;}</style><div style="padding: 24px 48px;"> <h1>:)</h1><p>欢迎来到 <b>IACJ</b>！</p><br/>版本 V{$Think.version}</div><script type="text/javascript" src="http://ad.topthink.com/Public/static/client.js"></script><thinkad id="ad_55e75dfae343f5a1"></thinkad><script type="text/javascript" src="http://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script>','utf-8');
    }
}