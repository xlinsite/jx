<?php
error_reporting(0);
if (substr(PHP_VERSION,0.1) == 8){
require_once('JG_config_v8.php');
}else{
require_once('JG_config.php');
}
header('content-type:application/json');// 表明为json接口
define('HC_',false);hcon(HC_);//redis开启=true 关闭=false
//注意：关闭redis 就无法限制访问次数，限制访问次数功能失效。
$sts = 30;//删除ts数量
// redis配置**未设置过密码默认即可
$redisConfig = [
    'host' => '127.0.0.1',
    'port' => 6379,
    'pass' => '',//没有请留空
    'limit' => 10,//访问次数限制--10次
    'time' => 30,//访问时间限制--60秒
    'ip' => getip(),
];
//特征码 注意: 严格按照此示例格式！否则无法使用,注意结尾逗号！格式: '关键词',
$jggz = [//极光特征码
    'jiguang',
    ];
$m3u8Url = $_GET['url'];
if(empty($m3u8Url)){die('请输入极光资源 链接地址');exit;}
//****************************************************//
if(is_exist($m3u8Url,$jggz) !== false){$mulu="/Jg/";}//极光缓存目录
//****************************************************//
if(empty($mulu)){$arr=array('code'=>404,'msg'=>'解析失败',);die(json_encode($arr,456));exit;}
if (HC_){
    $redisk = redis($redisConfig['host'],$redisConfig['port'],$redisConfig['pass'],$redisConfig['limit'],$redisConfig['time'],$redisConfig['ip']);
    if(!empty($redisk)){
        $redisk_count = ['count' => '第 '.$redisk['count'].' 次请求'];
        if($redisk['count'] > $redisConfig['limit']){die(json_encode(['code'=>201,'msg'=>'请求太频繁，请 '.$redisk['ttl'].' 秒后再试','ip'=>$redisConfig['ip'],'UA'=>$_SERVER['HTTP_USER_AGENT']], 456));exit;}
    }
}
define('HCFILE',__DIR__.$mulu);
$mulu=str_replace($_SERVER["DOCUMENT_ROOT"],"",HCFILE);
$MD5 = Md5($m3u8Url).'.m3u8';
$filename = HCFILE.$MD5;
if(is_dir(HCFILE) && file_exists($filename)){
$arr=array('code'=>200,'msg'=>'成功解析','domain'=>'极光去广告源码下载bigon.cn','type'=>'m3u8','url'=>HTTP_HOST().$mulu.$MD5,'ip'=>$redisConfig['ip']);
if(isset($redisk_count)){$arr=array_merge($arr,$redisk_count);}die(json_encode($arr, 456));
}else {
if (!file_exists(HCFILE)) {//判断目录是否存在，不存在则创建
mkdir(HCFILE, 0777, true);//设置目录权限为777
}
if(is_exist($m3u8Url,$jggz) !== false){
    $fullPath = curl($m3u8Url);
    $tsFullPath = dirname($fullPath)."/";
    $response2 = jgcurl($fullPath);
}
if(empty($response2) || strpos($response2,'404 Not Found') !== false || strpos($response2,'html') !== false){$arr = array("code" => 403,"msg" => "视频数据异常，返回错误！","title" => "Bigon_Cn");die(json_encode($arr, 456));exit;}
$tsContent = JGTXT($response2,$tsFullPath,$sts);file_put_contents($filename, $tsContent);
$arr=array('code'=>200,'msg'=>'成功解析','domain'=>'极光去广告源码下载bigon.cn','type'=>'m3u8','url'=>HTTP_HOST().$mulu.$MD5,'ip'=>$redisConfig['ip']);
if(isset($redisk_count)){$arr=array_merge($arr,$redisk_count);}die(json_encode($arr, 456));
}
?>
