## 项目描述
    1、增加redis,加快解析速度
    2、自定义域名,文档,api分开
    3、自定义app路径,
    4、增加令牌,方便测试,过期再生成,自动保存
    5、精简参数
    6、开放权限下降class,method.mould无需标注
    7、继承的类的方法需要显式标注
    8、删除bootstrap,不再因加载困扰
## 安装
composer require junguo/yii2-apidoc

## 访问
直接访问项目地址根目录即可。登录默认密码：123456

## 准备
1、请确保yii2开启了url美化功能，
``` php
'urlManager' => [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'rules' => [
    ],
],
```
2、需要安装好redis
``` php
    "yiisoft/yii2-redis": "^2.0.9"
```
## 项目配置：
将下方配置引入入口文件main-local.php或按照项目配置文件( 正式环境不要引入）：
``` php
$config['modules']['apidoc'] = [
    'class'=>'junguo\apidoc\Module',
    'password'=>'123456',
    'ipFilters'=>['*','::1'],
    'domain'=>'api.xxx.com',//测试api域名,一般文档和api不在同一域名下.api index.php头部添加header('access-Control-Allow-Origin:*'); ,否则会出现跨域问题
    'tokenname'=>'access-token', // 令牌键值
     'apppath' => 'api',      //api模块路径
    'token_type' => '1',      //令牌以明文方式上传,其他参数见文档,默认1
    'ctrs' =>['controllers'], //  apppath下其他Controller 路径
];

```

### ['modules']['apidoc']配置说明：

  注释参数  | 作用 | 备注
  ------------- | ------------- |  -------------
  class  | 模块入口 |
  password | 登录模块的密码 |
  ipFilters | IP登录限制 |
  subOfClasses | 需要继承的指定class,若为空则将所有controller囊括，否则必须继承这些类才会显示在页面上
  apppath    |api模块路径
  ctrs      | api下根controllers,可多个
  tokenname  | 测试时使用.
  domain  | 实际api的请求域名  不是同一域名下要注意跨域问题 在API的index.php文件添加 header('access-Control-Allow-Origin:*');
  token_type    | 令牌上传方式,默认1 ,  2 post参数形式  3 header Bearer 1 明文url参数追加 
## Phpstrom 注释配置：
打开phpstrom->setting->Editor->File and Code Templates->Includes，将两个文件内容替换原本的文件内容,点击apply即可

### 配置文件：
PHP Function Doc Comment
```
/**
#if (${NAME.indexOf('action')} != '-1' && ${NAME}!='actions')
@brief 接口名称
@param type $name 描述
@method POST/GET
@detail 接口描述
@return array
@throws Null
#end
${PARAM_DOC}
#if (${TYPE_HINT} != "void") * @return ${TYPE_HINT}
#end
${THROWS_DOC}
*/
```

PHP Class Doc Comment
```
/**
 * Class ${NAME}
#if (${NAMESPACE}) * @package ${NAMESPACE}
#end
#eif (${NAME.indexOf('Controller')}!=-1)
@brief controller名称
#end
 */
```

## 检查phpstorm配置


## 使用apidoc编写程序注释

1、方法注释规范如下：

  注释参数  | 作用 | 备注
  ------------- | ------------- |  -------------
 brief         | 标明方法名称，将会显示在页面左方      |
 param        | 需要传入的参数 
 method        | 调用方法，POST/GET      |
 return        | 返回参数，将会显示在页面右方      |
 throws        | 异常声明，将会显示在页面右方      |
 detail        | 接口说明，将会显示在页面右方      |
 api-disable   | 接口说明，默认显示,如显式增加为隐藏class或者method      |
 ext-enable    | 接口说明，默认隐藏继承的类的method,如无显式标注不显示, 多个method逗号分隔,无需加action     |

## 示例：
先配置好phpstome注释配置。
1、配置controller类的注释
2、配置action的注释

