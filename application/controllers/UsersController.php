<?php

class UsersController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */
        $authNamespace = Zend_Registry::get("authNamespace");
        if (!$authNamespace->isLogin) {
            $this->_redirect('/');
        } else {
            $this->_helper->layout->setLayout("dashboard_layout");
            $func = new Application_Model_Func_Access();
            if ($authNamespace->isAdmin) {
                $result = array('result' => true);
            } else {
                $result = $func->checkAccess($this->getRequest());
            }
            //  var_export($result);exit;
            if ($result['result']) {
                if ($authNamespace->unmngabsent) {
                    $action = $this->getRequest()->getActionName();
                    $controller = $this->getRequest()->getControllerName();
                    if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == '') :
                        if ($action != "absent" || $controller != "report") {
                            $poolFunction = new Application_Model_Func_Function();
                            $yesterday = $poolFunction->yesterday();
                            $this->_redirect('/report/absent?alert=yes&getdate=' . $yesterday);
                        }
                    endif;
                }
            } else {
                if ($result['error'] == 2) {
                    Zend_Session::forgetMe();
                    Zend_Session::destroy();
                    $this->_redirect('/index?error=2');
                }
                if ($result['error'] == 3) {
                    Zend_Session::forgetMe();
                    Zend_Session::destroy();
                    $this->_redirect('/index?error=3');
                }
            }
        }
    }

    public function indexAction() {
        $funcusr = new Application_Model_Func_Users();
        $users = $funcusr->getallusers();
        $this->view->users = $users;
    }

    public function updateactiveAction() {
        $this->_helper->layout->disableLayout();
        $post = $this->getRequest()->getParams();
        $funcusr = new Application_Model_Func_Users();
        $result = $funcusr->saveusers($post);
        if ($result) {
            echo"<div class=\"alert alert-success infoalert\">عملیات با موفقیت انجام شد </div>";
        } else {
            echo"<div class=\"alert alert-danger infoalert\">خطا در انجام عملیات </div>";
            echo "";
        }
        exit;
    }

    public function updateforceAction() {
        $this->_helper->layout->disableLayout();
        $post = $this->getRequest()->getParams();
        $funcusr = new Application_Model_Func_Users();
        $result = $funcusr->saveusers($post);
        if ($result) {
            echo"<div class=\"alert alert-success infoalert\">عملیات با موفقیت انجام شد </div>";
        } else {
            echo"<div class=\"alert alert-danger infoalert\">خطا در انجام عملیات </div>";
            echo "";
        }
        exit;
    }

    public function deleteusersAction() {
        $this->_helper->layout->disableLayout();
        $post = $this->getRequest()->getParams();
        $funcusr = new Application_Model_Func_Users();
        $result = $funcusr->deleteusr($post);
        if ($result) {
            echo"<div class=\"alert alert-success infoalert\">عملیات با موفقیت انجام شد </div>";
        } else {
            echo"<div class=\"alert alert-danger infoalert\">خطا در انجام عملیات </div>";
            echo "";
        }
        exit;
    }

    public function adduserAction() {
        $this->_helper->layout->disableLayout();
    }

    public function changepasswordAction() {
        $this->_helper->layout->disableLayout();
        $post = $this->getRequest()->getParams();
        $funcusr = new Application_Model_Func_Users();
        $result = $funcusr->getallusers($post['userid']);
        // var_export($result);
        if (count($result) == 0) {
            die('خطا در دریافت اطلاعات کاربر');
        }
        $this->view->user = $result[0];
    }

    public function savepasswordusersAction() {
        $this->_helper->layout->disableLayout();
        $post = $this->getRequest()->getParams();
        $funcusr = new Application_Model_Func_Users();
        $result = $funcusr->checkpass($post['name'], $post['oldpassword']);
        $authNamespace = Zend_Registry::get("authNamespace");
        if (count($result) > 0 || $authNamespace->isAdmin == true) {
            $saveresult = $funcusr->saveusers(array('id' => $post['id'], 'password' => $post['newpassword']));
            if ($saveresult) {
                echo"<div class=\"alert alert-success infoalert\">عملیات با موفقیت انجام شد </div>";
            } else {
                echo"<div class=\"alert alert-danger infoalert\">خطا در انجام عملیات</div>";
            }
        } else {
            echo"<div class=\"alert alert-danger infoalert\">اطلاعات را با دقت وارد کنید</div>";
        }
        exit;
    }

    public function saveusersAction() {
        $this->_helper->layout->disableLayout();
        $post = $this->getRequest()->getParams();
        $funcusr = new Application_Model_Func_Users();
        $result = $funcusr->saveusers($post);
        if ($result) {
            echo"<div class=\"alert alert-success infoalert\">عملیات با موفقیت انجام شد </div>";
        } else {
            echo"<div class=\"alert alert-danger infoalert\">خطا در انجام عملیات </div>";
            echo "";
        }
        exit;
    }

    public function saveaddusersAction() {
        $this->_helper->layout->disableLayout();
        $post = $this->getRequest()->getParams();
        $funcusr = new Application_Model_Func_Users();
        $users = $funcusr->getallusersbyname($post["name"]);
        if (count($users) > 0) {
            echo"<div class=\"alert alert-danger infoalert\">کاربری با این نام وجود دارد </div>";
            exit;
        }
        $result = $funcusr->saveusers($post);
        if ($result) {
            echo"<div class=\"alert alert-success infoalert\">عملیات با موفقیت انجام شد </div>";
        } else {
            echo"<div class=\"alert alert-danger infoalert\">خطا در انجام عملیات </div>";
            echo "";
        }
        exit;
    }

    public function getalluserlistAction() {
        $this->_helper->layout->disableLayout();
        $Function = new Application_Model_Func_Function();
        $post = $this->getRequest()->getParams();
        $SSP = new Application_Model_Func_SSP();
        $table = 'TBL_IDM_USER';
        $primaryKey = "name";
        $columns = array(
            array('db' => 'NAME', 'dt' => 0),
            array('db' => 'ACL', 'dt' => 1),
            array('db' => 'PASSWORD', 'dt' => 2),
            array('db' => 'ACTIVE', 'dt' => 3),
            array('db' => 'FORCEABSENTREPORT', 'dt' => 4),
        );
        $db = Zend_Registry::get("db");
        $arr = Application_Model_Func_SSP::simple($post, $db, $table, $primaryKey, $columns);

        for ($i = 0; $i < count($arr['data']); $i++) {
            $arr['data'][$i][1] = '<a class="btn btn-social-icon"><i onclick="editmodal(\'editaccess\', {acl:\'' . $arr['data'][$i][1] . '\',userid: \'' . $arr['data'][$i][0] . '\'})" 
                                           class="fa fa-fw fa-edit" data-toggle="modal" data-target="#infousers"></i>
                                    </a>';
            $arr['data'][$i][2] = '<a class="btn btn-social-icon "><i onclick="editmodal(\'changepassword\', {userid: \'' . $arr['data'][$i][0] . '\'})" 
                                           class="fa fa-fw fa-edit" data-toggle="modal" data-target="#infousers"></i>
                                    </a>';
            if ($arr['data'][$i][3]) {
                $checked = "checked='checked'";
            } else {
                $checked = "";
            }
            $arr['data'][$i][3] = ' <div class="checkbox"> <label>
                                            <input type="checkbox" ' . $checked . ' value="1" onchange="saveactive(\'' . $arr['data'][$i][0] . '\', $(this).is(\':checked\'), $(this).parent().parent())" name="active[' . $arr['data'][$i][0] . ']"> </label></div>';
            if ($arr['data'][$i][4]) {
                $checkedforce = "checked='checked'";
            } else {
                $checkedforce = "";
            }
            $arr['data'][$i][4] = ' <div class="checkbox"> <label>
                                            <input type="checkbox" ' . $checkedforce . ' value="1" onchange="saveforce(\'' . $arr['data'][$i][0] . '\', $(this).is(\':checked\'))" > </label></div>';
            $arr['data'][$i][5] = '<a class="btn btn-social-icon"><i onclick="deletemodal(\'' . $arr['data'][$i][0] . '\', \'' . $arr['data'][$i][0] . '\')" class="fa fa-fw fa-trash"></i></a>';
        }

        try {
            echo json_encode(
                    $arr
            );
        } catch (Exception $e) {
            var_export($e->getMessage());
        }
        exit;
    }

    public function editaccessAction() {
        $this->_helper->layout->disableLayout();
        $post = $this->getRequest()->getPost();
        $this->view->userid = $post['userid'];
        if (isset($post['acl'])) {
            $accessList = json_decode(base64_decode($post['acl']), true);
            //  var_export($accessList);
            $this->view->acls = $accessList;
        } else {
            echo"<div class=\"alert alert-danger infoalert\">خطا در انجام عملیات.نقص در اطلاعات ارسالی </div>";
            exit;
        }
        $accessFunc = new Application_Model_Func_Access();
        $accessList = $accessFunc->getAccess();
        $this->view->accessList = $accessList;
    }

    public function saveaccessAction() {
        $this->_helper->layout->disableLayout();
        $post = $this->getRequest()->getPost();

        if (isset($post['userid'])) {
            $user = array('name' => $post['userid'], 'access' => base64_encode(json_encode($post['accesschk'])));
        } else {
            echo"<div class=\"alert alert-danger infoalert\">خطا در انجام عملیات.نقص در اطلاعات ارسالی </div>";
            exit;
        }

        $accessFunc = new Application_Model_Func_Users();
        $accessResult = $accessFunc->saveusers($user);
        if ($accessResult == true) {
            echo"<div class=\"alert alert-success infoalert\">عملیات با موفقیت انجام شد. </div>";
        } else {
            echo"<div class=\"alert alert-danger infoalert\">خطا در انجام عملیات </div>";
            echo "";
        }
        exit;
    }

}
