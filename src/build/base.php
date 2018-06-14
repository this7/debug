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
    /**
     * 启动处理
     */
    public function bootstrap() {
        set_error_handler([$this, 'error'], E_ALL);
        set_exception_handler([$this, 'exception']);
        register_shutdown_function([$this, 'fatalError']);
    }

    /**
     * 自定义异常理(PHP>7.0)
     *
     * @param $e
     */
    public function exception($e) {
        if (DEBUG) {
            $trace_list = $this->stackTrace($e->getTrace());
            $this->error($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine(), $trace_list);
        } else {
            $this->closeDebugShowError($e->getMessage(), $e->getCode());
        }
        exit;
    }

    /**
     * 致命错误处理(PHP≤5.6)
     * @return [type] [description]
     */
    public function fatalError() {
        if (function_exists('error_get_last')) {
            $e = error_get_last();
            if (DEBUG) {
                $error_trace = $this->stackTrace();
                $this->error($e['type'], $e['message'], $e['file'], $e['line'], $error_trace);
            } else {
                $this->closeDebugShowError($e['message'], $e['type']);
            }
            exit;
        }
    }

    /**
     * 堆栈跟踪
     * @return [type]        [description]
     */
    public function stackTrace($data = '') {
        $error_array = [];
        if ($data) {
            foreach ($data as $key => $value) {
                $error_array[$key]['file']    = isset($value['file']) ? $value['file'] : $value['args'][0];
                $error_array[$key]['line']    = isset($value['line']) ? $value['line'] : 0;
                $class                        = isset($value['class']) ? $value['class'] : '';
                $type                         = isset($value['type']) ? $value['type'] : '';
                $function                     = isset($value['function']) ? $value['function'] . '()' : '';
                $error_array[$key]['message'] = $class . $type . $function;
            }
        } else {
            $error_log   = ROOT_DIR . DS . 'temp/debug/error_log.txt';
            $error_trace = file_get_contents($error_log);
            $error_list  = explode(PHP_EOL, $error_trace);
            if (stripos($error_trace, "Stack trace")) {
                foreach ($error_list as $key => $value) {
                    if ($key > 1) {
                        $line_list = explode(' ', $value);
                        if (count($line_list) == 8) {
                            $line                         = strripos($line_list[7], ":");
                            $error_array[$key]['message'] = $line_list[6];
                            $error_array[$key]['file']    = substr($line_list[7], 0, $line);
                            $error_array[$key]['line']    = substr($line_list[7], $line + 1);
                        }
                        if (count($line_list) == 9) {
                            $line                         = strripos($line_list[8], ":");
                            $error_array[$key]['message'] = $line_list[7];
                            $error_array[$key]['file']    = substr($line_list[8], 0, $line);
                            $error_array[$key]['line']    = substr($line_list[8], $line + 1);
                        }
                    }
                }
            }
            file_put_contents($error_log, '');
        }
        return array_values($error_array);
    }

    /**
     * 错误处理
     *
     * @param $errno
     * @param $error
     * @param $file
     * @param $line
     */
    public function error($errno, $error, $file, $line, $traces = '') {
        if (empty($error)) {
            return;
        }
        $title   = $this->errorType($errno);
        $content = $error . '在文件[' . addslashes($file) . ']第 ' . $line . ' 行';
        switch ($errno) {
        case 0;
        case 1;
        case 4;
        case 16;
        case 64;
        case 256;
            $type = 'error';
            break;
        case 2;
        case 32;
        case 128;
        case 512;
        case 2048;
            $type = 'warn';
            break;
        case 8;
        case 1024;
        case 4096;
        case 8192;
        case 16384;
        default:
            $type = 'info';
            break;
        }
        #判断是否API请求
        if (IS_API) {
            $array = array('code' => '900', 'type' => $type, 'errno' => $errno, 'error' => $error, 'file' => $file, 'line' => $line, 'traces' => $traces);
            echo to_json($array);
        } else {
            require __DIR__ . '/exception.php';
        }
    }

    /**
     * 关闭调试模式时显示错误
     * @param string $msg
     */
    protected function closeDebugShowError($msg = '系统错误，请稍候访问', $code = -9) {
        if (IS_API) {
            echo to_json(['msg' => $msg, 'code' => $code]);
        } else {
            require __DIR__ . ' / bug . php';
        }
        die;
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
        case 1;
        case E_ERROR: // 1 //
            return '致命错误';
        case 2;
        case E_WARNING: // 2 //
            return '警告错误';
        case 4;
        case E_PARSE: // 4 //
            return '语法错误';
        case 8;
        case E_NOTICE: // 8 //
            return '一般通知';
        case 16;
        case E_CORE_ERROR: // 16 //
            return '执行致命性错误';
        case 32;
        case E_CORE_WARNING: // 32 //
            return '执行警告错误';
        case 64;
        case E_COMPILE_ERROR: // 64 //
            return '编译致命性错误';
        case 128;
        case E_COMPILE_WARNING: // 128 //
            return '编译警告级错误';
        case 256;
        case E_USER_ERROR: // 256 //
            return '用户自定义错误';
        case 512;
        case E_USER_WARNING: // 512 //
            return '用户自定义警告';
        case 1024;
        case E_USER_NOTICE: // 1024 //
            return '用户自定义提醒';
        case 2048;
        case E_STRICT: // 2048 //
            return '编码标准化警告';
        case 4096;
        case E_RECOVERABLE_ERROR: // 4096 //
            return '可恢复错误';
        case 8192;
        case E_DEPRECATED: // 8192 //
            return '弃用代码';
        case 16384;
        case E_USER_DEPRECATED: // 16384 //
            return '用户自定义弃用代码';
        }
        return $type;
    }

    /**
     * 获取错误码
     *
     * @param $type
     *
     * @return string
     */
    public function errorCode($type) {
        switch ($type) {
        case E_ERROR:
            return 1;
        case E_WARNING:
            return 2;
        case E_PARSE:
            return 4;
        case E_NOTICE:
            return 8;
        case E_CORE_ERROR:
            return 16;
        case E_CORE_WARNING:
            return 32;
        case E_COMPILE_ERROR:
            return 64;
        case E_COMPILE_WARNING:
            return 128;
        case E_USER_ERROR:
            return 256;
        case E_USER_WARNING:
            return 512;
        case E_USER_NOTICE:
            return 1024;
        case E_STRICT:
            return 2048;
        case E_RECOVERABLE_ERROR:
            return 4096;
        case E_DEPRECATED:
            return 8192;
        case E_USER_DEPRECATED:
            return 16384;
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
    public function trace($value = '[this7]', $label = '', $level = 'DEBUG', $record = false) {
        static $trace = [];
        if ('[this7]' == $value) {
            #获取trace信息
            return $trace;
        } else {
            $level = strtoupper($level);
            if (IS_AJAX || $record) {
                logger::info($level, $record);
            } else {
                if (!isset($trace[$level])) {
                    $trace[$level] = [];
                }
                $trace[$level][] = $info;
            }
        }
    }
}