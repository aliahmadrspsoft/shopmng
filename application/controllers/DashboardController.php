<?php

class DashboardController extends Zend_Controller_Action {

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
        $poolFunction = new Application_Model_Func_Function();
        $yesterday = $poolFunction->yesterday();

        $this->view->yesterday = $yesterday;
        $this->view->dataStudents = $result;
        $countpersent = $poolFunction->getpersentcount($yesterday);
        $this->view->countpersent = $countpersent;
        $countabsent = $poolFunction->getabsentcount($yesterday);
        $this->view->countabsent = $countabsent;
        $countwikend = $poolFunction->getwikendcount();
        $countwikendHourly = $poolFunction->getcountwikendHourly();

        $this->view->countwikend = $countwikend;
        $this->view->countwikendHourly = $countwikendHourly;
    }

    public function getonlinetraficAction() {
        $this->_helper->layout->disableLayout();
        $Function = new Application_Model_Func_Function();
        $ReportModel = new Application_Model_Func_Report();
        $post = $this->getRequest()->getParams();
        $table = "(select * from TBL_IDM_INOUT i  JOIN TBL_IDM_MANAGER k ON i.scode=k.codecart JOIN TBL_KHC_STU_INFO_BASE m ON 
		k.studentid =m.code where  i.mydate= FORMAT(GETDATE(),'yyyy/MM/dd', 'fa')  )tbl";
        $primaryKey = "scode";
        $columns = array(
            array('db' => 'NAME_FAMILY', 'dt' => 0),
            array('db' => 'CODE', 'dt' => 1),
            array('db' => 'MYDATE', 'dt' => 2),
            array('db' => 'MYTIME', 'dt' => 3),
            array('db' => 'INOUT_TYPE',
                'dt' => 4,
                'formatter' => function ($d, $row) {
                    if ($d == 2) {
                        $checked = "checked='checked'";
                        if ($row['SENDSMS'] == '1') {
                            $stsussmstext = '<div  style="background: green;text-align: center;margin: 5px;" class="label label-info">پیام برای سرپرست ارسال شد</div>';
                        }
                    }

                    return $stsussmstext.'<input ' . $checked . ' value="' . $d . '" type="checkbox" onchange="regData(\'' . $row['CODE'] . '\',\'' . $row['SCODE'] . '\',$(this).is(\':checked\'),\'' . $row['MYDATE'] . '\',\'' . $row['MYTIME'] . '\',2)"/>';
                }),
            array('db' => 'INOUT_TYPE',
                'dt' => 5,
                'formatter' => function ($d, $row) {
                    if ($d == 3) {
                        $checked = "checked='checked'";
                    }
                    return '<input ' . $checked . ' value="' . $d . '" type="checkbox" onchange="regData(\'' . $row['CODE'] . '\',\'' . $row['SCODE'] . '\',$(this).is(\':checked\'),\'' . $row['MYDATE'] . '\',\'' . $row['MYTIME'] . '\',3)"/>';
                }),
            array('db' => 'SCODE', 'dt' => 6),
            array('db' => 'INOUT_TYPE', 'dt' => 7),
            array('db' => 'AUTOID', 'dt' => 8),
            array('db' => 'SENDSMS', 'dt' => 9),
        );
        $db = Zend_Registry::get("db");
        try {
            $result = Application_Model_Func_SSP::simple($post, $db, $table, $primaryKey, $columns);

            foreach ($result['data'] as $row) {
                if ($row['9'] == 0 && $row['7'] == 2) {
                    $smsStatus = $ReportModel->sendSmsWikendAction($row['6'], $row['2'], $row['3'], $row['8']);
                }
            }
            echo json_encode($result);
        } catch (Exception $e) {
            var_export($e->getMessage());
        }
        exit;
    }

    public function editkindtraficAction() {
        $this->_helper->layout->disableLayout();
        $Function = new Application_Model_Func_Function();

        try {
            $post = $this->getRequest()->getParams();
            $resultinout = $Function->saveinfoinout($post);
            if (!$resultinout) {
                echo '<div  style="background: #f7acbe;text-align: center;margin-top: 5px;" class="label label-danger"> خطا در انجام عملیات</div>';
            } else {
                echo '<div  style="background: green;text-align: center;margin-top: 5px;" class="label label-info">عملیات با موفقیت انجام شد</div>';
            }
        } catch (Exception $e) {
            var_export($e->getMessage());
        }
        exit;
    }

    public function addrowAction() {
        $this->_helper->layout->disableLayout();
        $Function = new Application_Model_Func_Function();

        try {
            $students = $Function->fetchAllCode();
            $this->view->students = $students;
        } catch (Exception $e) {
            var_export($e->getMessage());
        }
    }

    public function addrowsaveAction() {
        $this->_helper->layout->disableLayout();
        $Function = new Application_Model_Func_Function();
        $post = $this->getRequest()->getPost();
        if (empty($post['codecart'])) {
            echo '<div  style="background: #f7acbe;text-align: center;margin-top: 5px;font-size:15px" class="label label-danger">شماره دانشجویی را با دقت وارد کنید. کد شناسایی معتبر نیست</div>';
            exit;
        }
        if (empty($post['MYTIME'])) {
            echo '<div  style="background: #f7acbe;text-align: center;margin-top: 5px;font-size:15px" class="label label-danger">زمان تردد معتبر نیست</div>';
            exit;
        }

        $result = $Function->savetaradod($post);
        if (!$result) {
            echo '<div  style="background: #f7acbe;text-align: center;margin-top: 5px;" class="label label-danger"> خطا در انجام عملیات</div>';
        } else {
            echo '<div  style="background: green;text-align: center;margin-top: 5px;" class="label label-info">عملیات با موفقیت انجام شد</div>';
        }
        exit;
    }

    public function syncAction() {
        $this->_helper->layout->disableLayout();

        exit;
        $conn = oci_connect($db_username, $db_password, $dbinfo);
        if (!$conn) {
            $e = oci_error();
            trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        }
        $stm = oci_parse($conn, "SELECT  count(*)  FROM sleep_absent_new  ");
        oci_execute($stm);
        $row = oci_fetch_all($stm, $result);
        var_export($row);
        exit;
        ini_set('max_execution_time', 0); // to get unlimited php script execution time
        $total = 100;
        for ($i = 0;
                $i < $total;
                $i++) {

            $percent = intval($i / $total * 100) . "%";

            sleep(1); // Here call your time taking function like sending bulk sms etc.

            echo '<script>   parent.document.getElementById("progressbar").innerHTML="<div style=\"width:' . $percent . ';background:linear-gradient(to bottom, rgba(125,126,125,1) 0%,rgba(14,14,14,1) 100%); ;height:35px;\">&nbsp;</div>";
    parent.document.getElementById("information").innerHTML="<div style=\"text-align:center; font-weight:bold\">' . $percent . ' در حال انجام.</div>";</script>';

            ob_flush();
            flush();
        }
        echo '<script>parent.document.getElementById("information").innerHTML = "<div style=\"text-align:center; font-weight:bold\">همگامz</div>"</script>';

        exit;
    }

}
