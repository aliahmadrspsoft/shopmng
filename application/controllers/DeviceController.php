<?php

class DeviceController extends Zend_Controller_Action {

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
        
    }

    public function settingAction() {
        $Function = new Application_Model_Func_Device();      
        $result = $Function->fetchsetting();
        $this->view->datasetting = $result;
    }

    public function settingsaveAction() {
        $this->_helper->layout->disableLayout();
        $Function = new Application_Model_Func_Device();
        $post = $this->getRequest()->getPost();

        if (empty($post['SMSURL'])) {
            echo '<div  style="background: #f7acbe;text-align: center;margin-top: 5px;"'
            . ' class="label label-danger"> آدرس وب سرویس ارسال پیامک را وارد کنید </div>';
            exit;
        }


        $result = $Function->savesettinginfo($post);
        if (!$result) {
            echo '<div  style="background: #f7acbe;text-align: center;margin-top: 5px;" class="label label-danger"> خطا در انجام عملیات</div>';
        } else {
            echo '<div  style="background: green;text-align: center;margin-top: 5px;" class="label label-info">عملیات با موفقیت انجام شد</div>';
        }
        exit;
    }

    public function fetchdataAction() {
        $this->_helper->layout->disableLayout();
        $Function = new Application_Model_Func_Device();
        $post = $this->getRequest()->getPost();
        $result = $Function->fetchbyID($post['ID']);
        $this->view->datadevice = $result;
    }

    public function editAction() {
        $this->_helper->layout->disableLayout();
        $Function = new Application_Model_Func_Device();
        $post = $this->getRequest()->getPost();
        $detail = array(
            'NAMESET' => 'نام دستگاه',
            'KINDSET' => 'نوع دستگاه',
            'IPSET' => 'ip', 'PORTSET' => 'پورت',
            'GPSSET' => 'موقعیت', 'NUMSET' => 'شماره دستگاه',
            'STATUSSET' => 'وضعیت دستگاه',
            'GETDATATYPE' => 'نوع تخلیه داده ها'
        );
        foreach ($detail as $key => $val) {
            if (empty($post[$key])) {
                echo '<div  style="background: #f7acbe;text-align: center;margin-top: 5px;"'
                . ' class="label label-danger"> ' . $val . '  را وارد کنید </div>';
                exit;
            }
        }

        $result = $Function->saveinfo($post);
        if (!$result) {
            echo '<div  style="background: #f7acbe;text-align: center;margin-top: 5px;" class="label label-danger"> خطا در انجام عملیات</div>';
        } else {
            echo '<div  style="background: green;text-align: center;margin-top: 5px;" class="label label-info">عملیات با موفقیت انجام شد</div>';
        }
        exit;
    }

    public function getinfoAction() {
        $post = $this->getRequest()->getParams();
        $SSP = new Application_Model_Func_SSP();
        $table = 'TBL_KHC_DEVICE';
        $primaryKey = "ID";
        $columns = array(
            array('db' => 'KINDSET', 'dt' => 0),
            array('db' => 'NAMESET', 'dt' => 1),
            array('db' => 'IPSET', 'dt' => 2),
            array('db' => 'PORTSET', 'dt' => 3),
            array('db' => 'GPSSET', 'dt' => 4),
            array('db' => 'NUMSET', 'dt' => 5),
            array('db' => 'STATUSSET', 'dt' => 6),
            array('db' => '1', 'dt' => 7, 'formatter' => function ($d, $row) {
                    return '<a data-toggle="modal" data-target="#infodevice" onclick="editmodal(\'' . $row['ID'] . '\')" class="btn btn-primary"><i class="fa fa-fw fa-edit" ></i></a>' .
                    '<a  onclick="deletemodal(\'' . $row['ID'] . '\',\'' . $row['NAMESET'] . '\')" class="btn btn-danger"><i  class="fa fa-fw fa-trash"></i></a>';
                }),
            array('db' => 'ID', 'dt' => 8)
        );
        $db = Zend_Registry::get("db");
        try {
            echo json_encode(
                    Application_Model_Func_SSP::simple($post, $db, $table, $primaryKey, $columns)
            );
        } catch (Exception $e) {
            var_export($e->getMessage());
        }
        exit;
    }

    public function deletedvcAction() {
        $this->_helper->layout->disableLayout();
        $post = $this->getRequest()->getParams();
        $funcdvc = new Application_Model_Func_Device();
        $result = $funcdvc->deletedvc($post);
        if ($result) {
            echo"<div class=\"alert alert-success infoalert\">عملیات با موفقیت انجام شد </div>";
        } else {
            echo"<div class=\"alert alert-danger infoalert\">خطا در انجام عملیات </div>";
        }
        exit;
    }

}
