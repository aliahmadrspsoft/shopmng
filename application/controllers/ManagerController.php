<?php

class ManagerController extends Zend_Controller_Action {

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

    public function fetchdataAction() {
        $this->_helper->layout->disableLayout();
        $Function = new Application_Model_Func_Function();
        $post = $this->getRequest()->getPost();
        $result = $Function->fetchbycode($post['code']);

        $this->view->datastudent = $result;
    }

    public function editAction() {
        $this->_helper->layout->disableLayout();
        $Function = new Application_Model_Func_Function();
        $post = $this->getRequest()->getPost();
        if (empty($post['codecart'])) {
            echo '<div  style="background: #f7acbe;text-align: center;margin-top: 5px;" class="label label-danger"> کد شناسایی را وارد کنید</div>';
            exit;
        }

        $result = $Function->saveinfo($post);
        if (!$result) {
            echo '<div  style="background: #f7acbe;text-align: center;margin-top: 5px;" class="label label-danger"> خطا در انجام عملیات</div>';
        } else {
            echo '<div  style="background: green;text-align: center;margin-top: 5px;" class="label label-info">عملیات با موفقیت انجام شد</div>';
        }
        exit;
    }

    public function chkvalAction() {
        $this->_helper->layout->disableLayout();
        $Function = new Application_Model_Func_Function();
        $post = $this->getRequest()->getPost();
        $result = $Function->fetchbycodecart($post['codecart']);
        if (count($result) > 0) {
            echo '<div  style="background: #f7acbe;text-align: center;margin-top: 5px;" class="label label-danger"> این کد قبلا به دانشجوی ' . $result[0]['studentid'] . ' اختصاص داده شده</div>';
        } else {
            echo '<div  style="background: green;text-align: center;margin-top: 5px;" class="label label-info">کد معتبر می باشد</div>';
        }
        exit;
    }

    public function getinfoAction() {
        $post = $this->getRequest()->getParams();
        $SSP = new Application_Model_Func_SSP();
        $table = '(select * from TBL_KHC_STU_INFO_BASE m LEFT JOIN TBL_IDM_MANAGER k ON m.code=k.studentid)tbl ';
        $primaryKey = "CODE";
        $columns = array(
            array('db' => 'NAME_FAMILY', 'dt' => 0),
            array('db' => 'CODE', 'dt' => 1),
            array('db' => 'CODECART', 'dt' => 2),
            array('db' => 'INDORM', 'dt' => 3, 'formatter' => function ($d, $row) {

                    return ($d == 1) ? '<i class="fa fa-fw fa-check info"></i>' : '<i class="fa fa-fw fa-times danger"></i>';
                }),
            array('db' => 'WIKEND', 'dt' => 4, 'formatter' => function ($d, $row) {

                    return ($d == 1) ? '<i class="fa fa-fw fa-check info"></i>' : '<i class="fa fa-fw fa-times danger"></i>';
                }),
            array('db' => '1', 'dt' => 5, 'formatter' => function ($d, $row) {
                    return '<a class="btn btn-social-icon btn-dropbox"><i onclick="editmodal(\'' . $row['CODE'] . '\')" class="fa fa-fw fa-edit" data-toggle="modal" data-target="#infostudent"></a>';
                })
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

    public function inputexcelAction() {
        
    }

    public function insertexcelAction() {
        ini_set('max_execution_time', 0);
        $this->_helper->layout->disableLayout();
        $funcexcel = new Application_Model_Func_Excel();

        $authNamespace = Zend_Registry::get("authNamespace");
        if (empty($authNamespace->ind)) {
            $authNamespace->ind = 0;
        }

        $set_total = count($funcexcel->fileArray);
        foreach ($funcexcel->fileArray as $student) {
            $ind++;
            $percent = intval(($ind * 100) / $set_total) . "%";

            $txtres = $funcexcel->saveStudent($student);

            echo '<script  language="javascript" >
    parent.document.getElementById("progressbar").innerHTML="<div style=\"width:' . $percent . ';background:linear-gradient(to bottom, rgba(126,126,126,1) 0%,rgba(15,15,15,1) 100%); ;height:36px;\">&nbsp;</div>";
    parent.document.getElementById("informations").innerHTML ="<div style=\"text-align:center; font-weight:bold\"> عملیات انجام شد' . $percent . '</div>";'
            . ' parent.document.getElementById("noticDiv").innerHTML +="<br/>' . $txtres . '";'
            . '</script>';

            // ob_flush and flush process
            ob_flush();
            flush();
            sleep(1);
        }
        echo '<script language="javascript"> parent.document.getElementById("informations").innerHTML="<div style=\"text-align:center; font-weight:bold\">عملیات به اتمام رسید</div>"</script>';
        $authNamespace->ind = 0;
        exit;
    }

}
