<div class="well" style="width: 70%; margin: 80px auto 0;">
    <form class="form-horizontal ajax" method="post" action="/index/create" enctype="multipart/form-data">
        <fieldset>
            <legend>添加订单状态</legend>

            <div class="control-group">
 
                <label class="control-label" for="mobile">订单编号</label>
                <div class="controls">
                    <input type="text" class="input-xlarge" id="order_no" name="order_no">
                </div>
                <br />
               
                <label class="control-label" for="mobile">手机号码</label>
                <div class="controls">
                    <input type="text" class="input-xlarge" id="mobile" name="mobile">
                </div>
                <br />

                <label class="control-label" for="order_name">订单名称</label>
                <div class="controls">
                    <input type="text" class="input-xlarge" id="order_name" name="order_name">
                </div>
                <br />

                <label class="control-label" for="status">订单状态</label>
                <div class="controls">
                    <input type="text" class="input-xlarge" id="status" name="status">
                </div>
                <br />

                <label class="control-label" for="status">订单数量</label>
                <div class="controls">
                    <input type="text" class="input-xlarge" id="number" name="number">
                </div>
                <br />
                
                <label class="control-label" for="status">订单日期</label>
                <div class="controls">
                    <div class="input-prepend">
                        <span class="add-on"><i class="icon-calendar"></i></span>
                        <input type="text" id="order_date" style="width:243px;" name="order_date" class="datepicker input-xlarge">
                    </div>
                </div>
                <br />
                
                <div class="controls">
                    <button type="submit" id="ok" class="btn btn-large btn-primary">添加</button>
                </div>
            </div>
        </fieldset>
    </form>
</div>
