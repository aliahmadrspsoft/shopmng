<?php

class IndexController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */
    }

    public function indexAction() {
      
        Zend_Session::forgetMe();
        Zend_Session::destroy();
        $post = $this->getRequest()->getParams();
        $this->view->error = $post['error'];
    }

    public function loginAction() {
        $post = $this->getRequest()->getPost();
        $poolFunction = new Application_Model_Func_Function();
        if ($post['usr'] == 'Admin') {
            if ($post['pwd'] == '123456') {
                $authNamespace = Zend_Registry::get("authNamespace");
                $authNamespace->isLogin = true;
                $authNamespace->isAdmin = true;
                $authNamespace->name = "مدیر سیستم";
                $authNamespace->unmngabsent = false;
                $menu = $poolFunction->getMenu(true);
                $authNamespace->menu = $menu;
                $this->_redirect('/console');
            } else {
                $this->_redirect('/index?error=1');
            }
        } else {
            $userfunc = new Application_Model_Func_Users();

            $user = $userfunc->checkpass($post['usr'], $post['pwd']);

            if (count($user) > 0) {
                $authNamespace = Zend_Registry::get("authNamespace");
                $authNamespace->isLogin = true;
                $authNamespace->name = $post['usr'];
                $authNamespace->ACL = json_decode(base64_decode($user[0]['ACL']), true);
                $authNamespace->loginTime = mktime(date("H"), date("i"), 0, date('m'), date('d'), date('Y'));
                $authNamespace->unmngabsent = false;

                $menu = $poolFunction->getMenu(false);
                $authNamespace->menu = $menu;
                $yesterday = $poolFunction->yesterday();
                $countunmngabsent = $poolFunction->get_un_mng_absentcount($yesterday);

                if ($countunmngabsent > 0 && $user[0]['FORCEABSENTREPORT'] == 1) {
                    $authNamespace->unmngabsent = true;
                    $this->_redirect('/report/absent?alert=yes&getdate=' . $yesterday);
                } else {
                    $this->_redirect('/console');
                }
            } else {
                $this->_redirect('/index?error=1');
            }
        }
    }

    public function logoutAction() {
        Zend_Session::forgetMe();
        Zend_Session::destroy();
        $this->_redirect('/');
    }

}
