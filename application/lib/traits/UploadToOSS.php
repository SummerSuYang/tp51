<?php
/*
|--------------------------------------------------------------------------
| Creator: SuYang
| Date: 2018/3/27 0027 11:07
|--------------------------------------------------------------------------
|                                             上传到阿里OSS
|--------------------------------------------------------------------------
*/

namespace app\lib\traits;

use OSS\Core\OssException;
use OSS\OssClient;

trait UploadToOSS
{
    //bucket
    private $bucket;
    //oss client
    private $OSSClient;
    //上传的文件在bucket中的文件夹路径
    private $OSSPath;
    //上传方式 1公有的，直接返回永久的url ；传其他值为私有的返回临时url
    private $uploadType;

    /**
     * @param int $type
     * @return $this
     * type等于1的时候是公有上传等于其他为私有上传
     */
    public function initialOSSClient($uploadType = 1, $fileType = 1)
    {
        $this->OSSClient = new OssClient(
            $this->getOSSAccessKeyId(),
            $this->getOSSAccessKeySecret(),
            $this->getOSSEndPoint());

        $this->uploadType = $uploadType;

        return $this;
    }

    /**
     * @param $info
     * @param bool $deleteLocalFile
     * @return mixed|string
     * @throws OssException
     */
    public function uploadToOSS($info, $deleteLocalFile = true)
    {
        //info是一个数组，包括六个 $k=>$v
        //name 是 上传文件的原始的名字
        //type是类型（并不是扩展名）
        //tmp_name（临时路径）
        //size 大小
        //error（这个暂时没有用不是到干什么的）
        //extension 文件扩展名
        $object = $this->getOSSObjectPath($info['extension']);
        try{
            if($this->uploadType === 1){
                //公有方式上传
                $result = $this->uploadToOSSPublic($info['tmp_name'], $object);
            }

            else{
                //私有方式上传
                $result = $this->uploadToOSSPrivate($info['tmp_name'], $object);
            }
            //删除本地文件
            if($deleteLocalFile) $this->deleteLocalFile($info['tmp_name']);

            return $result;
        }catch (OssException $e){
            throw $e;
        }
    }

    /**
     * @param $localPath
     * @return mixed
     * 公有方式上传
     */
    protected function uploadToOSSPublic($localPath, $object)
    {
        $return = $this->OSSClient->uploadFile($this->bucket, $object, $localPath);
        return $return['info']['url'];
    }

    /**
     * @param $localPath
     * @param $object
     * @return string
     */
    protected function uploadToOSSPrivate($localPath, $object)
    {
        return '';
    }

    /**
     * @param $extension
     * @return string
     */
    public function getOSSObjectPath($extension, $name = '')
    {
        if(!empty($name)){
            $object = $this->OSSPath.'/'.$name;
            //判断当前的bucket中有没有已经存在的object
            if($this->OSSClient->doesObjectExist($this->bucket, $object)){
                $name = $this->OSSUnique().'.'.$extension;
            }
        }
        else {
            $name = $this->OSSUnique().'.'.$extension;
        }
        return $this->OSSPath.'/'.$name;
    }

    public function OSSUnique()
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
     * @param $bucket
     * @return $this
     */
    public function setOSSBucket($bucket)
    {
        $this->bucket = $bucket;
        return $this;
    }

    /**
     * @param $path
     * @return $this
     */
    public function setOSSPath($path)
    {
        $this->OSSPath = $path;
        return $this;
    }

    /**
     * @return mixed
     */
    protected function getOSSEndPoint()
    {
        return config('oss.endpoint');
    }

    /**
     * @return mixed
     */
    protected function getOSSAccessKeyId()
    {
        return config('oss.accessKeyId');
    }

    /**
     * @return mixed
     */
    protected function getOSSAccessKeySecret()
    {
        return config('oss.accessKeySecret');
    }

    public function createOSSBucket($bucket)
    {
        return $this->OSSClient->createBucket($bucket, 'public-read-write');
    }


}