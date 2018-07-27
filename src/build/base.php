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