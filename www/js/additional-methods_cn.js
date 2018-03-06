jQuery.validator.addMethod("mobile", function(value, element) {
    value = value.replace(/(^\s*)|(\s*$)/g, "");
    var length = value.length;
    var mobile = /^1[3,5,8]\d{9}$/;
    return this.optional(element) || (length == 11 && mobile.test(value));
}, "手机号码格式错误");

jQuery.validator.addMethod("int", function(value, element) {
    value = value.replace(/(^\s*)|(\s*$)/g, "");
    var chrnum = /^([1-9])[0-9]*$/;
    return this.optional(element) || (chrnum.test(value));
}, "正整数,(1-9)开头");
jQuery.validator.addMethod("new_mobile", function(value, element) {
    value = value.replace(/(^\s*)|(\s*$)/g, "");
    var chrnum = /^([1-9])[0-9]*$/;
    return this.optional(element) || (chrnum.test(value));
}, "手机号码格式错误");
jQuery.validator.addMethod("letter", function(value, element) {
    value = value.replace(/(^\s*)|(\s*$)/g, "");
    var chrnum = /^[A-Za-z]+$/;
    return this.optional(element) || (chrnum.test(value));
}, "请输入字母");
jQuery.validator.addMethod("email_new", function(value, element) {
    value = value.replace(/(^\s*)|(\s*$)/g, "");
    var chrnum = /^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/;
    return this.optional(element) || (chrnum.test(value));
}, "邮件格式错误");
jQuery.validator.addMethod("card_id", function(value, element) {
    value = value.replace(/(^\s*)|(\s*$)/g, "");
    var chrnum =  /^[1-9]\d{5}[1-9]\d{3}((0[1-9])|(1[0-2]))((0[1-9])|([1-2][0-9])|(3[0-1]))\d{3}(\d|x|X)$/;
    return this.optional(element) || (chrnum.test(value));
}, "请填写正确的身份证号码");
jQuery.validator.addMethod("price", function(value, element) {
    value = value.replace(/(^\s*)|(\s*$)/g, "");
    var chrnum =  /^[-\+]?\d+(\.\d+)?$/;
    return this.optional(element) || (chrnum.test(value));
}, "请填写正确的价格");
jQuery.validator.addMethod("d_point", function(value, element) {
    var point = value.indexOf('.');
    var eee = true;
    if(point != -1){
        var str = value.substring(point + 1);
        if(str.length > 2){
            eee = false;
        }

        value_2  = parseInt(value);
        if(value_2 <= 0){
            var tt = value.substring(point + 1,point + 3);
            tt = parseInt(tt);
            if(tt <=0)
                eee = false;
        }
    }


    return this.optional(element) || eee;
}, "最多两位小数组不能为0.00");

jQuery.validator.addMethod("contact_required", function(value, element) {
    value = value.replace(/(^\s*)|(\s*$)/g, "");
    if(value == '选择常用联系人')
        return false;

    if(!value)
        return false;

    return true;

}, "必填写");


//身份证验证
jQuery.validator.addMethod("idcard", function(value, element) {
    var length = value.length;
    if(18 != length) return false;

    var lastNum = value.substr(value.length - 1,1);
    var twnNum = value.substr(0,2);
//var aCity=array(
//			11=>"北京",12=>"天津",13=>"河北",14=>"山西",15=>"内蒙古",21=>"辽宁",22=>"吉林",23=>"黑龙江",31=>"上海",32=>"江苏",
//			33=>"浙江",34=>"安徽",35=>"福建",36=>"江西",37=>"山东",41=>"河南",42=>"湖北",43=>"湖南",44=>"广东",45=>"广西",46=>"海南",
//			50=>"重庆",51=>"四川",52=>"贵州",53=>"云南",54=>"西藏",61=>"陕西",62=>"甘肃",63=>"青海",64=>"宁夏",65=>"新疆",71=>"台湾",
//			81=>"香港",82=>"澳门",91=>"国外");
    var aCity= new Array(
        11,12,13,14,15,21,22,23,31,32,
        33,34,35,36,37,41,42,43,44,45,46,
        50,51,52,53,54,61,62,63,64,65,71,
        81,82,91
    );

    var f = 0;

    for(i=0;i<aCity.length;i++){
        if(aCity[i] == twnNum){
            f= 1;break;
        }
    }

    if(!f){
//	alert('省份所对应的ID错误' );
        return false;
    }

//判断出生日期
    var date = value.substr(6,8);
    var rg = /^(1|2)[0-9]{3}[0-9]{2}[0-9]{2}$/;
    if( ! rg.test(date) ){
        return false;
    }

//性别
    var sex = value.substr(16,1);
//if(sex % 2 == 0) var sexDesc = "女";
//else var sexDesc = "男";
//验证第18位
//将身份证号码前17位数分别乘以不同的系数
    var code = new Array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
//将得到的17个乘积相加。将相加后的和除以11并得到余数,如下
    var szVerCode = new Array('1','0','X','9','8','7','6','5','4','3','2');
    var total = 0;
    for (i = 0;i < 17;i++) {
        var str = value.substr(i,1);
        total += parseInt( str ) * parseInt(code[i]);
    }
    if(szVerCode[ total % 11 ] != lastNum ){
//	alert('第18位验证码错误');
        return false;
    }
// 		6位地址码,因为数组较大，所以在客服端就不做验证了，在服务端处理
    return true;
}, "请填写正确有效的身份证号码");