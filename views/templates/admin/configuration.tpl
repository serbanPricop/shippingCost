<form method="post">
    <div class="panel">
        <div class="panel-heading">
            {l s='Configuration shipping method' mod='shippingcost'}
        </div>
        <div class="panel-body">
            <div class="row">
                <label for="methodTax">{l s='Shipping method tax' mod='shippingcost'}</label>
                <input class='form-control' type="text" name="methodTax" id="methodTax">
            </div>
            <br>
            <div class="panel-footer">
                <button type="submit" name="shippingMethod" class="btn btn-default pull-right">
                    <i class="process-icon-save"></i>
                    {l s='Save' mod='shippingcost'}
                </button>
            </div>
        </div>
    </div>
</form>

