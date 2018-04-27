<?php
/*
|--------------------------------------------------------------------------
| Creator: SuYang
| Date: 2018/4/11 0011 13:51
|--------------------------------------------------------------------------
|                                            阿里云发送短信
|--------------------------------------------------------------------------
*/

namespace app\lib\traits;

use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Core\Exception\ClientException;
use app\lib\exception\CacheException;
use app\lib\exception\SmsException;
use Aliyun\Core\Profile\DefaultProfile;
use think\Config;
use think\facade\Cache;
use think\facade\Log;
use think\facade\Request;

// 加载区域结点配置
\Aliyun\Core\Config::load();

trait AliMsg
{
    private $msgKeyId;
    private $msgKeySecret;
    private $msgSignName;
    private $msgExpiresIn;
    private $msgResendTime;

    private function msgInitial()
    {
        $this->msgKeyId = config('aliMsg.msgKeyId');

        $this->msgKeySecret = config('aliMsg.msgKeySecret');

        $this->msgSignName = config('aliMsg.msgSignName');

        $this->msgExpiresIn = config('aliMsg.msgExpiresIn');

        $this->msgResendTime = config('aliMsg.msgResendTime');

        // 短信API产品名
        $product = "Dysmsapi";

        // 短信API产品域名
        $domain = "dysmsapi.aliyuncs.com";

        // 暂时不支持多Region
        $region = "cn-hangzhou";

        // 服务结点
        $endPointName = "cn-hangzhou";

        // 初始化用户Profile实例
        $profile = DefaultProfile::getProfile($region, $this->msgKeyId, $this->msgKeySecret);

        // 增加服务结点
        DefaultProfile::addEndpoint($endPointName, $region, $product, $domain);

        // 初始化AcsClient用于发起请求
        $this->acsClient = new DefaultAcsClient($profile);

        return $this;
    }

    public function msgSend($templateCode, $phoneNumbers, $templateParam = null, $outId = null)
    {
        $this->msgInitial()->msgVerifyFrequency($phoneNumbers);

        try {
            $request = new SendSmsRequest();
            /*签名名称*/
            $request->setSignName($this->msgSignName);
            /*模板code*/
            $request->setTemplateCode($templateCode);
            /*目标手机号*/
            //$request->setRecNum($phoneNumbers);
            $request->setPhoneNumbers($phoneNumbers);
            /*模板变量*/
            //$request->setParamString(json_encode($templateParam));
            $request->setTemplateParam(json_encode($templateParam));

            // 发起访问请求
            $acsResponse = $this->acsClient->getAcsResponse($request);
            if($acsResponse->Code != 'OK'){
                $info = "RequestId:".$acsResponse->RequestId;
                $info.=" Code:".$acsResponse->Code;
                $info.=" Phone:".$phoneNumbers." Msg:".$acsResponse->Message;

                Log::write($info,'debug');
                throw new SmsException([
                    'code' => $acsResponse->Code,
                    'msg' => $acsResponse->Message
                ]);
            }

            $this->msgCacheCode($phoneNumbers, $templateParam['code']);
        } catch (ClientException $e) {
            throw new SmsException([
                'code' => $e->getErrorCode(),
                'msg' => $e->getErrorMessage()
            ]);
        } catch (\ServerException $e) {
            throw new SmsException([
                'code' => $e->getErrorCode(),
                'msg' => $e->getErrorMessage()
            ]);
        }
    }

    /**
     * 生成短信码,默认6位
     */
    protected function msgCreateCode($length = 6)
    {
        $verifyCode = '';
        mt_srand(( double )microtime() * 1000000);
        for ($i = 0; $i < $length; $i++) {
            $verifyCode .= mt_rand(0, 9);
        }
        return $verifyCode;
    }

    /**
     * @param $mobile
     * @param $code
     * @throws CacheException
     * 缓存code
     */
    protected function msgCacheCode($mobile, $code)
    {
        $cacheData = serialize(['code' => $code, 'time' => Request::time()]);
        $result = Cache::set($this->msgCacheKey($mobile), $cacheData, $this->msgExpiresIn);
        if (!$result) {
            throw new CacheException(30001);
        }
    }

    /**
     * @param $mobile
     * @param $code
     * @return bool
     * @throws SmsException
     * 检验code
     */
    public function msgVerifyCode($mobile, $code)
    {
        $cacheKey = $this->msgCacheKey($mobile);

        if( !Cache::has($cacheKey)) {
            throw new SmsException(29001);
        }

        $cacheDate = unserialize(Cache::get($cacheKey));

        if(!is_array($cacheDate)) {
            throw new SmsException(29003);
        }

        if($cacheDate['code'] != $code) {
            throw new SmsException(29002);
        }

        Cache::rm($cacheKey);

        return true;
    }

    /**
     * @param $mobile
     * @return bool
     * @throws SmsException
     * 检查发送频率
     */
    public function msgVerifyFrequency($mobile)
    {
        $cacheKey = $this->msgCacheKey($mobile);

        if( !Cache::has($cacheKey)) {
            return true;
        }

        $cacheDate = unserialize(Cache::get($cacheKey));

        if(!is_array($cacheDate)) {
            throw new SmsException(29003);
        }

        if((Request::time() - $cacheDate['time']) <= $this->msgResendTime){
            throw new SmsException(29004);
        }

        return true;
    }

    /**
     * @param $mobile
     * @param string $prefix
     * @return string
     * 缓存的key
     */
    public function msgCacheKey($mobile, $prefix = 'sendSms')
    {
        return md5($prefix.$mobile);
    }
}