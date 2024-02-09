<?php echo $header; ?><?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content" class="container"><?php echo $content_top; ?>
  <ul class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
    <?php } ?>
  </ul>
	<h2><?php echo $heading_title; ?></h2>
	<p><?php echo $text_message; ?></p>
  
	<table style="width: 100%;text-align: left;">
    <tbody>
      <tr>
        <td valign="top" style="text-align:left;">
          <?php echo $content_body; ?>
        </td>
        <?php if ($show_qr_code == 1) : ?>
        <td style="text-align: center;padding: 40px 20px 0 0;vertical-align: middle">
          <p><img src="data:image/jpeg;base64,<?php echo $qr_code; ?>"  width="150" height="150"/></p>
          <div style="padding:0 15px"><b><?php echo $qr_description; ?></b></div>
        </td>
        <?php endif; ?>
      </tr>
    </tbody>
  </table>
  <div class="buttons">
    <div class="pull-right"><a id="button-confirm" class="btn btn-primary" data-loading-text="<?php echo $text_loading; ?>" href="<?php echo $continue; ?>" class="button"><?php echo $button_continue; ?></a></div>

    <?php if($test_mode) : ?>
      <div class="pull-left">
        <?php echo $test_mode_label; ?><input style="margin: 0 4px;" type="button" id="send_notify_success" class="btn btn-primary" value="<?php echo $text_send_notify_success; ?>" />
        <input type="button" id="send_notify_cancel" class="btn btn-primary" value="<?php echo $text_send_notify_cancel; ?>" />
      </div>

      <script type="text/javascript">
        $(document).ready(function() {
          $('#send_notify_success').click(function() {
            send_notify(1, '<?php echo $signature_success; ?>');
          });

          $('#send_notify_cancel').click(function() {
            send_notify(2, '<?php echo $signature_cancel; ?>');
          });

          function send_notify(type, signature) {
            $.post('<?php echo HTTPS_SERVER . "index.php?route=payment/epos_expresspay/notify" ?>', 'Data={"CmdType": ' + type + ', "AccountNo": <?php echo $order_id; ?>}&Signature=' + signature, function(data) {alert(data);})
            .fail(function(data) {
              alert(data.responseText);
            });
          }
        });
      </script>
    <?php endif; ?>
  </div>
  <?php echo $content_bottom; ?>
</div>
<?php echo $footer; ?>
