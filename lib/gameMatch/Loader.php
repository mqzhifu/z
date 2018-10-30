<?php

class Loader{
    function model($className,$class){
        return new $className;
    }
}