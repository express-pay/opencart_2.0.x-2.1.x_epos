<form action="<?php echo $action; ?>" method="post">
    <input type="hidden" name="ServiceId"           value="<?php echo $serviceId; ?>" />
    <input type="hidden" name="AccountNo"           value="<?php echo $accountNo; ?>" />
    <input type="hidden" name="Amount"              value="<?php echo $amount; ?>" />
    <input type="hidden" name="Currency"            value="<?php echo $currency; ?>" />
    <input type="hidden" name="Info"                value="<?php echo $info; ?>" />
    <input type="hidden" name="Surname"             value="<?php echo $surname; ?>" />
    <input type="hidden" name="FirstName"           value="<?php echo $firstName; ?>" />
    <input type="hidden" name="City"                value="<?php echo $city; ?>" />
    <input type="hidden" name="EmailNotification"   value="<?php echo $emailNotification; ?>" />
    <input type="hidden" name="SmsPhone"            value="<?php echo $smsPhone; ?>" />
    <input type="hidden" name="IsNameEditable"      value="<?php echo $isNameEditable; ?>" />
    <input type="hidden" name="IsAddressEditable"   value="<?php echo $isAddressEditable; ?>" />
    <input type="hidden" name="IsAmountEditable"    value="<?php echo $isAmountEditable; ?>" />
    <input type="hidden" name="Signature"           value="<?php echo $signature; ?>" />
    <input type="hidden" name="ReturnType"          value="<?php echo $returnType; ?>" />
    <input type="hidden" name="ReturnUrl"           value="<?php echo $returnUrl; ?>" />
    <input type="hidden" name="FailUrl"             value="<?php echo $failUrl; ?>" />
    <input type="hidden" name="ReturnInvoiceUrl"    value="<?php echo $returnInvoiceUrl; ?>" />
    <div class="buttons">
        <div class="pull-right">
            <input type="submit" value="<?php echo $button_confirm; ?>" id="button-confirm" class="btn btn-primary" />
        </div>
    </div>
</form>