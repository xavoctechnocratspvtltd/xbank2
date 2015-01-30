<?php
/**
 * Created by JetBrains PhpStorm.
 * User: vadym
 * Date: 4/28/13
 * Time: 9:57 PM
 * To change this template use File | Settings | File Templates.
 */
namespace x_captcha;
class Controller_Captcha extends \Controller {
    public $view_class = 'x_captcha\View_Captcha';
    public $captcha_session_id = null;
    public $session_name;
    function init() {
        parent::init();
        if (!class_exists('\Imagick',false)) throw $this->exception('Imagick is not installed');
        if (get_class($this->owner)!='Form_Field_Line') throw $this->exception('Captcha can be connected to Form_Field_Line only');

        $this->owner->captcha = $this;

        $this->session_name = $this->owner->name.'_'.$this->captcha_session_id.'_captcha_value';

        $this->addCaptcha();
    }
    function memorizeCaptcha($value) {
        $this->api->memorize($this->session_name,$value);
    }
    function recallCaptcha() {
        if ($this->api->recall($this->session_name)===null)
            $this->api->js()->univ()->errorMessage('Error! Reload captcha and try again!')->execute();
        return $this->api->recall($this->session_name);
    }
    function isSame($value,$case_sensetive=true) {
        $captcha_value = $this->recallCaptcha();
        if (!$case_sensetive) {
            $value = mb_strtolower($value,'UTF-8');
            $captcha_value = mb_strtolower($captcha_value,'UTF-8');
        }
        if ($value == $captcha_value) {
            return true;
        }
        return false;
    }
    private function addCaptcha() {
        if ($_GET['captcha_view']) {
            $this->add($this->view_class,array(
                'controller' => $this,
            ));
        } else {
            $view = $this->owner->aboveField();
            $view->setHTML('<div><img style="cursor:pointer" src="'.$this->api->url(null,array('captcha_view'=>'true','rand'=>md5(microtime()))).'" /> <i class="glyphicon glyphicon-refresh"></i></div>');
            $view->js('click')->reload();
        }
    }
}
