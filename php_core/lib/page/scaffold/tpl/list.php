<?php $scaffold_helper->beforeListRender(); ?>
<?php
$canCreate = ! isset($scaffold_config['can_create']) || $scaffold_config['can_create'];
?>
<form class="form-search" method="get" action="">
    <?php $scaffold_helper->beforeSearchFormRender()?>
    <input type="text" name="kw" placeholder="输入关键字" value="<?=h(@$_GET['kw'])?>" class="txt">
    <input type="submit" class="btn" value="搜索">

    <?php if ($canCreate) :?>
    <a class="ml20 btn-primary btn" href="<?=d(@$scaffold_config['create_url'],
    "/{$scaffold_config['controller_directory']}{$scaffold_config['controller']}/create?redirect_uri=".urlencode(get_self_full_url()))?>"><i class="icon-plus"></i> 新建<?=$scaffold_config['name']?></a>
    <?php endif;?>

    <?php $scaffold_helper->afterSearchFormRender()?>
</form>
    
<?php
    $columns = $scaffold_config['list']['columns'];
?>

<div content-id="list">
<?php $scaffold_helper->beforeListTableRender(); ?>
<table class="<?=d(@$scaffold_config['list']['table_class'], 'table table-hover')?>">
    <thead>
        <?php $scaffold_helper->beforeListTableHeadRender();?>
        <tr class="head">
        <?php foreach ($columns as $column_name => $ignor) :?>     
        <?php
            //{列属性}列名称
            preg_match('@^\{(.+?)\}(.+)$@', $column_name, $ma);
            $column_attrs = '';
            if ($ma) {
                $column_attrs = $ma[1];
                $column_name = $ma[2];
            }
            if ($column_name == '__checkbox__') {
                $column_attrs = 'width="20"';
            }
        ?>
        <th<?=empty($column_attrs) ? '' : ' '.$column_attrs?>>
            <?php if ($column_name == '__checkbox__') : ?>
            <input type="checkbox" class="sel-all"/>
            <?php elseif (! $scaffold_helper->headColumnRender($column_name)) :?>
            <?=$column_name?>
            <?php endif;?>
        </th>
        <?php endforeach;?>
        <th>操作</th>
        </tr>
        <?php $scaffold_helper->afterListTableHeadRender();?>
    </thead>
    <tbody>
    <?php if (empty($scaffold_items)) :?>
    <tr>
        <td colspan="<?=count($columns) + 1?>" style="text-align:center;font-size:14px;font-weight:bold;color:#999">
            <p>没有数据</p>
        </td>
    </tr>
    <?php else :?>
    <?php foreach ($scaffold_items as $i => $scaffold_item) :?>
    <tr>
        <?php foreach ($columns as $column_index => $method) :?>
        <td>
            <?php if ($column_index == '__checkbox__') :?>
            <input type="checkbox" class="sel-item" value="<?=$scaffold_item[$scaffold_config['primary_key']]?>"/>
            <?php elseif (isset($scaffold_item[$method])) :?>
            <?=$scaffold_item[$method]?>
            <?php elseif ($method == '__LINE__') :?>
            <?=$column_index + 1 ?>
            <?php elseif (strpos($method, 'cb_') !== FALSE) :?>
            <?=$scaffold_helper->$method($scaffold_item)?>
            <?php else :?>
            <?=$scaffold_helper->processTpl($method, $scaffold_item)?>
            <?php endif;?>
        </td>
        <?php endforeach;?>
        <td>
            <?php $scaffold_helper->beforeOpColumnRender($scaffold_item); ?>
            <?php if ( ! $scaffold_helper->OpColumnRender($scaffold_item)) :?>
            <?=$scaffold_helper->editLink($scaffold_config, $scaffold_item)?>
            <?=$scaffold_helper->deleteLink($scaffold_config, $scaffold_item)?>
            <?php endif;?>
            <?php $scaffold_helper->afterOpColumnRender($scaffold_item); ?>
        </td>
    </tr>
    <?php endforeach;?>
    </tbody>
    <?php endif;?>
    <tfoot>
        <?php $scaffold_helper->beforeListTableFootRender();?>
        <?php if (isset($columns['__checkbox__'])) :?>
        <tr class="dark">
            <td colspan="<?=count($columns) + 1?>">
                <input type="checkbox" class="sel-all"/>
                <?php if ( ! $scaffold_helper->batchActionRender()) :?>
                <input type="button" class="batch-del-btn" value="删除"/>
                <?php endif;?>
            </td>
        </tr>
        <?php endif;?>
        <?php $scaffold_helper->afterListTableFootRender();?>
    </tfoot>
</table>

<?php $scaffold_helper->afterListTableRender(); ?>

<div class="pagination"><?=$scaffold_pagination?></div>

<!--END LIST-->
</div>

<form id="delete-form" action="/<?=$scaffold_config['controller_directory'].$scaffold_config['controller']?>/delete" method="post" style="display:none">
    <input type="hidden" name="<?=$scaffold_config['primary_key']?>" value=""/>
</form>

<div id="modal-edit" class="modal hide fade">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h3></h3>
  </div>
  <div class="modal-body">
  </div>
  <div class="modal-footer">
    <a href="javascript:;" class="btn" data-dismiss="modal" aria-hidden="true">取消</a>
    <a href="javascript:;" class="btn btn-primary">确认</a>
  </div>
</div>

<script>
function load_partial(content_id)
{
    var url = location.href;

    url += (url.indexOf('?') > 0 ? '&' : '?') + 'r=' + (+ new Date);

    popup_msg('数据加载中...', 'info');

    $.ajax({
        url: url,
        beforeSend: function(jqXHR, settings) {
            jqXHR.setRequestHeader("Partial", content_id);
        },  
        success: function(result) {
            $('[content-id=' + content_id + ']').html(result);
            hide_popup_msg();
        }   
    });  
}

$(function(){
    $(document).delegate('.del-btn', 'click', function(event){
        var id = $(this).attr('rel');
        if ( ! confirm('你确定要删除记录吗？')) {
            return;
        }
        $('#delete-form').find(':hidden').val(id);
        
        <?php if (@$scaffold_config['ajax']) :?>
        popup_msg('删除中...', 'info');
        $.post($('#delete-form').attr('action'), $('#delete-form').serialize(), function(ret){
            if (ret.code === 0) {
                popup_msg(ret.msg, 'succ');
                load_partial('list');
            } else {
                popup_msg(ret.msg, 'error');
            }
        }, 'json');
        <?php else :?>
        $('#delete-form').get(0).submit();
        <?php endif;?>
    });
    
    function get_selected_ids()
    {
        return $(':checkbox.sel-item:checked').map(function(){
            return this.value;
        }).get().join(',');
    }
    
    window.get_selected_ids = get_selected_ids;
    
    $(document).delegate('.batch-del-btn', 'click', function(event){
        var checkedUids = get_selected_ids();
        if ( ! checkedUids) {
            popup_msg('未选择记录', 'error');
            return;
        }
        if ( ! confirm('你确定要删除所选记录吗？')) {
            return;
        }
        $('#delete-form').find(':hidden').val(checkedUids).end()
        .get(0).submit();
    });
    
    $(document).delegate(':checkbox.sel-all,:checkbox.sel-item', 'click', function(){
        var $t = $(this), isChecked = $t.attr('checked');
        if ($t.is('.sel-all')) {
            if (isChecked) {
                $(':checkbox.sel-item,:checkbox.sel-all').attr('checked', true);
            } else {
                $(':checkbox.sel-item,:checkbox.sel-all').attr('checked', false);
            }
        } else {
            if (isChecked) {
                if ($(':checkbox.sel-item:not(:checked)').length == 0) {
                    $(':checkbox.sel-all').attr('checked', true);    
                }
            } else {
                $(':checkbox.sel-all').attr('checked', false);
            }
        }
    });
    
    $(document).delegate('.list-table tbody', 'click', function(event){
        var $target = $(event.target);
        if ($target.is('a,input,select')) {
            return;
        }
        var $checkbox = $target.closest('tr').find(':checkbox');
        if ($checkbox.length) {
            $checkbox.attr('checked', ! $checkbox.attr('checked'));
        }
    });

<?php if (@$scaffold_config['ajax']) :?>
    
    var $modal = $('#modal-edit');

    $modal.delegate('.btn-primary', 'click', function() {
        var $f = $modal.find('form');
        
        if (!$f.length) {

            return; 
        }

        $f.trigger('submit'); 

    }).delegate('form', 'ajax_succ', function(event, result) {

        delete result.redirect_uri; 

        $modal.modal('hide');
        
        if (result.code == 0) {
            load_partial('list');
        }
    });

    $(document).delegate('a[href*="/create"],a[href*="/edit"]', 'click', function(event){
        event.preventDefault();
        
        var href = $(this).attr('href');

        if (/create/.test(href)) {
            $modal.find('.modal-header h3').html('添加<?=$scaffold_config['name']?>');
        } else {
            $modal.find('.modal-header h3').html('编辑<?=$scaffold_config['name']?>');
        }

        $modal.modal({
            remote : 'about:blank',
            keyboard : true
        }).css({
            width: 850,
            'margin-left': '-375px'
        });

        popup_msg('数据加载中...', 'info');

        $modal.find('.modal-body').load(href, function(){
            hide_popup_msg();
        });
    });

<?php endif;?>
});
</script>

<?php $scaffold_helper->afterListRender(); ?>
