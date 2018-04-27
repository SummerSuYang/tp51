<?php
/*
|--------------------------------------------------------------------------
| Creator: SuYang
| Date: 2018/3/27 0027 11:07
|--------------------------------------------------------------------------
|                              公有方式上传到阿里OSS,URL永久有效
|--------------------------------------------------------------------------
*/

namespace app\lib\traits;

use app\lib\exception\UploadFileException;
use OSS\Core\OssException;
use OSS\OssClient;
use think\facade\Request;

trait UploadToOSSPublic
{
    //bucket
    private $OSSBucket;
    //oss client
    private $OSSClient;
    //上传文件的类型，1为图片，2为其他的文件
    private $uploadFileType;
    //不同文件类型对应的文件夹
    private $uploadTypeFolder = [
        1 => 'images',
        2 => 'files'
    ];

    //上传文件的大小,默认都为5M
    private $uploadMaxSize = [
        1 => 5242880,
        2 => 5242880
    ];
    //上传的应用场景
    private $OSSScene = 'default';

    /**
     * @param int $type
     * @return $this
     * type等于1的时候是上传图片，等于2是上传其他文件
     */
   protected function initialOSSClient($type = 1, $scene = '')
    {
        //检查type
        if(!in_array($type, array_keys($this->uploadFileType))){
            throw new UploadFileException(14001);
        }

        //文件类型
        $this->uploadFileType = $type;

        //设置应用场景
        if(!empty($scene)) $this->OSSScene = $scene;

        $this->OSSClient = new OssClient(
            $this->getOSSAccessKeyId(),
            $this->getOSSAccessKeySecret(),
            $this->getOSSEndPoint());

        return $this;
    }

    /**
     * @param string $scene
     * @return mixed
     * @throws UploadFileException
     * @throws \Exception
     * 上传
     */
    public function uploadToOSS($scene = '')
    {
        try{
            //文件类型
            $type = Request::param('type') ? : 1;

            //文件信息
            $info = $this->initialOSSClient($type, $scene)->checkUploadFile();

            $object = $this->getOSSObject($info);

            $return = $this->OSSClient->uploadFile($this->getOSSBucket(), $object, $info['tmp_name']);

            return $return['info']['url'];
        }catch (OssException $e){
            throw $e;
        }
    }

    /**
     * @return mixed
     * @throws UploadFileException
     * 检查上传的文件并返回文件信息
     */
    protected function checkUploadFile()
    {
        if(is_null($file = Request::file('file'))) {
            throw new UploadFileException(14001);
        }

        $info = $file->getInfo();

        //检查文件必须的信息
        if( !key_exists('name' , $info) || !key_exists('tmp_name', $info) ||
            !key_exists('size', $info)){
            throw new UploadFileException(14006);
        }


        //获取文件扩展名
        $info['extension'] = $this->getExtension($info);

        //检查大小
        if($info['size'] > $this->uploadMaxSize[$this->uploadFileType]) {
            throw new UploadFileException(14005);
        }

        /* info是一个数组，包括六个 $k=>$v
            name 是 上传文件的原始的名字
            type是类型（并不是扩展名）
            tmp_name（临时路径）
            size 大小
            error（这个暂时没有用不是到干什么的）
            extension 文件扩展名*/
        return $info;
    }

    /**
     * @param $extension
     * @return string
     * 获得object
     */
    public function getOSSObject($info)
    {
        //文件夹
        $folder = $this->uploadTypeFolder[$this->uploadFileType].'/';
        //如果是图片,就用一个随机名
        if($this->uploadFileType == 1){
            $object = $folder.$this->OSSUniqueName().'.'.$info['extension'];
        }
        //如果是文件，先判断文件是否存在，如果存在就使用随机名
        else{
            $object = $folder.$info['name'];
            if($this->OSSClient->doesObjectExist($this->getOSSBucket(), $object)){
                $object = $folder.$this->OSSUniqueName().'.'.$info['extension'];
            }
        }

        return $object;
    }

    /**
     * @return string
     * 随机获取一个唯一的字符串
     */
    public function OSSUniqueName()
    {
        return md5(microtime());
    }

    /**
     * @param $localPath
     * 上传完以后删除本地文件
     */
    public function deleteLocalFile($localPath)
    {
        if(file_exists($localPath))
            @unlink($localPath);
    }

    /**
     * @return mixed
     * @throws UploadFileException
     * 从配置中读取endpoint
     */
    protected function getOSSEndPoint()
    {
        if(empty($endpoint = config("oss".".$this->OSSScene."."endpoint"))){
            throw new UploadFileException(14002);
        }

        return $endpoint;
    }

    /**
     * @return mixed
     * @throws UploadFileException
     * 从配置中读取key Id
     */
    protected function getOSSAccessKeyId()
    {
        if(empty($key = config("oss".".$this->OSSScene."."accessKeyId"))) {
            throw new UploadFileException(14003);
        }

        return$key;
    }

    /**
     * @return mixed
     * @throws UploadFileException
     * 从配置中读取secret
     */
    protected function getOSSAccessKeySecret()
    {
        if(empty($secret = config("oss".".$this->OSSScene."."accessKeySecret"))) {
            throw new UploadFileException(14004);
        }
        return $secret;
    }

    /**
     * @param $bucket
     * @return mixed
     * 新建一个bucket
     */
    public function createOSSBucket($bucket)
    {
        return $this->OSSClient->createBucket($bucket, 'public-read-write');
    }

    /**
     * @return mixed
     * @throws UploadFileException
     * 读取配置中的bucket
     */
    protected function getOSSBucket()
    {
        if(empty($bucket = config("oss".".$this->OSSScene."."bucket"))) {
            throw new UploadFileException(14007);
        }

        if( !$this->OSSClient->doesBucketExist($bucket)){
            throw new UploadFileException(14008);
        }

        return $bucket;
    }

    /**
     * @param $fileName
     * @return string
     * 获得上传文件的扩展名
     */
    protected function getExtension($fileName)
    {
        return getExtension($fileName);
    }
}