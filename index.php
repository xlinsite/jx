<?php
error_reporting(0);
require_once('config.php');
header('content-type:application/json');// 表明为json接口
define('HC_',false);hcon(HC_);//redis开启=true 关闭=false
//注意：关闭redis 就无法限制访问次数，限制访问次数功能失效。
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
$lzgz = [//量子特征码
    'lz-',
    'lzcdn',
    'cdnlz',
    ];
$ffgz = [//非凡特征码
    'ffzy',
    ];
$bfgz = [//暴风特征码
    'bfzy',
    ];
$m3u8Url = $_GET['url'];
if(empty($m3u8Url)){die('请输入量子、非凡、暴风 链接地址');exit;}
//****************************************************//
if(is_exist($m3u8Url,$lzgz) !== false){$mulu="/LZ/";}//量子缓存目录
if(is_exist($m3u8Url,$ffgz) !== false){$mulu="/Ff/";}//非凡缓存目录
if(is_exist($m3u8Url,$bfgz) !== false){$mulu="/Bf/";}//暴风缓存目录
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
$arr=array('code'=>200,'msg'=>'成功解析','domain'=>'量子非凡暴风去广告源码下载bigon.cn','type'=>'m3u8','url'=>HTTP_HOST().$mulu.$MD5,'ip'=>$redisConfig['ip']);
if(isset($redisk_count)){$arr=array_merge($arr,$redisk_count);}die(json_encode($arr, 456));
}else {
if (!file_exists(HCFILE)) {//判断目录是否存在，不存在则创建
mkdir(HCFILE, 0777, true);//设置目录权限为777
}
if(is_exist($m3u8Url,$bfgz) !== false){$tsFullPath = dirname($m3u8Url)."/";$fullPath = $m3u8Url;}else{
$tsUrl = curl($m3u8Url);
$startPath = dirname($m3u8Url)."/";
$tsM3u8Url = str_replace("\\", "/", $tsUrl);
$fullPath = $startPath.$tsM3u8Url;
$tsFullPath = dirname($fullPath)."/";
}
$response2 = curlts($fullPath);
if(empty($response2) || strpos($response2,'404 Not Found') !== false || strpos($response2,'html') !== false){$arr = array("code" => 403,"msg" => "视频数据异常，请稍后尝试！","title" => "Bigon_Cn");die(json_encode($arr, 456));exit;}
$tsContent = txt($response2,$tsFullPath);
$tsContent = str_replace('"index.key"','"'.$tsFullPath.'index.key"',$tsContent);
file_put_contents($filename, $tsContent);
$arr=array('code'=>200,'msg'=>'成功解析','domain'=>'量子非凡暴风去广告源码下载bigon.cn','type'=>'m3u8','url'=>HTTP_HOST().$mulu.$MD5,'ip'=>$redisConfig['ip']);
if(isset($redisk_count)){$arr=array_merge($arr,$redisk_count);}
die(json_encode($arr, 456));
}
?>