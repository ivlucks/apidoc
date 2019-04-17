<?php

namespace junguo\apidoc;

use junguo\apidoc\models\Language;
use junguo\apidoc\models\User;

class Module extends \yii\base\Module
{
    /**
     * @var string the password that can be used to access Module.
     * If this property is set false, then Module can be accessed without password
     * (DO NOT DO THIS UNLESS YOU KNOW THE CONSEQUENCE!!!)
     */
    public $password;

    /**
     * @var array the IP filters that specify which IP addresses are allowed to access Module.
     * Each array element represents a single filter. A filter can be either an IP address
     * or an address with wildcard (e.g. 192.168.0.*) to represent a network segment.
     * If you want to allow all IPs to access , you may set this property to be false
     * (DO NOT DO THIS UNLESS YOU KNOW THE CONSEQUENCE!!!)
     * The default value is array('127.0.0.1', '::1'), which means Module can only be accessed
     * on the localhost.
     */
    public $ipFilters =['127.0.0.1', '::1'];
    /**
     * @var string 签名获取接口
     */
    public $signUrl;
    public $secretKey = [];
    private $_assetsUrl;
    public $dropdownList;
    public $logoutUrl;
    public $subOfClasses;
    public $language = 'zh';
    public $apppath = 'api';
    public $tokenname = 'accessToken';
    public $domain='';
    public $ctrs='';
    /**
     * Initializes the  module.
     */
    public function init()
    {
        User::$passwordSetting = $this->password;
        Language::$lang = strtolower($this->language);
        parent::init();
        \Yii::$app->setComponents(array(
            'errorHandler' => array(
                'class'       => '\yii\web\ErrorHandler',
                'errorAction' => $this->id . '/default/error',
            ),
            'user'         => [
                'class'         => 'yii\web\User',
                'identityClass' => 'junguo\apidoc\models\User',
                'loginUrl'      => \Yii::$app->urlManager->createUrl($this->id . '/default/login'),
           ],
        ), false);
    }

    /**
     * @return string the base URL that contains all published asset files of .
     */
    public function getAssetsUrl()
    {
        if ($this->_assetsUrl === null) $this->_assetsUrl = \Yii::$app->getAssetManager()->publish(\Yii::getAlias('@vendor/junguo/yii2-apidoc/src/assets'))[1];
        return $this->_assetsUrl;
    }

    /**
     * @param string $value the base URL that contains all published asset files of .
     */
    public function setAssetsUrl($value)
    {
        $this->_assetsUrl = $value;
    }

    /**
     * Performs access check to .
     * This method will check to see if user IP and password are correct if they attempt
     * to access actions other than "default/login" and "default/error".
     * @param \yii\base\Controller $controller the controller to be accessed.
     * @param \yii\base\Action $action the action to be accessed.
     * @return boolean whether the action should be executed.
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $route = \Yii::$app->controller->id . '/' . $action->id;
            if (!$this->allowIp(\Yii::$app->request->userIP) && $route !== 'default/error')
                throw new \yii\web\HttpException(403, "You are not allowed to access this page.");
            $publicPages = [
                'default/login',
                'default/error',
            ];
            if ($this->password !== false && \Yii::$app->user->isGuest && !in_array($route, $publicPages)) \Yii::$app->user->loginRequired();
            else return true;
        }
        return false;
    }

    /**
     * Checks to see if the user IP is allowed by {@link ipFilters}.
     * @param string $ip the user IP
     * @return boolean whether the user IP is allowed by {@link ipFilters}.
     */
    protected function allowIp($ip)
    {
        if (empty($this->ipFilters))
            return true;
        foreach ($this->ipFilters as $filter) {
            if ($filter === '*' || $filter === $ip || (($pos = strpos($filter, '*')) !== false && !strncmp($ip, $filter, $pos)))
                return true;
        }
        return false;
    }
}