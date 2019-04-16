## 项目描述
    1、github找到的项目都无法完美使用,根据实际开发出发完善
    2、增加redis,加快速度,不用每次都去分析 ,
    3、自定义域名,
    4、自定义app路径,
    5、增加令牌已方便测试
    
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
将下方配置引入入口文件main-local.php或你认为合适的配置文件( 正式环境不要引入）：
``` php
$config['modules']['apidoc'] = [
    'class'=>'junguo\apidoc\Module',
    'password'=>'123456',
    'ipFilters'=>['*','::1'],
    'domain'=>'api.xxx.com',//api请求域名,一般文档和api不在同一域名下.api头部添加header('access-Control-Allow-Origin:*'); ,否则会出现跨域问题
    'tokenname'=>'access-token', // 令牌键值
     'apppath' => 'api',      //api模块路径
    'ctrs' =>['controllers'], //  apppath下其他Controller 路径
    'subOfClasses' => [], //需要继承的classes
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
  tokenname  |测试时使用.
  domain  |实际api的请求域名  不是统一域名要注意跨域问题 在API的index文件添加 header('access-Control-Allow-Origin:*');
## Phpstrom 注释配置：
打开phpstrom->setting->Editor->File and Code Templates->Includes，将两个文件内容替换原本的文件内容,点击apply即可

### 配置文件：
PHP Function Doc Comment
```
/**
#if (${NAME.indexOf('action')} != '-1' && ${NAME}!='actions')
@brief 接口名称
@param type $name this is your test name (=name:defaultValue=)
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
#if ('Module' == ${NAME})
@jid-enable
@jid-name 模块名称
@jid-id 模块ID
#elseif (${NAME.indexOf('Controller')}!=-1)
@brief controller名称
#end
 */
```

## 检查phpstorm配置


## 使用apidoc编写程序注释
1、检索项目第一级目录下的Modules模块下所有Module.php文件，注释参数如下：

  注释参数  | 作用 | 备注
  ------------- | ------------- |  -------------
apidoc-enable  | 标明此module将被收录到文档中 |
apidoc-id      | id值 |
apidoc-name    | 模块名称，将显示在页面上方 |


2、检索modules具体模块下controllers文件夹下所有controller文件，并遍历所有controller文件中所有action开头的所有方法，方法注释规范如下：

  注释参数  | 作用 | 备注
  ------------- | ------------- |  -------------
 brief         | 标明方法名称，将会显示在页面左方      |
 param        | 需要传入的参数 
 method        | 调用方法，POST/GET      |
 return        | 返回参数，将会显示在页面右方      |
 throws        | 异常声明，将会显示在页面右方      |
 detail        | 接口说明，将会显示在页面右方      |



## 示例：
先配置好phpstome注释配置。
1、配置modules模块注释
2、配置controller类的注释
3、配置action的注释
