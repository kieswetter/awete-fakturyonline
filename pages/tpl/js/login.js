jQ(document).ready(function(){
	jQ('#login_js_missing').remove();
	
	jQ("#form_authentication").removeClass('hidden');
	
	jQ("#form_authentication").submit(function(){
		var val = hex_hmac_sha1(hex_sha1(jQ("input[name='password']").val()), jQ("input[name='challenge']").val());
		jQ("input[name='password_hmac']").attr('value',val);
		jQ("input[name='password']").attr('disabled','disabled');
		jQ(this).submit();
	    jQ("input[name='password']").removeAttr('disabled');
	    return false;
	});
});