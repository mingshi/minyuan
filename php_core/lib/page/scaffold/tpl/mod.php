<?php
    $scaffold_helper->beforeModRender();
?>

<?php if (empty($isAjax)) :?>
<ul class="breadcrumb">
    <li><a href="<?=$scaffold_config['list_url']?>"><?=$scaffold_config['name']?>列表</a> <span class="divider">/</span></li>
    <li class="active">
    <span><?=@$scaffold_item[$scaffold_config['primary_key']] ? '编辑' : '新建'?><?=$scaffold_config['name']?></span>
    </li>
</ul>
<?php endif;?>

<form id="scaffold-form" action="<?=preg_replace('@\'"@', '', $_SERVER['REQUEST_URI'])?>" method="post" class="form-horizontal">
<?php $scaffold_helper->beforeModFormRender(); ?>
    <?php
        $postAndGet = $_POST + $_GET;
    ?>
    <?php foreach ($scaffold_config['fields'] as $field_config) :?>
    <?php
        $type = d(@$field_config['type'], 'text');
        if ( ! isset($field_config['rules'])) {
            $field_config['rules'] = '';
        }

        $attrs = isset($field_config['attrs']) ? ' ' . $field_config['attrs'] : '';

        $isRequired = preg_match('@required@', $field_config['rules']);

        $name = $field_config['field'];

        //特殊字段支持自定义
        $customMethod = 'onField' . camelize($name) . 'Render';
        $fieldContent = $scaffold_helper->$customMethod($field_config, @$scaffold_item);
        if ($fieldContent === TRUE) {
            continue;
        }
    ?>
    <div class="control-group" <?php if ($type == 'hidden') :?>style="display:none"<?php endif;?>>
        <label class="control-label">
            <?php if ($isRequired) :?>
            <i>*</i>
            <?php endif;?>
            <?=$field_config['label']?>
        </label>
        <div class="controls">
            <?php
                $default = NULL;
                if (isset($postAndGet[$name])) {
                    $default = $postAndGet[$name];
                } else if (isset($scaffold_item[$name])) {
                    $default = $scaffold_item[$name];
                } else if (isset($field_config['default'])) {
                    $default = $field_config['default'];
                }
                if ($type == 'readonly') {
                    echo $default;
                } else if ($type == 'text') {
                    $class = "";
                    if (preg_match('@(^|\|)numeric@', $field_config['rules'])) {
                        $class .= ' numeric';
                    } else if (strpos($field_config['rules'], 'alpha') !== FALSE) {
                        $class .= ' alpha';
                    }
                    echo form_input($name, set_value($name, $default), " class='$class'$attrs");
                } else if ($type == 'hidden') {
                    echo form_hidden($name, set_value($name, $default));
                } else if ($type == 'textarea') {
                    $class = "";
                    if (@$field_config['rich_html']) {
                        $class = "rich-edit";
                    }
                    echo form_textarea($name, set_value($name, $default), " class='$class'$attrs");
                } else if ($type == 'date') {
                    echo form_input($name, set_value($name, $default), " class='datepicker'$attrs");
                } else if ($type == 'datetime') {
                    echo form_input($name, set_value($name, $default), " class='datetimepicker'$attrs");
                } else if ($type == 'image') {
                    $id = rand_str(10);
                    $path = set_value($name, @$scaffold_item[$name]);
                    $url = '';
                    if ($path) {
                        $url = Config::get('Image.Prefix').$path;
                    }
                    $uploadOptions = array(
                        'file' => array('url' => $url, 'path' => $path),
                        'post_params' => array('session' => $_COOKIE[Config::get('Cookie.Session')]),
                        'name' => $name
                    );
                    if (isset($field_config['options'])) {
                        $uploadOptions = array_merge($uploadOptions, $field_config['options']);
                    }
                    $uploadOptions = json_encode($uploadOptions);
                    echo '<div id="img_upload_'.$id.'"></div>';
                    echo '<script>
                    $("#img_upload_'.$id.'").img_uploader('.$uploadOptions.');
                    </script>';
                } else if ($type == 'checkbox') {
                    $options = $field_config['options'];
                    $defaultOptions = array();
                    $item_val = @$scaffold_item[$name];
                    if ($item_val) {
                        $defaultOptions = explode(',', $item_val);
                    }
                    //加入中括号支持数组提交
                    $name .= '[]';
                    foreach ($options as $op_val => $op_name) {
                        $id = "ck_{$name}_{$op_val}";
                        $defaultChecked = in_array($op_val, $defaultOptions) ? TRUE : FALSE;
                        echo '<label class="checkbox inline">';
                        echo form_checkbox($name, $op_val, set_checkbox($name, $op_val, $defaultChecked));
                        echo $op_name;
                        echo '</label>';
                        echo "&nbsp;&nbsp;";
                    }
                } else if ($type == 'password') {
                    echo form_password($name, '', $attrs);
                } else if (is_array($type)) {
                    $options = $type;
                    echo form_dropdown($name, $options, $default, $attrs);
                }
            ?>
            <?php if (@$field_config['exp']) :?>
            <span class="help-inline"><?=$field_config['exp']?></span>
            <?php endif;?>
        </div>
    </div>
    <?php endforeach; ?>

    <?=$form_hash?>
    <?php if (! empty($scaffold_item)) :?>
    <?=form_hidden($scaffold_config['primary_key'], $scaffold_item[$scaffold_config['primary_key']])?>
    <?php endif;?>
    <input type="hidden" name="redirect_uri" value="<?=set_value('redirect_uri', @$redirect_uri)?>"/> 

    <?php if (empty($isAjax)) :?>
    <div class="form-actions">
        <input type="submit" class="btn btn-primary" value="保存">
        <input type="button" onclick="history.go(-1)" class="btn ml20" value="取消">
    </div>
    <?php endif;?>

<?php $scaffold_helper->afterModFormRender(); ?>
</form>

<script>
$(function(){
    if ($('#scaffold-form :text:first').length) {
        $('#scaffold-form :text:first').get(0).focus();
    }
    var rich_edit_id_index = 1;
	$('textarea.rich-edit').each(function(){
		var id = 'ke-eidt-' + (rich_edit_id_index++);
		$(this).attr('id', id).css({
            width: 600,
            height: 200
        });
        KE.show({
            id : id,
            resizeMode : 1,
            allowUpload : false,
            items : [
            'fontname', 'fontsize', '|', 'textcolor', 'bgcolor', 'bold', 'italic', 'underline',
            'removeformat', '|', 'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist',
            'insertunorderedlist', '|', 'image', 'link']
		});
	});

    $('#scaffold-form').submit(function(event){
        event.preventDefault();

        popup_msg('数据保存中...', 'info');
        var $f = $(this);
        
        $f.trigger('before_submit');
        var $disabled = $f.find(':disabled[name]');
        $disabled.prop('disabled', false);
        var post_params = $f.serialize();
        $disabled.prop('disabled', true);

        $.post($f.attr('action') || location.href, post_params, function(ret){
            if (ret.code != 0) {
                popup_msg(ret ? ret.msg : '发生异常错误', 'error');
            } else {

                $f.trigger('ajax_succ', ret); 

                if (ret.msg) {
                    popup_msg(ret.msg, 'succ');

                    if (/version=\w+/.test(location.search) && !ret.redirect_uri) {
                        return location.replace(location.href.replace(/version=\w+(&)?/, '').replace(/[&?]$/, ''));
                    }
                }
            }

            if (ret && ret.redirect_uri) {
                
                hide_popup_msg();
                if (/javascript\s*:\s*(.+)/.test(ret.redirect_uri)) {
                    $.globalEval(RegExp.$1);
                } else {
                    return location.replace(ret.redirect_uri);
                }
            }
            
            if (ret && ret.code == 0) {
                //location.reload();
            }

        }, 'json').error(function(){
            popup_msg('服务器响应错误', 'error');
        });
    });
});
</script>
