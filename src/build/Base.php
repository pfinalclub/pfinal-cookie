<?php
/**
 * Created by PhpStorm.
 * User: 南丞
 * Date: 2019/2/15
 * Time: 10:39
 *
 *
 *                      _ooOoo_
 *                     o8888888o
 *                     88" . "88
 *                     (| ^_^ |)
 *                     O\  =  /O
 *                  ____/`---'\____
 *                .'  \\|     |//  `.
 *               /  \\|||  :  |||//  \
 *              /  _||||| -:- |||||-  \
 *              |   | \\\  -  /// |   |
 *              | \_|  ''\---/''  |   |
 *              \  .-\__  `-`  ___/-. /
 *            ___`. .'  /--.--\  `. . ___
 *          ."" '<  `.___\_<|>_/___.'  >'"".
 *        | | :  `- \`.;`\ _ /`;.`/ - ` : | |
 *        \  \ `-.   \_ __\ /__ _/   .-` /  /
 *  ========`-.____`-.___\_____/___.-`____.-'========
 *                       `=---='
 *  ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
 *           佛祖保佑       永无BUG     永不修改
 *
 */

namespace pf\cookie\build;

use pf\config\Config;
use pf\encryption\Encryption;

class Base
{
    protected $items = [];
    protected $prefix;

    public function __construct()
    {
        $this->items = $_COOKIE;
        $this->prefix = (Config::get('cookie.prefix') ?: 'pfinal') . '##';
    }


    public function set($cookie_name, $value, $expire = 0, $path = '/', $domain = '')
    {
        $cookie_name = $this->prefix . $cookie_name;
        $value = Encryption::encrypt($value);
        $this->items[$cookie_name] = $value;
        $expire = $expire ? time() + $expire : $expire;
        if (PHP_SAPI != 'cli') {
            setcookie(
                $cookie_name,
                $value,
                $expire,
                $path,
                $domain
            );
        } else {
            die("Running client incorrectly\n");
        }
    }

    public function get($cookie_name)
    {
        if ($this->has($cookie_name)) {
            return Encryption::decrypt($this->items[$this->prefix . $cookie_name]);
        } else {
            die("No cookie\n");
        }
    }

    public function has($cookie_name)
    {
        return isset($this->items[$this->prefix . $cookie_name]);
    }

    public function all()
    {
        $data = [];
        foreach ($this->items as $name => $value) {
            $data[$name] = $this->get($name);
        }
        return $data;
    }

    public function del($cookie_name)
    {
        if (isset($this->items[$this->prefix . $cookie_name])) {
            unset($this->items[$this->prefix . $cookie_name]);
        }
        if (PHP_SAPI != 'cli') {
            setcookie($this->prefix . $cookie_name, '', 1);
        }
        return true;
    }

    public function flush()
    {
        $this->items = [];
        if (PHP_SAPI != 'cli') {
            foreach ($this->items as $key => $value) {
                setcookie($key, '', 1, '/');
            }
        }
        return true;
    }
}
