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
        #设置DEBUG错误
        $debug = is_boolean(($_GET['type'] == 'dapi'), true) . is_boolean(DEBUG, true) . is_boolean(XDEBUG, true);
        switch ($debug) {
        case '111':
            #报告所有错误
            error_reporting(E_ALL);
            break;
        case '100':
        case '110':
            #报告运行时错误
            error_reporting(E_ERROR | E_WARNING | E_PARSE);
            set_error_handler([$this, 'error'], E_ALL);
            set_exception_handler([$this, 'exception']);
            register_shutdown_function([$this, 'fatalError']);
            break;
        case '101':
        case '000':
        case '001':
        case '010':
        case '011':
            #禁用错误报告
            error_reporting(0);
            break;
        }
    }

    /**
     * 判断显示状态
     * @param  string  $value [description]
     * @return boolean        [description]
     */
    public function isDisplay() {
        # code...
    }

    /**
     * 自定义异常理(PHP>7.0)
     *
     * @param $e
     */
    public function error($e) {

    }

    /**
     * 自定义异常理(PHP>7.0)
     *
     * @param $e
     */
    public function exception($e) {

    }

    /**
     * 致命错误处理(PHP≤5.6)
     * @return [type] [description]
     */
    public function fatalError($e) {

    }

}