<?php
/**
 * Created by PhpStorm.
 * User: sheeran
 * Date: 2016/11/1
 * Time: 9:51
 */

class Calculator {

    protected $all;
    protected $cost;
    protected $value=[
        'total'=>5600,
        'house'=>700,
        'life' => 700,
        'render' =>200,
    ];

    public function getAll()
    {
        return $this->all;
    }

    public function getCost()
    {
        return $this->cost;
    }

    public function setAll($all)
    {
        $this->all=$all;
    }

    public function setCost($cost)
    {
        $this->cost=$cost;
    }

    public function surplus()
    {
        if (!$this->getAll()) {
            throw new Exception("Value must be filled");
        }
        if (!$this->getCost()) {
            throw new Exception("Value must be filled");
        }
        return $this->all-$this->cost;
    }

    public function p()
    {
        echo '<pre>';
        print_r($this->surplus());
        echo '<pre>';
    }

}
$n='1,2,5,8,10';
$n1='91,92,95,98,100';

echo '<pre>';
print_r(count($n));
