<?php
/**
 * this7 PHP Framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright 2016-2018 Yan TianZeng<qinuoyun@qq.com>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://www.ub-7.com
 */
namespace this7\debug\build;

/**
 * 错误处理
 * Class Base
 *
 * @package
 */
class base {
    public $is_debug = true;
    /**
     * 启动处理
     */
    public function bootstrap() {
        $this->is_debug = true;
        #设置DEBUG错误
        $debug = is_boolean(($_GET['type'] == 'api'), true) . is_boolean(DEBUG, true) . is_boolean(XDEBUG, true);
        switch ($debug) {
        case '011':
            #报告所有错误
            error_reporting(E_ALL);
            #错误信息
            ini_set('display_errors', true);
            #php启动错误信息
            ini_set('display_startup_errors', true);
            break;
        case '010':
        case '001':
            #报告运行时错误
            error_reporting(0);
            #错误信息
            ini_set('display_errors', false);
            #php关闭错误信息
            ini_set('display_startup_errors', false);
            #执行错误提示函数
            set_error_handler([$this, 'error'], E_ALL);
            set_exception_handler([$this, 'exception']);
            register_shutdown_function([$this, 'fatalError']);
            break;
        case '000':
        case '111':
        case '110':
        case '101':
        case '100':
            $this->is_debug = false;
            #禁用错误报告
            error_reporting(0);
            break;
        }
    }

    /**
     * 显示调试界面
     * @Author   Sean       Yan
     * @DateTime 2018-09-03
     * @param    array      $object 数据对象
     * @return   HTML               调试界面
     */
    public function display($object = array()) {
        if ($this->is_debug) {
            extract(static_storage());
            echo isset($object['model']) && $object['model'] == 'api' ? '<!doctype html><html lang="zh"><head><meta charset="UTF - 8"><title> This7框架Debug调试器</title></head><body>' : '';
            require __DIR__ . '/debug.php';
            echo isset($object['model']) && $object['model'] == 'api' ? '</body></html>' : '';
        }

    }

    /**
     * 自定义异常理
     *
     * @param $e
     */
    public function exception($e) {
        //命令行错误
        logger::writeLog('EXCEPTION', $e->getMessage() . " FILE:" . $e->getFile() . '(' . $e->getLine() . ')');
        if (DEBUG == true) {
            require __DIR__ . '/exception.php';
        } else {
            $this->closeDebugShowError($e->getMessage());
        }
        exit;
    }

    /**
     * 关闭调试模式时显示错误
     * @param string $msg
     */
    protected function closeDebugShowError($msg = '系统错误，请稍候访问') {
        if (!$this->is_debug) {
            echo to_json(['msg' => $msg, 'code' => 1]);
        } else {
            require C("debug", "error");
        }
        die;
    }

    /**
     * 错误处理
     *
     * @param $errno
     * @param $error
     * @param $file
     * @param $line
     */
    public function error($errno, $error, $file, $line) {
        $msg = $error . "($errno)" . $file . " ($line).";
        //命令行错误
        switch ($errno) {
        case E_USER_NOTICE:
        case E_DEPRECATED:
            break;
        case E_NOTICE:
            if (DEBUG == true && C('debug', 'notice')) {
                require __DIR__ . '/notice.php';
            }
            break;
        case E_WARNING:
            logger::writeLog($this->errorType($errno), $msg);
            if (DEBUG == true) {
                require __DIR__ . '/debug.php';
            } else {
                $this->closeDebugShowError($error);
            }
            exit;
        default:
            logger::writeLog($this->errorType($errno), $msg);
            if (DEBUG == true) {
                require __DIR__ . '/debug.php';
            } else {
                $this->closeDebugShowError($error);
            }
            exit;
        }

    }

    //致命错误处理
    public function fatalError() {
        if (function_exists('error_get_last')) {
            if ($e = error_get_last()) {
                require __DIR__ . '/fatal.php';
                die;
            }
        }
    }

    /**
     * 获取错误标识
     *
     * @param $type
     *
     * @return string
     */
    public function errorType($type) {
        switch ($type) {
        case E_ERROR: // 1 //
            return 'ERROR';
        case E_WARNING: // 2 //
            return 'WARNING';
        case E_PARSE: // 4 //
            return 'PARSE';
        case E_NOTICE: // 8 //
            return 'NOTICE';
        case E_CORE_ERROR: // 16 //
            return 'CORE_ERROR';
        case E_CORE_WARNING: // 32 //
            return 'CORE_WARNING';
        case E_COMPILE_ERROR: // 64 //
            return 'COMPILE_ERROR';
        case E_COMPILE_WARNING: // 128 //
            return 'COMPILE_WARNING';
        case E_USER_ERROR: // 256 //
            return 'USER_ERROR';
        case E_USER_WARNING: // 512 //
            return 'USER_WARNING';
        case E_USER_NOTICE: // 1024 //
            return 'USER_NOTICE';
        case E_STRICT: // 2048 //
            return 'STRICT';
        case E_RECOVERABLE_ERROR: // 4096 //
            return 'RECOVERABLE_ERROR';
        case E_DEPRECATED: // 8192 //
            return 'DEPRECATED';
        case E_USER_DEPRECATED: // 16384 //
            return 'USER_DEPRECATED';
        }

        return $type;
    }

    /**
     * trace 信息
     *
     * @param  string  $value  变量
     * @param  string  $label  标签
     * @param  string  $level  日志级别(或者页面Trace的选项卡)
     * @param  boolean $record 是否记录日志
     *
     * @return void|array
     */
    public function trace($value = '[hdphp]', $label = '', $level = 'DEBUG', $record = false) {
        static $trace = [];

        if ('[hdphp]' == $value) {
            // 获取trace信息
            return $trace;
        } else {
            $info  = ($label ? $label . ':' : '') . print_r($value, true);
            $level = strtoupper($level);
            if (IS_AJAX || $record) {
                logger::record($info, $level);
            } else {
                if (!isset($trace[$level])) {
                    $trace[$level] = [];
                }
                $trace[$level][] = $info;
            }
        }
    }

}