<?php

class Application_Model_Func_Function {

    function yesterday() {
        $db = Zend_Registry::get("db");
        $select = "select FORMAT( DATEADD(DAY,-1,GETDATE()), 'yyyy/MM/dd', 'fa') as yesterday";
        $stmt = $db->query($select);
        $result = $stmt->fetchAll();
        return $result[0]['yesterday'];
    }

    function todayinsh() {
        $db = Zend_Registry::get("db");
        $select = "select  FORMAT(GETDATE(), 'yyyy/MM/dd', 'fa') as today";
        $stmt = $db->query($select);
        $result = $stmt->fetchAll();
        return $result[0]['today'];
    }

    public function getTraffic() {

        $db = Zend_Registry::get("db");
        $select = $db->select()
                ->from(array('i' => 'TBL_IDM_INOUT'),
                        array('SCODE', 'MYDATE', 'MYTIME', 'MYCOMMENT'))
                ->join(array('k' => 'TBL_IDM_MANAGER'), 'k.codecart=i.scode',
                        array('STUDENTID', 'CODECART'))
                ->join(array('m' => 'TBL_KHC_STU_INFO_BASE'),
                        'k.studentid =m.code',
                        array('NAME_FAMILY', 'CODE'))
                ->where("i.mydate=FORMAT(GETDATE(), 'yyyy/MM/dd', 'fa')")
        ;
        //var_export($select->__toString());exit;
        $stmt = $db->query($select);
        $result = $stmt->fetchAll();
        return $result;
    }

    public function saveinfoinout($post) {

        $db = Zend_Registry::get("db");
        $db->beginTransaction();
        try {
            $arrayupdate = array();
            if (isset($post['wikend'])) {
                $arrayupdate['INOUT_TYPE'] = ($post['wikend'] == 'true' || $post['wikend'] == 1) ? $post['kindtrafic'] : 1;
                $arrayupdate2['wikend'] = ($post['wikend'] == 'true' || $post['wikend'] == 1) && ($post['kindtrafic'] == 2) ? 1 : 0;
                $wherecode = $db->quoteInto('SCODE=?', array(Application_Model_Func_SSP::convert($post['scode'])));
                $wheredate = $db->quoteInto('MYDATE=?', array(Application_Model_Func_SSP::convert($post['date'])));
                $wheretime = $db->quoteInto('MYTIME=?', array(Application_Model_Func_SSP::convert($post['time'])));

                $db->update(
                        'TBL_IDM_INOUT',
                        $arrayupdate,
                        $wherecode . ' and ' . $wheredate . ' and ' . $wheretime
                );
                if (ltrim(rtrim($post['kindtrafic'] == "2"))) {
                    $where2 = $db->quoteInto('studentid=?', array($post['code']));
                    $db->update(
                            'TBL_IDM_MANAGER',
                            $arrayupdate2,
                            $where2
                    );
                }

                $db->commit();
                return true;
            }
        } catch (Exception $e) {
            echo $e->getMessage();
            $db->rollBack();
            return false;
        }
        return false;
    }

    public function removewekend($wikend, $studentid) {

        $db = Zend_Registry::get("db");
        $db->beginTransaction();
        try {
            $where2 = $db->quoteInto('studentid=?', $studentid);
            $db->update(
                    'TBL_IDM_MANAGER',
                    array('wikend' => $wikend),
                    $where2
            );
            $db->commit();
            return true;
        } catch (Exception $e) {
            echo $e->getMessage();
            $db->rollBack();
            exit;
            return false;
        }
        return false;
    }

    public function savetaradod($post) {
        if (!isset($post['MYDATE'])) {
            $post['MYDATE'] = $this->todayinsh();
        }
        $result = $this->fetchtaradodcode($post['codecart'], $post['MYTIME'], $post['MYDATE']);

        if (count($result) > 0) {
            echo '<div  style="background: #f7acbe;text-align: center;margin-top: 5px;font-size:15px" class="label label-danger">برای این دانشجو این تردد قبلا درج شده است</div>';
            exit;
        }
        $db = Zend_Registry::get("db");
        try {
            $db->insert('TBL_IDM_INOUT', array("SCODE" => $post['codecart']
                , "MYDATE" => $post['MYDATE']
                , "MYTIME" => $post['MYTIME']
                , "GATE_NO" => -1
                , "INOUT_TYPE" => $post['INOUT_TYPE']
                , "COMMENT" => $post['COMMENT']));
            return true;
        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }
        return false;
    }

    public function saveinfo($post) {
        $result = $this->fetchbycodecartandothercode($post['code'], $post['codecart']);
        if (count($result) > 0)
            return null;

        $db = Zend_Registry::get("db");

        try {
            $result = $this->fetchbycodemanger($post['code']);
            $arrayupdate = array();

            $arrayupdate['wikend'] = ($post['wikend'] == 'true' || $post['wikend'] == 1) ? 1 : 0;

            if (isset($post['codecart'])) {
                $arrayupdate['codecart'] = $post['codecart'];
            }
            if (isset($post['codecart'])) {
                $arrayupdate['codecart'] = $post['codecart'];
            }
            if (isset($post['MOBIL'])) {
                $arrayupdate['MOBIL'] = $post['MOBIL'];
            }
            if (isset($post['NOTIFICMOBILE'])) {
                $arrayupdate['NOTIFICMOBILE'] = $post['NOTIFICMOBILE'];
            }

            $arrayupdate['indorm'] = ($post['indorm'] == 'true' || $post['indorm'] == 1) ? 1 : 0;

            if (count($result) > 0) {
                $db->update(
                        'TBL_IDM_MANAGER',
                        $arrayupdate,
                        'studentid=' . $post['code']
                );
            } else {
                if (isset($post['code'])) {
                    $arrayupdate['studentid'] = $post['code'];
                }
                $db->insert('TBL_IDM_MANAGER', $arrayupdate);
            }
            return true;
        } catch (Exception $e) {
            echo $e->getMessage();
            return null;
        }
        return $result;
    }

    public function fetchbycode($code) {
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

    public function fetchAllCode() {
        $db = Zend_Registry::get("db");
        $select = $db->select()
                ->from(array('m' => ' TBL_KHC_STU_INFO_BASE'),
                        array('name' => 'concat(NAME_FAMILY,CODE)'))
                ->joinLeft(array('k' => 'TBL_IDM_MANAGER'),
                        'm.code=k.studentid',
                        array('id' => 'CODECART'))
                ->where('k.CODECART>0');

        $stmt = $db->query($select);
        $result = $stmt->fetchAll();
        return $result;
    }

    public function fetchbycodecartandothercode($code, $codecart) {
        $db = Zend_Registry::get("db");
        $select = $db->select()
                ->from(array('k' => 'TBL_IDM_MANAGER'),
                        array('studentid', 'codecart'))
                ->where('k.codecart=?', array("codecart" => $codecart))
                ->where('k.studentid<>?', array("studentid" => $code));

        $stmt = $db->query($select);
        $result = $stmt->fetchAll();
        return $result;
    }

    public function fetchtaradodcode($codecart, $time, $date) {
        //    echo "select  * from TBL_IDM_INOUT i where i.SCODE='' and i.MYDATE='' and";
        $db = Zend_Registry::get("db");
        $select = $db->select()
                ->from(array('i' => 'TBL_IDM_INOUT'),
                        array('*'))
                ->where('i.SCODE=?', array($codecart))
                ->where('i.MYDATE=?', array($date))
                ->where('i.MYTIME=?', array($time));

        $stmt = $db->query($select);
        $result = $stmt->fetchAll();
        return $result;
    }

    public function fetchbycodemanger($code) {
        $db = Zend_Registry::get("db");
        $select = $db->select()
                ->from(array('k' => 'TBL_IDM_MANAGER'),
                        array('STUDENTID', 'CODECART'))
                ->where('k.studentid=?', array("studentid" => $code));

        $stmt = $db->query($select);
        $result = $stmt->fetchAll();
        return $result;
    }

    public function fetchbycodecart($codecart) {
        $db = Zend_Registry::get("db");
        $select = $db->select()
                ->from(array('k' => 'TBL_IDM_MANAGER'),
                        array('STUDENTID', 'CODECART'))
                ->where('k.codecart=?', array($codecart));

        $stmt = $db->query($select);
        $result = $stmt->fetchAll();
        return $result;
    }

    public function getpersentcount($date = null) {

        if ($date == null) {
            $date = $this->todayinsh();
        }
        $db = Zend_Registry::get("db");
        $select = $db->select()->distinct()
                ->from(array('i' => 'TBL_IDM_INOUT'), array())
                ->join(array('ttp' => '(select scode as tscode,max(MYTIME)as tmytime,MYDATE as tmydate from TBL_IDM_INOUT  
group by scode,mydate)'), 'i.SCODE=ttp.tscode and 
ttp.tmydate=i.MYDATE and ttp.tmytime=i.MYTIME',
                        array())
                ->join(array('k' => 'TBL_IDM_MANAGER'), 'k.codecart=i.scode',
                        array())
                ->join(array('m' => 'TBL_KHC_STU_INFO_BASE'),
                        'k.studentid =m.code', array())
                ->where('i.mydate=?', $date)
                ->where('k.indorm=1')
                ->where('i.inout_type=\'1\'')
                ->columns(array('TotalRecords' => new Zend_Db_Expr('COUNT(*)')));
        //  echo $select->__toString();       exit;
        $stmt = $db->query($select);
        $result = $stmt->fetchAll();
        return $result;
    }

    public function getabsentcount($date = null) {
        if ($date == null) {
            $date = $this->todayinsh();
        }
        $db = Zend_Registry::get("db");

        //echo $select->__toString();exit;
        $select = 'SELECT DISTINCT COUNT(*) AS TotalRecords FROM 
(select * from TBL_IDM_INOUT where mydate=\'' . $date . '\' and inout_type in(\'1\') ) AS i INNER JOIN (select scode as tscode,max(MYTIME)as tmytime,MYDATE as tmydate from TBL_IDM_INOUT group by scode,mydate) AS ttp ON i.SCODE=ttp.tscode and ttp.tmydate=i.MYDATE and ttp.tmytime=i.MYTIME

 RIGHT JOIN (select k.* from tbl_idm_inout ii join (select scode,max(AUTOID)maxid from TBL_IDM_INOUT where mydate<=\'' . $date . '\' group by scode) tt
  on  ii.SCODE=tt.SCODE and ii.AUTOID=tt.maxid right join 
TBL_IDM_MANAGER k on k.CODECART=ii.SCODE where k.INDORM=1 and (ii.INOUT_TYPE is null or ii.INOUT_TYPE<>2)) AS k ON k.codecart=i.scode LEFT JOIN
 (select * from TBL_IDM_STATESTU where TBL_IDM_STATESTU.DATE=\'' . $date . '\' ) AS s ON k.studentid=s.code INNER JOIN 
 TBL_KHC_STU_INFO_BASE AS m ON k.studentid =m.code WHERE (k.indorm=1) AND (i.scode is null)';
        $stmt = $db->query($select);
        $result = $stmt->fetchAll();
        return $result;
    }

    public function getMenu($admin = false) {
        $authNamespace = Zend_Registry::get("authNamespace");
        $db = Zend_Registry::get("db");
        $select = $db->select()
                ->from(array('m' => 'TBL_IDM_MENU'));

        $stmt = $db->query($select);
        $menus = $stmt->fetchAll();
        $menutxt = '<ul class="sidebar-menu">
                        <li class="header">منوی اصلی</li><li><ul>';
        foreach ($menus as $item) {
            if ($authNamespace->ACL[$item['PARENT'] . '_' . $item['NAME']] == 1 || $admin):

                if ($item['TYPE'] == 'main') {

                    $menutxt .= '  </ul>
                        </li>
                        <li class="treeview">
                            <a href="#">
                                <i class="' . $item['ICON'] . '"></i>
                                <span>' . $item['TITLE'] . '</span>

                            </a> 
                            <ul class="treeview-menu">';
                }
                if ($item['TYPE'] == 'child') {
                    $menutxt .= ' <li><a href="' . $item['ADDRESS'] . '"><i class="' . $item['ICON'] . '"></i>'
                            . $item['TITLE'] . '</a></li>';
                }
            endif;
        }
        $menutxt .= '</ul></li>';

        return $menutxt;
    }

    public function get_un_mng_absentcount($date = null) {
        if ($date == null) {
            $date = $this->todayinsh();
        }
        $db = Zend_Registry::get("db");
        //echo $select->__toString();exit;
        $select = 'SELECT DISTINCT COUNT(*) AS TotalRecords FROM 
(select * from TBL_IDM_INOUT where mydate=\'' . $date . '\' and inout_type in(\'1\') ) AS i INNER JOIN (select scode as tscode,max(MYTIME)as tmytime,MYDATE as tmydate from TBL_IDM_INOUT group by scode,mydate) AS ttp ON i.SCODE=ttp.tscode and ttp.tmydate=i.MYDATE and ttp.tmytime=i.MYTIME

 RIGHT JOIN (select k.* from tbl_idm_inout ii join (select scode,max(AUTOID)maxid from TBL_IDM_INOUT where mydate<=\'' . $date . '\' group by scode) tt
  on  ii.SCODE=tt.SCODE and ii.AUTOID=tt.maxid right join 
TBL_IDM_MANAGER k on k.CODECART=ii.SCODE where k.INDORM=1 and ii.INOUT_TYPE<>2) AS k ON k.codecart=i.scode LEFT JOIN
 (select * from TBL_IDM_STATESTU where TBL_IDM_STATESTU.DATE=\'' . $date . '\' ) AS s ON k.studentid=s.code INNER JOIN 
 TBL_KHC_STU_INFO_BASE AS m ON k.studentid =m.code WHERE (k.indorm=1) AND (i.scode is null) AND s.code is null';

        //	echo $select->__toString();exit;	
        $stmt = $db->query($select);
        $result = $stmt->fetchAll();
        return $result[0]['TotalRecords'];
    }

    public function getwikendcount($date = null) {
        if ($date == null) {
            $date = $this->todayinsh();
        }
        $db = Zend_Registry::get("db");

        $select = 'SELECT DISTINCT COUNT(*) AS TotalRecords FROM  tbl_idm_inout ii join (select scode,max(AUTOID)maxid from TBL_IDM_INOUT where mydate<=\'' . $date . '\' group by scode) tt
  on  ii.SCODE=tt.SCODE and ii.AUTOID=tt.maxid  join 
TBL_IDM_MANAGER k on k.CODECART=ii.SCODE where k.INDORM=1 and ii.INOUT_TYPE=2';
        //echo $select->__toString();exit;	
        $stmt = $db->query($select);
        $result = $stmt->fetchAll();
        return $result;
    }

    public function getcountwikendHourly($date = null) {
        if ($date == null) {
            $date = $this->todayinsh();
        }
        $db = Zend_Registry::get("db");
        $select = $db->select()->distinct()
                ->from(array('i' => 'TBL_IDM_INOUT'), array())
                ->join(array('ttp' => '(select scode as tscode,max(MYTIME)as tmytime,MYDATE as tmydate from TBL_IDM_INOUT  
group by scode,mydate)'), 'i.SCODE=ttp.tscode and 
ttp.tmydate=i.MYDATE and ttp.tmytime=i.MYTIME',
                        array())
                ->join(array('k' => 'TBL_IDM_MANAGER'), 'k.codecart=i.scode',
                        array())
                ->join(array('m' => 'TBL_KHC_STU_INFO_BASE'),
                        'k.studentid =m.code', array())
                ->where('i.mydate=?', $date)
                ->where('k.indorm=1')
                ->where('i.inout_type=\'3\'')
                ->columns(array('TotalRecords' => new Zend_Db_Expr('COUNT(*)')));
        // echo $select->__toString();
        // exit;
        $stmt = $db->query($select);
        $result = $stmt->fetchAll();
        return $result;
    }

}
