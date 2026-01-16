<?php
declare (strict_types=1);
namespace app\common\library;
/**
 *压缩文件类 
 */
class FileZip
{
    // 整理数据
    private static function parseData($data){
        $new = [];
        foreach ($data as $item){
            $new[$item['shelf_unit_code']] = $item['shelf_unit_qrcode'];
        }
        return $new;
    }
    
    public static function init($data){
         $data = self::parseData($data);
         $filename = 'uploads/temp/'.date("Ymdhis").'.zip';
         $zip = new \ZipArchive();
         $res = $zip->open($filename,\ZipArchive::CREATE);
         foreach($data as $k => $file){
            $zip->addFile($file,$k.".png");  //向压缩包中添加文件
         }
         $zip->close(); //关闭
         return 'https://'.$_SERVER['HTTP_HOST'].'/'.$filename;
    }
}