<?php 
namespace app\common\service;

class QrcodeService {
    
    public $engine = 'phpQrcode'; 
    
    public $outPut = 'uploads/qrcode/'; // 输出文件夹
    
    public function create($text){
        // dump($text);die;
        if ($this->engine=='phpQrcode'){
            $rout1 = $this->createQrcodeByQr($text);
            $rout2 = $this->makeImgWithStr($text,30);
            $this->CompositeImage([$rout1,$rout2],$rout1);
            
            return $rout1;
        }
        if ($this->engine=='barcode'){
            
        }
    }    
    
    public function createQrcodeByQr($text){
        // dump($text);die;
        require_once APP_PATH.'/common/library/phpqrcode/phpqrcode.php';
        $data = $text;//内容
        $level = 'L';// 纠错级别：L、M、Q、H
        $size = 10;//元素尺寸
        $margin = 1;//边距
        $outfile = $this->outPut.date("YmdHis").rand(000000,999999).'.png';

        $saveandprint = false;// true直接输出屏幕  false 保存到文件中
        $back_color = 0xFFFFFF;//白色底色
        $fore_color = 0x000000;//黑色二维码色 若传参数要hexdec处理，如 $fore_color = str_replace('#','0x',$fore_color); $fore_color = hexdec('0xCCCCCC');
        
        $QRcode = new \QRcode();
        
        //生成png图片
        $QRcode->png($data, $outfile, $level, $size, $margin, false, $back_color, $fore_color);
//         $product_sn1 = substr($text,0,2);
//         $product_sn = $product_sn1.'-'.$product_sn2;
        return $outfile;
    }
    
    //文字生成图片
	public function makeImgWithStr($text, $font_size=20,$font = 'assets/common/fonts/verdanab.ttf')
	{
	   // dump($text);
		//图片尺寸
		$im = imagecreatetruecolor(444, 70);
		//背景色
		$white = imagecolorallocate($im, 255, 255, 255);
		//字体颜色
		$black = imagecolorallocate($im, 0, 0, 0);
        // $product_sn1 = substr($text,0,2);
        // $product_sn2 = substr($text,2,2);
        $product_sn = $text;
		imagefilledrectangle($im, 0, 0, 444, 300, $white);
		$txt_max_width = intval(0.8 * 444);
		$content = "";
		for ($i = 0; $i < mb_strlen($product_sn); $i++) {
			$letter[] = mb_substr($product_sn, $i, 1);
		}
		foreach ($letter as $l) {
			$test_str = $content . " " . $l;
			$test_box = imagettfbbox($font_size, 0, $font, $test_str);
			// 判断拼接后的字符串是否超过预设的宽度。超出宽度添加换行
			if (($test_box[2] > $txt_max_width) && ($content !== "")) {
				$content .= "\n";
			}
			$content .= $l;
		}

		$txt_width = $test_box[2] - $test_box[0];

		$y = 70 * 0.6; // 文字从何处的高度开始
		$x = (230 - $txt_width) / 4; //文字居中
		// echo $x;die;
		//文字写入
		imagettftext($im, $font_size, 0, $x, $y, $black, $font, $content); //写 TTF 文字到图中
		//图片保存
		$outfile = $this->outPut.date("YmdHis").rand(000000,999999).'.jpg';
		imagejpeg($im, $outfile);
		return $outfile;
	}
	
	/**
	 * 合并图片,拼接合并
	 * @param array $image_path 需要合成的图片数组
	 * @param $save_path 合成后图片保存路径
	 * @param string $axis 合成方向
	 * @param string $save_type 合成后图片保存类型
	 * @return bool|array
	 */
	public function CompositeImage(array $image_path, $save_path, $axis = 'y', $save_type = 'png')
	{
		if (count($image_path) < 2) {
			return false;
		}
		//定义一个图片对象数组
		$image_obj = [];
		//获取图片信息
		$width = 0;
		$height = 0;
// 		dump($image_path);die;
		foreach ($image_path as $k => $v) {
			$pic_info = getimagesize($v);
			list($mime, $type) = explode('/', $pic_info['mime']);
			//获取宽高度
			$width += $pic_info[0];
			$height += $pic_info[1];
			if ($type == 'jpeg') {
				$image_obj[] = imagecreatefromjpeg($v);
			} elseif ($type == 'png') {
				$image_obj[] = imagecreatefrompng($v);
			} else {
				$image_obj[] = imagecreatefromgif($v);
			}
		}
		//按轴生成画布方向
		if ($axis == 'x') {
			//TODO X轴无缝合成时请保证所有图片高度相同
			$img = imageCreatetruecolor($width, imagesy($image_obj[0]));
		} else {
			//TODO Y轴无缝合成时请保证所有图片宽度相同
			$img = imageCreatetruecolor(imagesx($image_obj[0]), $height);
		}
		//创建画布颜色
		$color = imagecolorallocate($img, 255, 255, 255);
		imagefill($image_obj[0], 0, 0, $color);
		//创建画布
		imageColorTransparent($img, $color);
		imagecopyresampled($img, $image_obj[0], 0, 0, 0, 0, imagesx($image_obj[0]), imagesy($image_obj[0]), imagesx($image_obj[0]), imagesy($image_obj[0]));
		$yx = imagesx($image_obj[0]);
		$x = 0;
		$yy = imagesy($image_obj[0]);
		$y = 0;
		//循环生成图片
		for ($i = 1; $i <= count($image_obj) - 1; $i++) {
			if ($axis == 'x') {
				$x = $x + $yx;
				imagecopymerge($img, $image_obj[$i], $x, 0, 0, 0, imagesx($image_obj[$i]), imagesy($image_obj[$i]), 100);
			} else {
				$y = $y + $yy;
				imagecopymerge($img, $image_obj[$i], 0, $y, 0, 0, imagesx($image_obj[$i]), imagesy($image_obj[$i]), 100);
			}
		}
		//设置合成后图片保存类型
		if ($save_type == 'png') {
			imagepng($img, $save_path);
		} elseif ($save_type == 'jpg' || $save_type == 'jpeg') {
			imagejpeg($img, $save_path);
		} else {
			imagegif($img, $save_path);
		}
		return true;


	}

    
}