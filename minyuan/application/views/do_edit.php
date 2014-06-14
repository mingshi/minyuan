<div class="well" style="width: 70%; margin: 80px auto 0;">
    <form class="form-horizontal ajax" method="POST" action="" enctype= "multipart/form-data">
        <legend>编辑订单状态</legend>

        <div class="control-group">
            <label class="control-label" for="order_name">订单名称:</label>
            <div class="controls">
                <input type="hidden" name="order_no" value="<?=$order_no?>" />
                <?=$order_name?>
            </div>
            <br />            

            <label class="control-label" for="order_no">订单编号:</label>
            <div class="controls">
                <?=$order_no?>
            </div>
            <br />
             
            <label class="control-label" for="mobile">手机号:</label>
            <div class="controls">
                <?=$mobile?>
            </div>
            <br />            

            <label class="control-label" for="order_date">订单日期:</label>
            <div class="controls">
                <?=$order_date?>
            </div>
            <br />

            <label class="control-label" for="order_num">订单数量:</label>
            <div class="controls">
                <?=$order_num?>
            </div>
            <br />

            <label class="control-label" for="order_status">订单状态:</label>
            <div class="controls">
                <input type="text" name="order_status" id="order_status" value="<?=$order_status?>" />
            </div>
            <br />    
         
            <div class="controls">
                <input type="submit" value="确定" name="submit" id="ok" class="btn btn-large btn-primary" />
            </div>
        </div>

    </form>
</div>

