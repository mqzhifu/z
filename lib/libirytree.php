<?php
//最简单的  二叉树
class TwotreeLib{
    public $filePath = "";
    public $incElementId = 0;
    public $rootElement = null;
    public $elementList = null;
    //添加一个节点
    function add($data){

        echo "压入$data<br/>";
        if(!$this->elementList){
            $element = $this->createElement($data);
        }else{
            $element = $this->findAddElement($this->elementList[1],$data);
        }
        $this->elementList[$element['id']] = $element;
    }

//    function addElementList($element){
//
//    }ss

    function find($data){
        if($this->elementList){
            $el = $this->findElement($this->rootElement,$data);
            var_dump($el);
            return $el;
        }else{
            return null;
        }
    }
    //10 1 11 3 4 20
    function findAddElement($element,$data){
        if($element['data'] == $data){

        }elseif($data < $element['data'] ){
            if($element['child_left']){
                return $this->findAddElement($this->elementList[$element['child_left']],$data);
            }else{
                $newElement = $this->createElement($data);
                $newElement['parent'] = $element['id'];
                $newElement['data'] = $data;

                $this->elementList[$element['id']]['child_left'] = $newElement['id'];

                return $newElement;
            }
        }else{
            if($element['child_right']){
                return $this->findAddElement($this->elementList[$element['child_right']],$data);
            }else{
                $newElement = $this->createElement($data);
                $newElement['parent'] = $element['id'];
                $newElement['data'] = $data;

                $this->elementList[$element['id']]['child_right'] = $newElement['id'];

                return $newElement;
            }
        }
    }

    function findElement($element,$data){
        if( $data == $element['data'] ){
            return $element;
        }elseif($data < $element['data'] ){
            if($element['child_left']){
                return $this->findElement($this->elementList[$element['child_left']],$data);
            }
            return null;
        }else{
            if($element['child_right']){
                return $this->findElement($this->elementList[$element['child_right']],$data);
            }
            return null;
        }
    }

    function del($data){
        $element = $this->find($data);
        if(!$element)
            return null;

        unset($this->elementList[$element['id']]);

        if($element['child_left']){
            $this->elementList[$element['child_left']]['parent'] = $element['parent'];
        }

        if($element['child_right']){
            $this->elementList[$element['child_right']]['parent'] = $element['parent'];
        }

        return true;
    }

    //优化树-做平衡处理
    function balance(){
        return $this->calculationLayer($this->elementList[1],1);

    }
    //循环递归计算-层数
    function calculationLayer($element,$layerNum){
//        echo "层:".$layerNum."<br/>";
//        if($element['child_left']){
//            echo "左元素， id:".$element['child_left']." 值：". $this->elementList[$element['child_left']]['data']."<br/>";
//        }else{
//            echo "无左元素"."<br/>";
//        }
//
//        if($element['child_right']){
//            echo "右元素,".$element['child_right']." 值:".$this->elementList[$element['child_right']]['data']."<br/>";
//        }else{
//            echo "无右元素"."<br/>";
//        }


        if($element['child_left']){
            return $this->calculationLayer($this->elementList[$element['child_left']],++$layerNum);
        }

        if($element['child_right']){
            return $this->calculationLayer($this->elementList[$element['child_right']],++$layerNum);
        }

        return ++$layerNum;


    }

    function show(){
        if(!$this->elementList)
            exit('elementList is null');
        foreach($this->elementList as $k=>$v){
            print_r($v);
            echo "<br/>";
        }
    }

    function createElement($key){
        $arr = array(
            'id'=>++$this->incElementId,
            'parent'=>null,//父结点
            'child_left'=>null,//左-子结点
            'child_right'=>null,//右-子结点
            'data'=>$key,
        );

        return $arr;
    }

    function readFileLoadMem(){

    }
}

$c  = new TwotreeLib();
$c->add(10);
$c->add(1);
$c->add(11);
$c->add(3);
$c->add(4);
$c->add(20);
$c->add(16);
$c->add(7);
$c->add(2);
$c->add(5);
$c->add(13);
$c->add(6);




//1 2 3 4 5 7 10 11 16 25

$c->show();

$num = $c->balance();
var_dump($num);

