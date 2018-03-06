/*
 游心后台AJAX请求

 必须按照后台标准JSON返回

 __Examples__

 UTHING.ajax({
 url:"a.php",
 type:"get",//可选择GET或POST,默认GET
 data:{id:1,name:"aaa"},
 title:"管理页面",
 bootboxtype:"dialog",//可选择dialog或者alert
 size:"large",  //可选择large大或者small小，或者为空
 success:function(ret,UTbox){
 alert(111);//执行成功后要执行的函数]
 },
 error:function(ret,UTbox){
 alert(222);//执行失败后要执行的函数
 }
 });
 */



jQuery(function($) {

    if(typeof UTHING !== "object"){
        UTHING = {};
    }

    UTHING.ajax = function(options){
        if (typeof options !== "object") {
            throw new Error("参数需要是一个对象");
        }

        if (!options.url) {
            throw new Error("请求的URL必须指定");
        }
        if (!options.type) {
            options.type = "post";
        }
        if (!options.data) {
            options.data = "";
        }
        if (!options.title) {
            options.title = "";
        }

        if (!options.bootboxtype) {
            options.bootboxtype = "dialog";
        }

        if (!options.size) {
            options.size = "";
        }
        var UTbox = false;
        jQuery.ajax({
            url:options.url,
            type:options.type,
            dataType:"json",
            async:false,
            data:options.data,
            success:function(ret){
                if(ret.code === 200){
                    switch(options.bootboxtype){
                        case "dialog":
                            UTbox = UTHING.bootbox({
                                message: ret.data,
                                title: options.title,
                                size:options.size
                            });
                            break;
                        case "alert":
                            bootbox.hideAll();

                            UTbox = UTHING.bootbox({
                                message: ret.data,
                                type:"alert",
                                status : "success"
                            });

                            setTimeout(function(){
                                bootbox.hideAll();
                            },2000);
                            break;

                    }

                }else{

                    UTbox = UTHING.bootbox({
                        message: ret.data,
                        type:"alert",
                        status : "error"
                    });

                }
                if ( typeof options.success === "function" ) {
                    options.success = options.success.call(this, ret, UTbox);
                }
            },
            error:function(XMLHttpRequest, textStatus, errorThrown){
                UTbox = UTHING.bootbox({
                    message: XMLHttpRequest.status+" "+errorThrown,
                    type:"alert",
                    status : "error"
                });
                var ret = {XMLHttpRequest:XMLHttpRequest, textStatus:textStatus, errorThrown:errorThrown};
                if ( typeof options.error === "function" ) {
                    options.error = options.error.call(this, ret, UTbox);
                }
            }
        });
        return UTbox;
    }
});

