<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style>
.errorback{background-color:#a6aeb8; width:1336px;}
.errorpic{background:url(<?php echo $STATIC_URL; ?>/common_img/error.png) no-repeat; width:1007px; height:538px; margin:0 auto;}
.errortop{width:690px;;padding-top:22px; padding-left:18px;}
p{font-size:16px; color:#9099a2; line-height:25px; font-weight:bold;}
td{color:#9099a2; font-size:14px;}
table{padding-left:50px; padding-top:6px; padding-bottom:6px;}
.iconfont{padding-top: 12px; width:10px;}
.message{margin:0 auto;border-bottom:1px solid #dfe3e8; padding:6px 0 6px 0;}
.float{float:left;}
.Green{color:#13a992;}
.Blue{color:#0466c8;}
.Red{color:#e95259;}


</style>
</head>

<body class="errorback">
<div style="padding-top:40px;"></div>
<div class="errorpic">
    <div class="errortop">
        <p class="Blue"><?php echo $e; ?></div>
        
     <div style="width:600px;">
       <table border="0">
          <tr>
           <?php echo $traceInfo; ?>
  </tr>
   </table>
        
</div>
</div>
</body>
</html>
