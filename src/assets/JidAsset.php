<?php
namespace junguo\apidoc\assets;

class JidAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/junguo/yii2-apidoc/src/assets';
    public $css = [
        'bootstrap-3.2.0/dist/css/bootstrap.min.css',
        'jsonFormater/jsonFormater.css',
    ];
    public $js = [
        'bootstrap-3.2.0/dist/js/bootstrap.min.js',
        'jsonFormater/jsonFormater.js',
        'js/jquery.form.js',
        'layer/layer.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
    public $jsOptions = [
        'position' => \yii\web\View::POS_HEAD,
    ];
}