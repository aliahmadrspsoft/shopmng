<?php

class Application_Model_Func_Report {

    public function fetchmobilebycodecart($codecart) {
        $db = Zend_Registry::get("db");
        $select = $db->select()
                ->from(array('m' => ' TBL_KHC_STU_INFO_BASE'),
                        array('NAME_FAMILY', 'CODE'))
                ->joinLeft(array('k' => 'TBL_IDM_MANAGER'),
                        'm.code=k.studentid',
                        array('CODECART', 'INDORM', 'WIKEND', 'MOBIL', 'NOTIFICMOBILE'))
                ->where('k.CODECART=?', array($codecart));

        $stmt = $db->query($select);
        $result = $stmt->fetchAll();
        return $result;
    }

    public function fetchmobilebycode($code) {
        $db = Zend_Registry::get("db");
        $select = $db->select()
                ->from(array('m' => ' TBL_KHC_STU_INFO_BASE'),
                        array('NAME_FAMILY', 'CODE'))
                ->joinLeft(array('k' => 'TBL_IDM_MANAGER'),
                        'm.code=k.studentid',
                        array('CODECART', 'INDORM', 'WIKEND', 'MOBIL', 'NOTIFICMOBILE'))
                ->where('m.code=?', array($code));

        $stmt = $db->query($select);
        $result = $stmt->fetchAll();
        return $result;
    }

    public function fetchbycode($code, $date, $state) {
        $db = Zend_Registry::get("db");
        $select = $db->select()
                ->from(array('m' => 'TBL_IDM_STATESTU'),
                        array('CODE', 'DATE', 'STATE'))
                ->where('m.code=?', array($code))
                ->where('m.date=?', array($date))
                ->where('m.state=?', array($state));
        $stmt = $db->query($select);
        $result = $stmt->fetchAll();
        return $result;
    }

    public function sendsms($mobile, $code, $text, $url) {

        $mobile = '09137264562';
        require_once('nusoap.php');
        $client = new nusoap_client($url, 'wsdl');
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = true;
        $param = array('requestData' =>
            '<xmsrequest>
		<userid>63994</userid>
		<password>Ukh12369**</password>
		<action>smssend</action>
        <body>
          <type>oto</type>
          <recipient mobile="' . $mobile . '"  originator="90001941"  doerid="8" >' . $text . '</recipient>
        </body>
	</xmsrequest>');

        $result = $client->call('XmsRequest', $param);

        $ERRTXT = $result["XmsRequestResult"];

        if ($client->fault) {

            return array('issend' => 0, 'errortxt' => $ERRTXT);
        } else {
            $err = $client->getError();
            if ($err) {
                return array('issend' => 0, 'errortxt' => $err);
            } else {
                return array('issend' => 1, 'errortxt' => 'بدون خطا');
            }
        }
    }

    public function sendSmsWikendAction($codecart, $date, $time, $autoid) {
        $db = Zend_Registry::get("db");
        date_default_timezone_set('Asia/Tehran');
        $today = date("Y-m-d H:i:s"); // 
        $resultsms = Application_Model_Func_Device::fetchsetting();
        if ($resultsms[0]['WIKENDSMSACTIVE'] == 1) {
            try {
                $result = $this->fetchmobilebycodecart($codecart);
                if ($result[0]['NOTIFICMOBILE'] == "") {
                    return;
                } else {
                    $text = "دانشجو " . $result[0]['NAME_FAMILY'] . " در تاریخ " . $date . " ساعت " . $time . " از خوابگاه جواهری دانشکده خوانسار جهت حضور در منزل تردد مرخصی ثبت کرد";
                    $res = $this->sendsms($result[0]['NOTIFICMOBILE'], $codecart, $text, $resultsms[0]['SMSURL']);

                    $db->insert('[TBL_IDM_SENDSMS]',
                            array(
                                'CODE' => $result[0]['CODE'],
                                'DATESEND' => $today,
                                'DATE' => $date,
                                'INOUTTYPE' => 2,
                                'text' => $text,
                                'mobile' => $result[0]['NOTIFICMOBILE'],
                                'issend' => $res['issend'],
                                'err' => $res['errortxt'],
                    ));
                    $whereautoid = $db->quoteInto('AUTOID=?', $autoid);

                    $db->update('[TBL_IDM_INOUT]',
                            array(
                                'SENDSMS' => $res['issend'],
                            ), $whereautoid);
                }
            } catch (Exception $e) {
                
            }
        }
    }

    public function sendSmsAction($code, $date) {
        $db = Zend_Registry::get("db");
        date_default_timezone_set('Asia/Tehran');
        $today = date("Y-m-d H:i:s"); // 
        $reultaction="";
        $resultsms = Application_Model_Func_Device::fetchsetting();
        if ($resultsms[0]['ABSENTSMSACTIVE'] == 1) {

            try {
                $result = $this->fetchmobilebycode($code);

                if ($result[0]['NOTIFICMOBILE'] == "") {
                    $reultaction .='<div style=\"text-align:center;background:yellow; font-weight:bold\">  دانشجو' . $code . '  تلفن سرپرست ندارد لطفا برای آن تنظیم کنید</div>';
                } else {

                    $text = "دانشجو " . $result[0]['NAME_FAMILY'] . " در تاریخ " . $date . " در خوابگاه جواهری دانشکده خوانسار حضور ندارد";

                    $res = $this->sendsms($result[0]['NOTIFICMOBILE'], $code, $text, $resultsms[0]['SMSURL']);
                    if ($res['issend'] == 1) {
                        $reultaction .= '<div style=\"text-align:center;background:green; font-weight:bold\">  دانشجو' . $code . ' ارسال پیامک با موفقیت انجام شد</div>';
                    } else {
                        $reultaction .= '<div style=\"text-align:center;background:red; font-weight:bold\">  دانشجو' . $code . ' ارسال پیامک با موفقیت انجام نشد</div>';
                    }
                    $db->insert('[TBL_IDM_SENDSMS]',
                            array(
                                'CODE' => $code,
                                'DATESEND' => $today,
                                'DATE' => $date,
                                'INOUTTYPE' => 0,
                                'text' => $text,
                                'mobile' => $result[0]['NOTIFICMOBILE'],
                                'issend' => $res['issend'],
                                'err' => $res['errortxt'],
                    ));
                }
            } catch (Exception $e) {
                $reultaction .= '<div style=\"text-align:center;background:red; font-weight:bold\">  دانشجو' . $code . ' ارسال پیامک با موفقیت انجام نشد</div>';
            }
        } else {
            $reultaction .= '<div style=\"text-align:center;background:red; font-weight:bold\"> امکان ارسال پیامک توسط مدیر سیستم غیر فعال شده است</div>';
        }
        return $reultaction;
    }

    public function saveStudentState($code, $date, $state) {
        $db = Zend_Registry::get("db");
        date_default_timezone_set('Asia/Tehran');
        $today = date("Y-m-d"); // 

        try {
            $result = $this->fetchbycode($code, $date, $state);
            if (count($result) > 0) {
                return'<div style=\"text-align:center;background:yellow; font-weight:bold\">  دانشجو' . $code . '  قبلا در سیستم درج در پرونده شده است</div>';
            } else {
                $db->insert('TBL_IDM_STATESTU',
                        array(
                            'CODE' => $code,
                            'DATE' => $date,
                            'STATE' => $state,
                            'DATE_LOG' => $today,
                ));
                return'<div style=\"text-align:center;background:green; font-weight:bold\">  دانشجو' . $code . ' غیبت درج در پرونده  شد</div>';
            }
        } catch (Exception $e) {

            return'<div style=\"text-align:center;background:red; font-weight:bold\">  دانشجو' . $code . ' غیبت درج در پرونده نشد</div>' . $e->getMessage();
        }
    }

}
