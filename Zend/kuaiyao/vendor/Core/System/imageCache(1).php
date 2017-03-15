<?php
namespace Core\System;

class imageCache{
    public $getId;
    public $Ids;


  /*  public function ImgCache($Img=null){
        $path=APP_PATH.'/Cache/Img';
        $file=APP_PATH.'/Cache/Img/img.json';
        if(!is_dir($path))
        {
            mkdir($path,0777);
        }
        if(file_exists($file)== false)
        {
            $file=fopen(APP_PATH.'/Cache/Img/img.json',"w");
            fclose($file);
        }
        $ids=file_get_contents(APP_PATH.'/Cache/Img/img.json');
        $result=json_decode($ids);
        if($Img)
        {
            file_put_contents(APP_PATH.'/Cache/Img/img.json',$Img);
        }
        return $result;
    }

}*/
    public $timestampLeast;
    public function setCache($filename, $param, $type, $cache_time = 5)
    {
        if($type==1)
        {
            //�ļ�����
            $filename = $this->getCacheFilename($filename);

            if ($param)
            {
                if (! is_file($filename))
                {
                    @touch($filename);
                    @chmod($filename, 0777);
                }

                if (! is_array($param))
                {
                    $param = array(
                        $param
                    );
                }
                $data = json_encode($param);
    //          @file_put_contents($filename, $data);
                $file = new File();
                $file->mkFile($filename,$data,true);
            }
            elseif(is_file($filename))
            {
                @unlink($filename);
            }
            return true;
        }
        else
        {
            //�ڴ滺��
            if(! IS_MEMCACHE)
            {
                return false;
            }

            $param['cache_timestamp'] = $this->getTime();
            if($this->memcache->set($filename, $param,false,$cache_time))
            {
                return 2;
            }

        }
        return false;
    }
    
    public function getTimestampLeast()
    {
        return $this->timestampLeast ? $this->timestampLeast : '0000-00-00 00:00:00';
    }
    
    public function getCache($filename,$type,$check = true)
    {
         $timestampLeast = $this->getTimestampLeast();//APP提交的缓存时间 
        if($type==1)
        {
            //文件缓存
            $filename = $this->getCacheFilename($filename);
            if (! is_file($filename))
            {
                return false;
            }
             
            $ctime = filemtime($filename); // 缓存更新时间
            if (! $ctime)
            {
                return false;
            }
            if (strtotime($timestampLeast) >= $ctime && $check)
            {
                // 缓存时间大于文件生成时间就不用返回整个列表啦
                $this->response(STATUS_CACHE_AVAILABLE); // 1020 缓存数据可用
            }else{
    
                $data = file_get_contents($filename);
                if ($data)
                {
                    $param = json_decode($data, true);
    
                    return $param;
                }
            }
        }
        elseif($type==2)
        {
            //memcache 缓存
            if(! IS_MEMCACHE)
            {
                return false;
            }
    
            $data = @$this->memcache->get($filename);
            if(!$data)
            {
                //没找到缓存返回false
                return false;
            }
            else
            {
                 
                if($timestampLeast>=$data['cache_timestamp'] && $check)
                {
                    // 缓存时间大于文件生成时间就不用返回整个列表啦
                    $this->response(STATUS_CACHE_AVAILABLE); // 1020 缓存数据可用
                }
                else
                {
                    unset($data['cache_timestamp']);
                    return $data;
                }
            }
             
        }
        return false;
    }
    
    
    public function getCacheFilename($filename)
    {
        return APP_PATH . '/Cache/' . $filename . '';
    }
    
   
    
}


?>