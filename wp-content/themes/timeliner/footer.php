<a href="javascript:;" class="to_top btn">
	<span class="fa fa-caret-up fa-2x"></span>
</a>
<?php
wp_footer();
?>

<div id="footer-sidebar" class="secondary">
<div class = "col-md-4">
<div id="footer-sidebar1">
<?php
if(is_active_sidebar('footer-sidebar-1')){
dynamic_sidebar('footer-sidebar-1');
}
?>
</div>
</div>
<div class = "col-md-4">
<div id="footer-sidebar2">
<?php
if(is_active_sidebar('footer-sidebar-2')){
dynamic_sidebar('footer-sidebar-2');
}
?>
</div>
</div>
<div class = "col-md-4">
<div id="footer-sidebar3">
<?php
if(is_active_sidebar('footer-sidebar-3')){
dynamic_sidebar('footer-sidebar-3');
}
?>
</div>
</div>
</div>
<div class="col-xs-12" style="text-align:center">
<p>Copyright &copy NETpositive Futures Ltd & Stockholm Environment Institute 2014 | <a href="https://testsite-netpos.rhcloud.com/terms-and-conditions">Terms & Conditions</a>
</div>