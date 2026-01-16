<?php
declare (strict_types=1);

namespace app\api\service\user;

use app\common\service\Basics;
use app\api\model\Setting as SettingModel;
use app\api\model\UploadFile as UploadFileModel;
use app\api\model\User as UserModel;
use app\common\enum\file\FileType as FileTypeEnum;
use app\common\library\{Download, storage\Driver as Storage};
use cores\exception\BaseException;

/**
 * 服务类: 用户头像
 * Class Avatar
 * @package app\api\service\user
 */
class Avatar extends Basics
{
    // 存储配置
    private $config;

    /**
     * 构造函数
     * Avatar constructor.
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function __construct()
    {
        // parent::__construct();
        // 存储配置
        $this->config = SettingModel::getItem('storage');
    }

    /**
     * 下载第三方头像并返回文件记录ID
     * @param string $avatarUrl
     * @return int
     * @throws BaseException
     * @throws \think\Exception
     */
    public function party(string $avatarUrl): int
    {
        // 下载网络图片
        $filePath = $this->download($avatarUrl);
        // 上传到本地
        $fileInfo = $this->upload($filePath);
        // 新增文件记录
        return $this->record($fileInfo);
    }

    /**
     * 图片上传接口
     * @param string $filePath
     * @return false|mixed
     * @throws \think\Exception
     */
    public function upload(string $filePath = '')
    {
        // 实例化存储驱动
        $storage = new Storage($this->config);
        // 设置上传文件的信息
        $storage->setUploadFileByReal($filePath)
            ->setRootDirName((new UserModel)->getWxappid())
            ->setValidationScene('image')
            ->upload();
        // 执行文件上传
        if (!$storage->upload()) {
            throwError('图片上传失败：' . $storage->getError());
        }
        // 文件信息
        return $storage->getFileInfo();
    }

    /**
     * 添加文件库记录
     * @param array $fileInfo
     * @return int
     */
    private function record(array $fileInfo): int
    {
        $model = new UploadFileModel;
        $fileInfos['file_name'] = $fileInfo['name'];
        $fileInfos['storage'] = 'local';
        $fileInfos['file_size'] = $fileInfo['size'];
        $fileInfos['file_type'] = 'image';
        $fileInfos['extension'] = 'png';
        $model->add($fileInfos);
        return (int)$model['file_id'];
    }

    /**
     * 下载网络图片
     * @param string $avatarUrl
     * @return string
     * @throws BaseException
     */
    private function download(string $avatarUrl): string
    {
        $Download = new Download;
        return $Download->saveTempImage((int)(new UserModel)->getWxappid(), $avatarUrl, 'avatar');
    }
}