<?php


namespace app\sample\controller;

use think\Request;

class Test {
    public function hello(Request $request) {
        $all = $request->post("");
        var_dump($all);
    }
}