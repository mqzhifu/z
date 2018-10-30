<?php
function get_img($path){
    return "http:/local.static.com/upload/".APP_NAME.DS.$path;
}

function get_domain_url($protocol = 'http'){
    return $protocol."://".DOMAIN_URL."/";
}

function get_static_url($protocol = 'http'){
    return $protocol."://".STATIC_URL."/";
}