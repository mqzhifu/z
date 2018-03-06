var Login = function () {

	var handleLogin = function() {
		$('.login-form').validate({
	            errorElement: 'span', //default input error message container
	            errorClass: 'help-block', // default input error message class
	            focusInvalid: false, // do not focus the last invalid input
	            rules: {
	                username: {
	                    required: true
	                },
	                password: {
	                    required: true
	                },
					verify: {
	                    required: true,
						rangelength:[4,4]
	                }
	            },

	            messages: {
	                username: {
	                    required: "用户名必填写."
	                },
	                password: {
	                    required: "密码必填写."
	                },
					verify: {
						required: '验证码必填写.',
						rangelength:'只能输入4个字符'
					}
	            },

	            invalidHandler: function (event, validator) { //display error alert on form submit   
	                $('.alert-danger', $('.login-form')).show();
	            },

	            highlight: function (element) { // hightlight error inputs
	                $(element)
	                    .closest('.form-group').addClass('has-error'); // set error class to the control group
	            },

	            success: function (label) {
	                label.closest('.form-group').removeClass('has-error');
	                label.remove();
	            },

	            errorPlacement: function (error, element) {
	                error.insertAfter(element.closest('.input-icon'));
	            },

	            submitHandler: function (form) {
					var username = $("#username").val();
					var ps =  $("#password").val();
					var verify = $("#verify").val();
					var url = "?ac=loginuser&username="+username+"&password="+ps+"&verify="+verify;
					$.ajax({
						type: 'GET',
						url: url ,
						success:function(data) {
							var d = eval("("+data+")");
							if(200 != d.code){
								$('.alert-danger', $('.login-form')).show();
							}else{
								location.href="/admin.php";
							}
						}
					});
	                //form.submit();
	            }
	        });

	        $('.login-form input').keypress(function (e) {
	            if (e.which == 13) {
	                if ($('.login-form').validate().form()) {
	                    $('.login-form').submit();
	                }
	                return false;
	            }
	        });
	}


    return {
        //main function to initiate the module
        init: function () {
        	
            handleLogin();
        }

    };

}();