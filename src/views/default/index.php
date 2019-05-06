<?php
use junguo\apidoc\models\Language;
use \yii\helpers\Html;
$this->title = '接口系统文档';
?>

<div class="row-fluid">
    <div class="row">
        <nav role="navigation" class="navbar navbar-default">
            <div class="navbar-header">
                <button data-target="#example-navbar-collapse" data-toggle="collapse" class="navbar-toggle" type="button">
                    <span class="sr-only"><?= Language::t('navbar');?></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a href="#" class="navbar-brand"><?= $title ?></a>
            </div>
            <div id="example-navbar-collapse" class="collapse navbar-collapse">
                <ul class="nav navbar-nav">
                    <li class="dropdown"></li>
                    <li><span>&nbsp;</span></li>
                    <?php foreach ($modules as $name => $m): ?>
                        <?= Html::tag('li', Html::a($m['name'], ['/apidoc/default/index', 'parent' => '', 'module' => $name]), ['class' => $name == $module ? 'active' : '']) ?>
                    <?php endforeach ?>
                </ul>

                <ul class="nav navbar-nav">
                    <li class="dropdown"></li>
                    <li><span>&nbsp;<br></span></li>
                    <li><span>&nbsp;<br></span></li>
                    <li ><a href="#"> 令牌:<input id="accessToken" name="<?=$this->context->module->tokenname?>" value="<?=$token?>"><br></a></li>
                </ul>
            </div>
        </nav>
    </div>

    <div class="row">
        <div class="col-md-2">
            <div class="panel-group" id="accordion2">
                <?php foreach ($controllers as $cname => $c): ?>
                    <div class="panel panel-info">
                        <div class="panel-heading" data-toggle="collapse" data-parent="#accordion2"
                             href="#collapse<?= str_replace('\\', '-', $cname) ?>">
                            <a href="#" class="accordion-toggle"
                               style="text-decoration:none;display: block;outline: none"
                               title="<?= '【controller】' . $cname ?>"><?= $module . $c['brief']; ?></a>
                        </div>
                        <div id="collapse<?= str_replace('\\', '-', $cname) ?>"
                             class="list-group panel-collapse collapse<?= !strcasecmp($controller, $cname) ? ' in' : '' ?>">
                            <?php
                            if(!empty($c['actions'])){
                            foreach ($c['actions'] as $aname => $a) {
                                $apiPath        = explode("\\", $cname);
                                $moduleName     = isset($apiPath["3"]) ? $apiPath["3"] : "";
                                $controllerName = isset($apiPath["5"]) ? $apiPath["5"] : "";
                                $actionName     = $aname;
                                $apiUri         = "/" . $moduleName . "/" . $controllerName . "/" . $actionName;
                                ?>
                                <?=Html::a($a['brief'] . $apiUri, ['/apidoc/default/index', 'parent' => '', 'module' => $module, 'controller' => $cname, 'action' => $aname], ['title' => '【controller】' . $cname . "\n" . '【action】' . $aname, 'id' => 'collapse' . $aname, 'class' => 'list-group-item' . ($action == $aname ? ' active' : '')])
                                ?>
                            <?php }
                                 } ?>
                        </div>
                    </div>
                <?php endforeach ?>

            </div>
        </div>

        <div id="ouputPannel" class="col-md-5">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?= Language::t('panelTitle')?></h3>
                </div>
                <div class="panel-body">
                    <?php if (empty($controller) || empty($action)): ?>
                        <h3 class="panel-title"><?= Language::t('interfaceError');?></h3>
                    <?php else: ?>
                        <form id="invokeForm" class="form-horizontal" role="form" method="<?= trim(str_replace('<br />', '', $method)) ?>" action="
                    <?php
                        \Yii::$app->urlManager->baseUrl  =  $this->context->module->domain;
                        if(in_array($module,$this->context->module->ctrs)){
                            $_module = '';
                           }else{
                            $_module = lcfirst($module) . '/' ;
                        }
                        $url = \Yii::$app->urlManager->createAbsoluteUrl('' . '/' .$_module. lcfirst($shortController) . '/' . lcfirst($action));
                        $url  = str_ireplace('/-', '/', preg_replace_callback('/[A-Z]/', function ($match) { return '-' . strtolower($match[0]);}, $url));
                        $_url = $url;
                        if($token){
                            if($this->context->module->token_type==1) $_url         =  $url.'?'.$this->context->module->tokenname.'='.$token;
                            if($this->context->module->token_type==3){
                                $headers[]   =  "Accept:application/json";
                                $headers[]   =  "Authorization: Bearer ". $token;
                            }
                        }
                        echo $_url;
                        \Yii::$app->urlManager->baseUrl  ='';
                        ?>" enctype="multipart/form-data">
                            <?php foreach ($params as $i => $p): ?>
                                <div class="form-group">
                                    <?= Html::label($p['brief'], "param-{$i}-{$p['name']}", ['class' => 'col-sm-3 control-label']) ?>
                                    <div class="col-sm-9">
                                        <?php
                                            if ($p['type'] == 'file') {
                                                echo '<input type="file" onclick="" name="' . $p['name'] . '" placeholder="" value="">';
                                            } elseif ($p['type'] == 'files') {
                                                echo '<input type="file" onclick="" name="' . $p['name'] . '[]" placeholder="" value="">';
                                                echo '<input type="file" onclick="" name="' . $p['name'] . '[]" placeholder="" value="">';
                                                echo '<input type="file" onclick="" name="' . $p['name'] . '[]" placeholder="" value="">';
                                            } else {
                                                $defaultValue = rtrim(ltrim($p['default'], "("), ")");
                                                echo Html::textInput($p['name'], $defaultValue, ['class' => 'form-control', 'id' => "param-{$i}-{$p['name']}", 'placeholder' => $p['type'] . ' ' . $p['name']]);
                                            }
                                        ?>
                                    </div>
                                </div>
                            <?php endforeach ?>

                            <div class="form-group">
                                <div class="col-sm-offset-4 col-sm-10">
                                    <button type="button" class="btn btn-danger" id="invokeBtn"><?= Language::t('invokeButton');?></button>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <a href="" id="apiUrl" style="word-break: break-all;" target="_blank"></a>
                                </div>
                            </div>
                        </form>
                    <?php endif ?>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">
                    <ul class="nav nav-pills">
                        <li class="active"><a href="#result" data-toggle="tab"><?= Language::t('resultMessage');?> </a></li>
                        <li style="float:right;">
                            <a id="outputExpand" href="javascript:;" style="color:gray">展开&gt;&gt;</a>
                            <a id="outputCollapse" href="javascript:;" style="color:gray;display: none">&lt;&lt;缩回</a>
                        </li>
                    </ul>
                </div>
                <div class="panel-body">
                    <div class="tab-content">
                        <div class="tab-pane active" id="result"><?= Language::t('noResultMessage');?></div>
                        <div class="tab-pane" id="thrift"><?= Language::t('noResultMessage');?></div>
                    </div>
                </div>
            </div>
        </div>

        <div id="descPannel" class="col-md-5">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?= Language::t('interfaceExplain');?></h3>
                </div>
                <div class="panel-body">
                    <p><span style="display: inline-block;padding-right: 5px;font-weight: bold;"><?= Language::t('interfaceUri');?>:</span><?php if (!empty($url)) echo substr($url, strpos($url, '/', 7) + 1); ?></p>
                    <p>
                        <span style="display: inline-block;padding-right: 5px;font-weight: bold;"><?= Language::t('interfaceExplainMethod');?>:</span><?php echo $method ?>
                    </p>
                    <p>
                        <span style="display: inline-block;padding-right: 5px;font-weight: bold;"><?= Language::t('interfaceExplainFunc');?>:</span><?php echo $brief ?>
                    </p>
                    <p>
                        <span style="display: inline-block;padding-right: 5px;font-weight: bold;"><?= Language::t('interfaceExplainDetail');?>:</span><?php echo str_replace('<br&nbsp;/>', '<br />', str_replace(' ', '&nbsp;', $function)); ?>
                    </p>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?= Language::t('interfaceExplainParams');?></h3>
                </div>
                <div class="panel-body">
                    <?php if (!empty($params)): ?>
                        <?php foreach ($params as $i => $p): ?>
                            <?= '$' .$p['name'] . ' ' .  ' :' . $p['detail']  . '<br />' ?>
                        <?php endforeach ?>
                    <?php else: ?>
                        <?= Language::t('interfaceError'); ?>
                    <?php endif ?>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?= Language::t('interfaceExplainResult');?></h3>
                </div>
                <div class="panel-body">
                    <?php if (!empty($return)) {
                        echo str_replace('<br&nbsp;/>', '<br />', str_replace(' ', '&nbsp;', $return));
                    } else {
                        echo 'undefined';
                    } ?>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title" style="font-weight: bold;color:red"><?= Language::t('interfaceExplainException');?></h3>
                </div>
                <div class="panel-body">
                    <?php if (!empty($exception)) {
                        echo str_replace('<br&nbsp;/>', '<br />', str_replace(' ', '&nbsp;', $exception));
                    } else {
                        echo 'undefined';
                    } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {
        var _url         = '<?=\Yii::$app->urlManager->createUrl('/apidoc/default')?>';
        var folder       = '<?=$this->context->module->assetsUrl?>';
        $('#invokeBtn').click(function (e) {
            $("#result").html("");
            e.preventDefault();
            var loadIndex = "";
            var options = {
                beforeSubmit: function (paramsObj) {
                    var formActionUrl = $("#invokeForm").attr("action");
                    reStoreInputVal(paramsObj, formActionUrl);
                  <?php if ($token && $this->context->module->token_type==2){ ?>
                    var arr ={};
                    arr = { "name":'<?=$this->context->module->tokenname?>', "value":'<?=$token?>', "type": "text"};
                    paramsObj.push(arr);
                  <?php } ?>
                    loadIndex = layer.load(1);
                },
                type:"<?=$method?>",
                success: function (resp) {
                    layer.close(loadIndex);
                    $QueryUrl = $(this)[0]['url'];
                    $("#apiUrl").attr("href", $QueryUrl);
                    $("#apiUrl").html($QueryUrl);

                    new JsonFormater({
                        dom: '#result',
                        imgCollapsed: folder + "/jsonFormater/images/Collapsed.gif",
                        imgExpanded: folder + "/jsonFormater/images/Expanded.gif"
                    }).doFormat(resp);
                },
                error: function (error) {
                    layer.close(loadIndex);
                    var status = error.status;
                    var statusText = error.statusText;
                    var errorMsg = "ErrorCode:" + status + '，ErrorMsg:' + statusText;
                    layer.msg(errorMsg);
                }
            };
            $('#invokeForm').ajaxSubmit(options);
        })
    });
    $('#accessToken').change(function () {
         var url  = '<?=\Yii::$app->urlManager->createUrl('/apidoc/default/ajax-token')?>'
         var val = $(this).val();
         $.get(url+"?token="+val);
    })
    $('#outputExpand').click(function () {
        $('#ouputPannel').addClass('col-md-10').removeClass('col-md-5');
        $('#descPannel').hide();
        $(this).hide();
        $('#outputCollapse').show();
    });
    $('#outputCollapse').click(function () {
        $('#ouputPannel').addClass('col-md-5').removeClass('col-md-10');
        $('#descPannel').show();
        $(this).hide();
        $('#outputExpand').show();
    });
    function reStoreInputVal(paramsObj, apiUrl) {
        if (window.localStorage) {
            for (var key in paramsObj) {
                var inputName = paramsObj[key]['name'];
                var inputValue = paramsObj[key]['value'];
                var inputType = paramsObj[key]['type'];
                if (inputType == 'hidden') {
                    continue;
                }
                var localStorageKey = apiUrl + '_' + inputName;
                localStorage.setItem(localStorageKey, inputValue);
            }
        }
    }

    function rePutDataToInputFromlocalStorageData() {
        var inputObj = $('#invokeForm').find('input');
        if (!inputObj) return false;
        var formActionUrl = $("#invokeForm").attr("action");
        for (var i = 0; i < inputObj.length; i++) {
            var inputType = $(inputObj[i]).attr("type");
            var inputName = $(inputObj[i]).attr("name");
            if (inputType == 'hidden' || inputName == 'uid' || inputName == 'phone' || inputName == 'sid' || inputName == 'account') {
                continue;
            }
            var localStorageKey = formActionUrl + '_' + inputName;
            var cacheValue = localStorage.getItem(localStorageKey);
            if (cacheValue) {
                $(inputObj[i]).val(cacheValue);
            }
        }
    }
    rePutDataToInputFromlocalStorageData();
</script>