<?php

namespace app\api\controller;

use app\api\service\Token;
use think\Controller;

class BaseController extends Controller {
    protected function checkExclusiveScope() {
        //用户专有权限
        Token::needExclusiveScope();
    }

    protected function checkPrimaryScope() {

        Token::needPrimaryScope();
    }

    protected function checkSuperScope() {
        //管理员专业权限
        Token::needSuperScope();
    }
}