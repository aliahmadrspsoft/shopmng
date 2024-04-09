<?php

class ReportController extends Zend_Controller_Action {

    public $countunmngabsent = 0;

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

            if ($result['result']) {
                if ($authNamespace->unmngabsent) {
                    $action = $this->getRequest()->getActionName();
                    $controller = $this->getRequest()->getControllerName();
                    if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == '') :
                        if ($controller != "report") {
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

    public function persentAction() {
        $mydate = Application_Model_Func_SSP::convert_($this->getRequest()->getParam('getdate'));
        $this->view->myshowdate = $this->getRequest()->getParam('getdate');
        $this->view->mydate = $mydate;
    }

    public function absentAction() {
        $authNamespace = Zend_Registry::get("authNamespace");
        $mydate = Application_Model_Func_SSP::convert_($this->getRequest()->getParam('getdate'));
        $alert = $this->getRequest()->getParam('alert');
        $countunmngabsent = 0;
        if (!$authNamespace->isAdmin) {
            $poolFunction = new Application_Model_Func_Function();
            $yesterday = $poolFunction->yesterday();
            $countunmngabsent = $poolFunction->get_un_mng_absentcount($yesterday);
        }
        if ($countunmngabsent == 0) {
            $alert = null;
            $authNamespace->unmngabsent = false;
        } else {
            $alert = "yes";
            //  $authNamespace->unmngabsent = true;
        }
        $this->view->alert = $alert;
        $this->view->mydate = $mydate;
        $this->view->myshowdate = $this->getRequest()->getParam('getdate');
    }

    public function gardAction() {
        
    }

    public function wikendAction() {
        $mydate = Application_Model_Func_SSP::convert_($this->getRequest()->getParam('getdate'));
        $this->view->myshowdate = $this->getRequest()->getParam('getdate');
        $this->view->mydate = $mydate;
    }

    public function wikendhourlyAction() {
        
    }

    public function getwikendhourlylistAction() {
        $this->_helper->layout->disableLayout();
        $Function = new Application_Model_Func_Function();
        $post = $this->getRequest()->getParams();
        if (empty($post['date'])) {
            echo '<div  style="background: #f7acbe;text-align: center;margin-top: 5px;" class="label label-danger"> تاریخ  گزارش  را وارد کنید</div>';
            exit;
        }
        $table = '(select * from (select main.* from TBL_IDM_INOUT main,
                                    (select scode as tscode,max(MYTIME)as tmytime,MYDATE as tmydate from TBL_IDM_INOUT  
                                    group by scode,mydate)tbltemp where main.SCODE=tbltemp.tscode and 
                                    tbltemp.tmydate=main.MYDATE and tbltemp.tmytime=main.MYTIME) i JOIN TBL_IDM_MANAGER k ON k.codecart=i.scode  JOIN TBL_KHC_STU_INFO_BASE m ON 
		k.studentid =m.code where  i.mydate=\'' . Application_Model_Func_SSP::convert(trim($post['date'])) . '\' and k.indorm=1 and (i.inout_type=\'3\'))p  ';
        $primaryKey = "scode";
        $columns = array(
            array('db' => 'NAME_FAMILY', 'dt' => 0),
            array('db' => 'CODE', 'dt' => 1),
            array('db' => 'MYDATE', 'dt' => 2),
            array('db' => 'MYTIME', 'dt' => 3)
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

    public function getpersenrlistAction() {
        $this->_helper->layout->disableLayout();
        $Function = new Application_Model_Func_Function();
        $post = $this->getRequest()->getParams();
        if (empty($post['date'])) {
            echo '<div  style="background: #f7acbe;text-align: center;margin-top: 5px;" class="label label-danger"> تاریخ  گزارش  را وارد کنید</div>';
            exit;
        }
        $table = '(select * from (select main.* from TBL_IDM_INOUT main,
                                    (select scode as tscode,max(MYTIME)as tmytime,MYDATE as tmydate from TBL_IDM_INOUT  
                                    group by scode,mydate)tbltemp where main.SCODE=tbltemp.tscode and 
                                    tbltemp.tmydate=main.MYDATE and tbltemp.tmytime=main.MYTIME) i JOIN TBL_IDM_MANAGER k ON k.codecart=i.scode  JOIN TBL_KHC_STU_INFO_BASE m ON 
		k.studentid =m.code where  i.mydate=\'' . Application_Model_Func_SSP::convert(trim($post['date'])) . '\' and k.indorm=1 and ( i.inout_type=\'1\'))p  ';
        $primaryKey = "scode";
        $columns = array(
            array('db' => 'NAME_FAMILY', 'dt' => 0),
            array('db' => 'CODE', 'dt' => 1),
            array('db' => 'MYDATE', 'dt' => 2),
            array('db' => 'MYTIME', 'dt' => 3)
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

    public function getgardlistAction() {
        $this->_helper->layout->disableLayout();
        $Function = new Application_Model_Func_Function();
        $post = $this->getRequest()->getParams();
        if (empty($post['datefrom']) || empty($post['dateto'])) {
            echo '<div  style="background: #f7acbe;text-align: center;margin-top: 5px;" class="label label-danger"> تاریخ  گزارش  را وارد کنید</div>';
            exit;
        }
        if (empty($post['countabsent'])) {
            echo '<div  style="background: #f7acbe;text-align: center;margin-top: 5px;" class="label label-danger"> تعداد غیبت را وارد کنید</div>';
            exit;
        }
        $table = '(select  b.CODE,b.NAME_FAMILY,g.countabsent,g.DATES,m.MOBIL,m.NOTIFICMOBILE '
                . 'from TBL_KHC_STU_INFO_BASE b,(select   code,count(code) as countabsent, '
                . ' STRING_AGG([date],\' - \')as DATES from TBL_IDM_STATESTU where [date] between \'' . Application_Model_Func_SSP::convert(trim($post['datefrom'])) . '\' and \'' . Application_Model_Func_SSP::convert(trim($post['dateto'])) . '\' 
  group by code having count(code)>=\'' . $post['countabsent'] . '\')g,TBL_IDM_MANAGER m where g.code=b.CODE 
  and b.CODE=m.STUDENTID)p  ';
        $primaryKey = "CODE";
        $columns = array(
            array('db' => 'NAME_FAMILY', 'dt' => 0),
            array('db' => 'CODE', 'dt' => 1),
            array('db' => 'DATES', 'dt' => 2),
            array('db' => 'countabsent', 'dt' => 3),
            array('db' => 'MOBIL', 'dt' => 4),
            array('db' => 'NOTIFICMOBILE', 'dt' => 5)
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

    public function getabsentlistAction() {
        $this->_helper->layout->disableLayout();
        $Function = new Application_Model_Func_Function();
        $post = $this->getRequest()->getParams();
        if (empty($post['date'])) {
            echo '<div  style="background: #f7acbe;text-align: center;margin-top: 5px;" class="label label-danger"> تاریخ  گزارش  را وارد کنید</div>';
            exit;
        }
        $date = Application_Model_Func_SSP::convert(trim($post['date']));

        $table = '(SELECT  i.*,k.*,m.*,s.code as CODESTATUS,\'' . $date . '\' as mirdate ,SMS.INOUTTYPE FROM 
(select * from TBL_IDM_INOUT where mydate=\'' . $date . '\' and inout_type in(\'1\') ) AS i INNER JOIN (select scode as tscode,max(MYTIME)as tmytime,MYDATE as tmydate from TBL_IDM_INOUT group by scode,mydate) AS ttp ON i.SCODE=ttp.tscode and ttp.tmydate=i.MYDATE and ttp.tmytime=i.MYTIME

 RIGHT JOIN (select k.* from tbl_idm_inout ii join (select scode,max(AUTOID)maxid from TBL_IDM_INOUT where mydate<=\'' . $date . '\' group by scode) tt
  on  ii.SCODE=tt.SCODE and ii.AUTOID=tt.maxid right join 
TBL_IDM_MANAGER k on k.CODECART=ii.SCODE where k.INDORM=1 and (ii.INOUT_TYPE is null or ii.INOUT_TYPE<>2)) AS k ON k.codecart=i.scode LEFT JOIN
 (select * from TBL_IDM_STATESTU where TBL_IDM_STATESTU.DATE=\'' . $date . '\' ) AS s ON k.studentid=s.code  LEFT JOIN
 (select * from TBL_IDM_SENDSMS where TBL_IDM_SENDSMS.DATE=\'' . $date . '\' ) AS SMS ON k.studentid=SMS.code  INNER JOIN 
 TBL_KHC_STU_INFO_BASE AS m ON k.studentid =m.code WHERE (k.indorm=1) AND (i.scode is null))tbl';
        $primaryKey = "CODE";
        $columns = array(
            array('db' => 'CODESTATUS',
                'dt' => 0,
                'formatter' => function ($d, $row) {
                    return '<input  value="' . $row['mirdate'] . '" class="checkBoxClass" name="choice[' . $row['CODE'] . ']" type="checkbox"/>';
                }),
            array('db' => 'NAME_FAMILY', 'dt' => 1),
            array('db' => 'CODE', 'dt' => 2),
            array('db' => 'mirdate', 'dt' => 3),
            array('db' => 'MOBIL', 'dt' => 4),
            array('db' => 'NOTIFICMOBILE', 'dt' => 5),
            array('db' => 'CODECART', 'dt' => 9),
            array('db' => 'INOUTTYPE', 'dt' => 8,
                'formatter' => function ($d, $row) {

                    if ($d == '0')
                        $addtextSMS = "<span style='color:darkgreen'> ارسال شده</span>";
                    else {
                        $addtextSMS = "<span  style='color:darkred'> ارسال نشده</span>";
                    }
                    return $addtextSMS;
                }),
            array('db' => 'CODESTATUS',
                'dt' => 7,
                'formatter' => function ($d, $row) {
                    if (isset($d))
                        $addtext = "<span style='color:darkgreen'>درج شده</span>";
                    else {
                        $addtext = "<span  style='color:darkred'>درج نشده</span>";
                    }
                    if ($row['INOUTTYPE'] == '0')
                        $addtextSMS = "<span style='color:darkgreen'>پیامک ارسال شده</span>";
                    else {
                        $addtextSMS = "<span  style='color:darkred'>پیامک ارسال نشده</span>";
                    }
                    return $addtext;
                }),
            array('db' => '1',
                'dt' => 6,
                'formatter' => function ($d, $row) {

                    return '<label for="INOUT_TYPE" class="col-sm-2 control-label">نوع تردد </label>'
                    . '<div class="col-lg-4"><select name="INOUT_TYPE" class="form-control" id="INOUT_TYPE' . $row['CODE'] . '"  >'
                    . '  <option value="1">حضور</option>'
                    . '  <option value="2">مرخصی روزانه</option>'
                    . '  </select >
                              
                    </div>'
                    . ' <div class="col-lg-6"><input type="time" class="form-control" id="MYTIME' . $row['CODE'] . '" />'
                    . ' <a onclick=" regDataTaradod(\'' . $row['CODE'] . '\',\'' . $row['CODECART'] . '\',$(\'#MYTIME' . $row['CODE'] . '\').val(), \'' . $row['mirdate'] . '\', $(\'#INOUT_TYPE' . $row['CODE'] . '\').val(),\'absent report\')" class="btn btn-social-icon btn-dropbox">درج'
                    . '<i onclick=""   class="fa fa-fw fa-edit"></i>
                                    </a></div>';
                }),
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

    public function getwikendtlistAction() {
        $this->_helper->layout->disableLayout();
        $Function = new Application_Model_Func_Function();
        $post = $this->getRequest()->getParams();
        if (empty($post['date'])) {
            echo '<div  style="background: #f7acbe;text-align: center;margin-top: 5px;" class="label label-danger"> تاریخ  گزارش  را وارد کنید</div>';
            exit;
        }
        $SSP = new Application_Model_Func_SSP();
        $table = '(SELECT m.*,concat(ii.MYTIME,\'  - \',ii.MYDATE) as DATETIME_OUT,ii.SENDSMS FROM   tbl_idm_inout ii join (select scode,max(AUTOID)maxid from TBL_IDM_INOUT where mydate<=\'' . Application_Model_Func_SSP::convert(trim($post['date'])) . '\' group by scode) tt
  on  ii.SCODE=tt.SCODE and ii.AUTOID=tt.maxid  join 
TBL_IDM_MANAGER k on k.CODECART=ii.SCODE join TBL_KHC_STU_INFO_BASE m on k.STUDENTID=m.CODE where k.INDORM=1 and ii.INOUT_TYPE=2)tbl';
        $primaryKey = "CODE";
        $columns = array(
            array('db' => 'NAME_FAMILY', 'dt' => 0),
            array('db' => 'CODE', 'dt' => 1),
            array('db' => 'DATETIME_OUT', 'dt' => 2),
            array('db' => 'SENDSMS', 'dt' => 3, 'formatter' => function ($d, $row) {
                    if ($d == 1) {
                        $stsussmstext = '<div  style="background: green;text-align: center;margin: 5px;" class="label label-info">پیام برای سرپرست ارسال شد</div>';
                    } else {
                        $stsussmstext = '<div  style="background: red;text-align: center;margin: 5px;" class="label label-danger">پیام برای سرپرست ارسال  نشد</div>';
                    }

                    return $stsussmstext;
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

    public function insertabsentlistAction() {
        ini_set('max_execution_time', 0);
        $this->_helper->layout->disableLayout();
        $funcReport = new Application_Model_Func_Report();
        $post = $this->getRequest()->getPost();
        $authNamespace = Zend_Registry::get("authNamespace");
        if (empty($authNamespace->ind)) {
            $authNamespace->ind = 0;
        }
        $ind = 0;
        $set_total = count($post['choice']);
        echo '<script  language="javascript" >
            parent.document.getElementById("noticDiv").innerHTML="";
            </script>';

        foreach ($post['choice'] as $code => $mydate) {
            $ind++;
            $percent = intval(($ind * 100) / $set_total) . "%";

            $txtres = $funcReport->saveStudentState($code, Application_Model_Func_SSP::convert($mydate), 4);

            echo '<script  language="javascript" >
    parent.document.getElementById("progressbar").innerHTML="<div style=\"width:' . $percent . ';background:linear-gradient(to bottom, rgba(126,126,126,1) 0%,rgba(15,15,15,1) 100%); ;height:36px;\">&nbsp;</div>";
    parent.document.getElementById("informations").innerHTML ="<div style=\"text-align:center; font-weight:bold\"> عملیات انجام شد' . $percent . '</div>";'
            . ' parent.document.getElementById("noticDiv").innerHTML +="<br/>' . $txtres . '";parent.document.getElementById("noticDiv").style.height="200px";'
            . '</script>';

            // ob_flush and flush process
            ob_flush();
            flush();
            sleep(1);
        }
        $textend = "عملیات به اتمام رسید";
        if ($set_total == 0) {

            $textend = "هیچ دانشجویی انتخاب نکردید";
        }

        echo '<script language="javascript"> parent.document.getElementById("informations").innerHTML="<div style=\"text-align:center; font-weight:bold\">' . $textend . '</div>";'
        . '</script>';
        $authNamespace->ind = 0;
        exit;
    }

    public function sendsmsabsentlistAction() {
        ini_set('max_execution_time', 0);
        $this->_helper->layout->disableLayout();
        $funcReport = new Application_Model_Func_Report();
        $post = $this->getRequest()->getPost();
        //  var_export($post);exit;
        $authNamespace = Zend_Registry::get("authNamespace");
        if (empty($authNamespace->ind)) {
            $authNamespace->ind = 0;
        }
        $ind = 0;
        $set_total = count($post['choice']);
        echo '<script  language="javascript" >
            parent.document.getElementById("noticDiv").innerHTML="";
            </script>';

        foreach ($post['choice'] as $code => $mydate) {
            $ind++;
            $percent = intval(($ind * 100) / $set_total) . "%";

            $txtres = $funcReport->sendSmsAction($code, $mydate);

            echo '<script  language="javascript" >
    parent.document.getElementById("progressbar").innerHTML="<div style=\"width:' . $percent . ';background:linear-gradient(to bottom, rgba(126,126,126,1) 0%,rgba(15,15,15,1) 100%); ;height:36px;\">&nbsp;</div>";
    parent.document.getElementById("informations").innerHTML ="<div style=\"text-align:center; font-weight:bold\"> عملیات انجام شد' . $percent . '</div>";'
            . ' parent.document.getElementById("noticDiv").innerHTML +="<br/>' . $txtres . '";parent.document.getElementById("noticDiv").style.height="200px";'
            . '</script>';

            // ob_flush and flush process
            ob_flush();
            flush();
            sleep(1);
        }
        $textend = "عملیات به اتمام رسید";
        if ($set_total == 0) {

            $textend = "هیچ دانشجویی انتخاب نکردید";
        }

        echo '<script language="javascript"> parent.document.getElementById("informations").innerHTML="<div style=\"text-align:center; font-weight:bold\">' . $textend . '</div>"</script>';
        $authNamespace->ind = 0;
        exit;
    }

}
