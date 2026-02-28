@echo off
echo ========================================
echo PHP GD 扩展启用脚本
echo ========================================
echo.

set PHP_INI=D:\php7\php.ini

echo 正在检查 php.ini 文件...
if not exist "%PHP_INI%" (
    echo 错误: 找不到 php.ini 文件: %PHP_INI%
    pause
    exit /b 1
)

echo 找到 php.ini: %PHP_INI%
echo.

echo 正在备份 php.ini...
copy "%PHP_INI%" "%PHP_INI%.backup_%date:~0,4%%date:~5,2%%date:~8,2%_%time:~0,2%%time:~3,2%%time:~6,2%" >nul
echo 备份完成: %PHP_INI%.backup_%date:~0,4%%date:~5,2%%date:~8,2%_%time:~0,2%%time:~3,2%%time:~6,2%
echo.

echo 正在启用 GD 扩展...
echo 请手动编辑 php.ini 文件，找到以下行并取消注释:
echo.
echo   ;extension=gd
echo.
echo 改为:
echo.
echo   extension=gd
echo.
echo 或者如果是 PHP 7.x，可能是:
echo.
echo   ;extension=php_gd2.dll
echo.
echo 改为:
echo.
echo   extension=php_gd2.dll
echo.
echo ========================================
echo 按任意键打开 php.ini 文件进行编辑...
pause >nul

notepad "%PHP_INI%"

echo.
echo ========================================
echo 编辑完成后，请重启 PHP 服务或 Web 服务器
echo ========================================
echo.
echo 如果使用 Apache: 重启 Apache 服务
echo 如果使用 Nginx + PHP-FPM: 重启 PHP-FPM 服务
echo 如果使用内置服务器: 重启 PHP 内置服务器
echo.
pause
