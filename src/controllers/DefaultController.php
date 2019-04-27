<?php

namespace junguo\apidoc\controllers;

use junguo\apidoc\models\Language;
use junguo\apidoc\models\LoginForm;
use \yii;

header('Content-type:text/html;charset=utf-8');
class DefaultController extends \yii\web\Controller
{
    public $layout = 'column1';
    public $enableCsrfValidation = false;
    public function actionIndex($parent = '', $module = 'user', $controller = '', $action = '')
    {
        $modules          = $this->getReflectionModules($parent);
        if (empty($modules)) throw new \yii\base\Exception(sprintf('未检测到模块'));
        if ($module == 'user' && !empty($modules) && empty($modules[$module])) $module = key($modules);
        if (empty($modules[$module])) throw new \yii\base\Exception(sprintf('未检测到模块[%s]', $module));
        $controllers      = $this->getControllers($parent . $module);
        $params           = isset($controllers[$controller]['actions'][$action]['param']) ? $controllers[$controller]['actions'][$action]['param'] : [];
        $pos              = strrpos($controller, '\\');
        $shortController  = substr($controller, $pos + 1);
        return $this->render('index', [
            'module'          => $module,
            'controller'      => $controller,
            'shortController' => $shortController,
            'action'          => $action,
            'modules'         => $modules,
            'token'           => Yii::$app->cache->get($this->module->tokenname),
            'controllers'     => $controllers,
            'method'          => isset($controllers[$controller]['actions'][$action]['method']) ? $controllers[$controller]['actions'][$action]['method'] : 'GET',
            'brief'           => isset($controllers[$controller]['actions'][$action]['brief']) ? $controllers[$controller]['actions'][$action]['brief'] : '未填写',
            'function'        => isset($controllers[$controller]['actions'][$action]['detail']) ? $controllers[$controller]['actions'][$action]['detail'] : '未填写',
            'params'          => $params,
            'return'          => isset($controllers[$controller]['actions'][$action]['return']) ? $controllers[$controller]['actions'][$action]['return'] : '未填写',
            'exception'       => isset($controllers[$controller]['actions'][$action]['throws']) ? $controllers[$controller]['actions'][$action]['throws'] : '未填写',
            'title'           => '接口测试系统文档'
        ]);
    }
    /**
     * 判断是否继承设定的classes
     * @param \ReflectionClass $rc
     * @return bool
     */
    private function isSubclassOfList(\ReflectionClass $rc)
    {
        if (!empty($this->module->subOfClasses)) {
            foreach ($this->module->subOfClasses as $subOfClass) {
                if (!class_exists($subOfClass)) continue;
                if (!$rc->isSubclassOf($subOfClass)) {
                    return false;
                }
            }
        }
        return true;
    }
    /**
     * @brief 反射控制器
     * @return array
     * @throws \Exception
     */
    protected function getControllers($module)
    {
        $controllers = [];
        $base_path   = $this->module->apppath.'/modules/' . $module;
        $dirName     = Yii::getAlias('@'.$base_path . '/controllers');
        if(!is_dir($dirName)){
            $base_path   =  $this->module->apppath ;
            $dirName     =  Yii::getAlias('@'.$base_path.'/'. $module );
        }

        if (!is_dir($dirName)) return [];
        $dirs = scandir($dirName);
        foreach ($dirs as &$d) {
            if (preg_match('/^\..*/', $d)) continue;
            $tem_dir      =  $dirName.'/'.$d;
            $class        = '\\'.$base_path. '\\controllers\\' . substr($d, 0, -4);
            $class        =  str_replace('/','\\',$class);
            $cache_key    =  $class.'-'. @filemtime($tem_dir);
            $_data        =  [];
            $_data        =  Yii::$app->cache->get($cache_key);
            if (is_dir($tem_dir)) {
                $_dirs = scandir($tem_dir);
                foreach ($_dirs as $_d){
                    if (preg_match('/^\..*/', $_d)) continue;
                    array_push($dirs, $d.'\\'.$_d);
                }
                continue;
            }
                     if(empty($_data)){
                                $actions = [];
                                $class_name = str_replace('Controller','',substr($d, 0, -4));
                                $rc  = new \ReflectionClass($class);
                                if (!$this->isSubclassOfList($rc))continue;
                                if (preg_match('/@api-disable/', $rc->getDocComment())) continue;
                                if (preg_match('/@ext-enable'  . '([\s]+)([a-zA-Z]+)\b([^@]+)/u', $rc->getDocComment(),$mathches)){
                                     $outfun   =   str_replace('*', '', $mathches[0]);
                                     $outfun   =   str_replace('@ext-enable', '', $outfun );
                                     $outfun   =   str_replace('  ', ' ', $outfun );
                                     $outfun   =   preg_replace("/\s/","",$outfun);
                                     $funs     =  explode(',',strtolower($outfun));
                                 }
                                $rm            = $rc->getMethods(\ReflectionMethod::IS_PUBLIC);
                                foreach ($rm as $m) {
                                    $name       = $m->getName();
                                    if (!preg_match('/action*/', $m) || $name == 'actions') continue;
                                    $_name = preg_replace('/action/','',strtolower($name));
//                                    gengxin
                                    if( $m->class != $rc->name){
                                        if(!empty($funs)){
                                            if(!in_array($_name,$funs))continue;
                                        } else{
                                            continue;
                                        }
                                    }
                                    if (!strncasecmp($name, 'action', 6) && $name != 'actions') {
                                        $method = new \ReflectionMethod($class, $name);
                                        if (preg_match('/@api-disable/', $method->getDocComment())) continue;
                                        $actions[substr($name, 6)] = array_merge([
                                            'id'      => substr($name, 6),
                                            'version' => 1,
                                            'brief'   =>''
                                        ], $this->extractProperty($method->getDocComment()));
                                        unset($method);
                                    }
                                }
                             if(!empty($actions)){
                                 $_data =array_merge(['id'      => substr($class, 0, -10),
                                     'actions' =>  $actions,
                                     'brief'   =>  '-'.$class_name],
                                     $this->extractProperty($rc->getDocComment())
                                 );
                                 Yii::$app->cache->set($cache_key,$_data);
                             }
                      }
            if(!empty($_data)) $controllers[substr($class, 0, -10)] =$_data;
        }
        return $controllers;

    }
    /**
     * @brief 反射模块
     * @return array
     * @throws \Exception
     */
    protected function getReflectionModules($parent = '')
    {
        $modules = [];
        $dir     = \Yii::getAlias('@'.$this->module->apppath.'/modules' . ($parent == '' ? '' : '/' . $parent . '/modules'));

        if (!is_dir($dir)) return false;
        $dirs    = scandir($dir);
        foreach ($dirs as $d) {
            if (preg_match('/^\..*/', $d)) continue;
            if (strpos($d, '.php')) continue;
            $properties['id']   =$d;
            $properties['name'] = $d;
            $modules[$d] = $properties;
        }
        if(!empty($this->module->ctrs)){
            foreach ($this->module->ctrs as  $v){
                $modules[$v]  =[
                    'enable'=>'',
                    'id'=>$v,
                    'name'=>$v
                ];
            }
        }
        return $modules;
    }
    protected function getReflectionModuleClass($parent, $module)
    {
        $modClass = '\\'.$this->module->apppath.'\modules\\' . $module . '\Module';
        return new \ReflectionClass($modClass);
    }
    /**
     * @brief 提取注解属性
     * @param $comment
     * @param string $prefix
     * @return array
     */
    protected function extractProperty($comment, $prefix = '')
    {
        $properties = [];
        if (preg_match_all('/@' . $prefix . '([a-zA-Z]+)\b([^@]+)/u', $comment, $matches)) {
            for ($i = 0; $i < count($matches[0]); $i++) {
                if (in_array($matches[1][$i], ['param'])) {
                    $properties[$matches[1][$i]][] = $this->extractParamInfo(str_replace('*', '', trim($matches[2][$i], '/')));
                } else {
                    $properties[$matches[1][$i]] = nl2br(preg_replace('/^\s*\n/', '', str_replace('*', '', trim($matches[2][$i], '/'))));
                }
            }
        }
        return $properties;
    }
    /**
     * @brief 提取参数
     */
    public function extractParamInfo($paramInfo)
    {
        $paramInfo = str_replace('  ',' ',$paramInfo);
        if (empty($paramInfo)) return [];
        $param = [
            'type'    => 'unknown',
            'name'    => 'unknown',
            'default' => null,
            'brief'   => 'unknown',
            'detail'  => ''
        ];
        $part = explode(' ', trim($paramInfo));
        if (!empty($part[0])) $param['type'] = $part[0];
        if (!empty($part[1])) $param['name'] = $part[1];
        unset($part[0]);
        unset($part[1]);
        $detail = implode(' ',$part);
        if ('unknown' != $param['name'] && !empty($param['name'])) {
            $param['detail']   = $detail?$detail:"未注明";
            $param['brief']    = str_replace('$', '', $param['name']);
            $param['default']  = '';
        }
        $param['name'] = str_replace('$', '', $param['name']);
        return $param;
    }
    function is_json($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
    public function actionError()
    {
        if ($error = \yii::$app->errorHandler->error) {
            if (\yii::$app->request->isAjaxRequest) echo $error['message'];
            else $this->render('error', $error);
        }
    }
    /**
     * @brief 登入
     */
    public function actionLogin()
    {
        $model = new LoginForm();
        if (isset($_POST['LoginForm'])) {
            $model->attributes = $_POST['LoginForm'];
            if ($model->validate() && $model->login()) {
                $this->redirect(\yii::$app->urlManager->createUrl('apidoc/default/index'));
            }
        }
        return $this->render('login', array('model' => $model));
    }
    /**
     * @desc 保存token
     * **/
    public function actionAjaxToken()
    {
      return Yii::$app->cache->set($this->module->tokenname,Yii::$app->request->get('token'));
    }
}