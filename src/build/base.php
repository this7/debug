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
use Exception;

/**
 * 错误处理
 * Class Base
 *
 * @package
 */
class base {

    public $exception;

    public function bootstrap() {
        #执行错误提示函数
        set_error_handler([$this, 'error'], E_ALL);
        set_exception_handler([$this, 'exception']);
        register_shutdown_function([$this, 'fatalError']);
        #Debug大于所有
        if (DEBUG) {
            if (XDEBUG) {
                #报告所有错误
                error_reporting(E_ALL);
                #错误信息
                ini_set('display_errors', true);
                #php启动错误信息
                ini_set('display_startup_errors', true);
            } else {
                #报告运行时错误
                error_reporting(0);
                #错误信息
                ini_set('display_errors', false);
                #php关闭错误信息
                ini_set('display_startup_errors', false);
            }
        } else {
            error_reporting(0);
        }
    }

    /**
     * 自定义异常理
     *
     * @param $e
     */
    public function exception($e) {
        switch ($_GET['type']) {
        case 'api':
            ob_get_clean();
            $data = array(
                'code' => $e->getCode(),
                'msg'  => $e->getMessage() . " FILE:" . str_replace(ROOT_DIR, "", $e->getFile()) . '(' . $e->getLine() . ')',
            );
            header("content-type:application/json");
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
            exit();
            break;
        case 'dapi':
            $this->exception[] = $e;
            break;
        case 'app':
            require __DIR__ . '/exception.php';
            break;
        }
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
        try {
            throw new MyException($error, $errno, $file, $line);
        } catch (MyException $e) {
            ERRORCODE($e);
        }
    }

    /**
     * 致命错误处理
     * @Author   Sean       Yan
     * @DateTime 2018-09-18
     * @return   [type]     [description]
     */
    public function fatalError() {
        if (function_exists('error_get_last')) {
            if ($e = error_get_last()) {
                try {
                    throw new MyException($e['message'], $e['type'], $e['file'], $e['line']);
                } catch (MyException $e) {
                    ERRORCODE($e);
                }
            }
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
        P($object);
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

    public function __destruct() {
        if (!empty($this->exception)) {
            P($this->exception);
        }
    }

}