jQuery(document).ready(function ($) {
  $('.install-wp301').on('click',function(e){
    e.preventDefault();

    if (!confirm('The free WP 301 Redirects plugin will be installed & activated from the official WordPress repository. Click OK to proceed.')) {
      return false;
    }

    jQuery('body').append('<div style="width:550px;height:450px; position:fixed;top:10%;left:50%;margin-left:-275px; color:#444; background-color: #fbfbfb;border:1px solid #DDD; border-radius:4px;box-shadow: 0px 0px 0px 4000px rgba(0, 0, 0, 0.85);z-index: 9999999;"><iframe src="' + no_right_click_images_vars.wp301_install_url + '" style="width:100%;height:100%;border:none;" /></div>');
    jQuery('#wpwrap').css('pointer-events', 'none');

    e.preventDefault();
    return false;
  });

  $('.install-wpcaptcha').on('click',function(e){
    e.preventDefault();

    if (!confirm('The free WP Captcha plugin will be installed & activated from the official WordPress repository. Click OK to proceed.')) {
      return false;
    }

    jQuery('body').append('<div style="width:550px;height:450px; position:fixed;top:10%;left:50%;margin-left:-275px; color:#444; background-color: #fbfbfb;border:1px solid #DDD; border-radius:4px;box-shadow: 0px 0px 0px 4000px rgba(0, 0, 0, 0.85);z-index: 9999999;"><iframe src="' + no_right_click_images_vars.wpcaptcha_install_url + '" style="width:100%;height:100%;border:none;" /></div>');
    jQuery('#wpwrap').css('pointer-events', 'none');

    e.preventDefault();
    return false;
  });

  function no_right_click_images_fix_dialog_close(event, ui) {
    jQuery('.ui-widget-overlay').bind('click', function () {
      jQuery('#' + event.target.id).dialog('close');
    });
  } // no_right_click_images_fix_dialog_close
});
