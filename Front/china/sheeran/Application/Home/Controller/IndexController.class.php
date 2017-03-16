<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
        $list = $this->getValue();
    }

    public function getValue()
    {
        $parent = array(0);
        $handle = fopen('D:/one/sheeran/Application/Home/Controller/a.txt', 'r');
        $arr = array();
        while (!feof($handle)) {
            //读取一行
            $row = trim(str_replace('　', ' ', fgets($handle)));
            if (!preg_match('/^(\d+)\s+(.+)$/', $row, $matches)) {
                continue;
            }

            list($row, $id, $name) = $matches;
            //110000 , 110200, 110204(看规律取)
            $level = strlen(preg_replace('/(00){1,2}$/', '', $id)) / 2;
            $parent_id = $parent[$level - 1];
            $parent[$level] = $id;
            $arr[]=array(
                'id' =>$id,
                'parent_id' => $parent_id,
                'name' => $name,
                'level' => $level,
            );
            $data = array(
                'id' =>$id,
                'parent_id' => $parent_id,
                'name' => $name,
                'level' => $level,
            );
            D('region')->add($data);
        }
        fclose($handle);
        return $arr;
    }
}