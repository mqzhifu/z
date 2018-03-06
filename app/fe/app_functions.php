<?php
function getMsgRedisKey($service_id,$sess_id){
    return "msg_".$service_id."_".$sess_id;
}