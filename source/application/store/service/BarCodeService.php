<?php
namespace app\store\service;
// 条形码生成服务

class BarCodeService
{
    // 生成
    public function Generator($code){
        $generatorPNG = new \Picqer\Barcode\BarcodeGeneratorPNG();
        $blackColor = [0, 0, 0];
        $barCodeData = $generatorPNG->getBarcode($code, $generatorPNG::TYPE_CODE_128, 3, 50, $blackColor);
        return file_put_contents('barcode/'.$code.'.png',$barCodeData);
    }             
}
?>