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

class MyException extends Exception {
    //构造函里处理一些逻辑，然后将一些信息传递给基类
    public function __construct($message = null, $code = 0, $file = '', $line = '') {
        $this->file = $file;
        $this->line = $line;
        parent::__construct($message, $code);
    }
}