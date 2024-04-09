<?php

class Application_Model_Func_Device {
    public function fetchsetting() {
        $db = Zend_Registry::get("db");
        $select = $db->select()
                ->from(array('d' => ' TBL_IDM_SETTING'));

        $stmt = $db->query($select);
        $result = $stmt->fetchAll();
        return $result;
    }
    public function fetchbyID($code) {
        $db = Zend_Registry::get("db");
        $select = $db->select()
                ->from(array('d' => ' TBL_KHC_DEVICE'))
                ->where('d.ID=?', array($code));

        $stmt = $db->query($select);
        $result = $stmt->fetchAll();
        return $result;
    }
    public function savesettinginfo($post) {
        $db = Zend_Registry::get("db");
        try {
            $arraydetail = array(
                'SMSURL' => $post['SMSURL'],           
                'ABSENTSMSACTIVE' => $post['ABSENTSMSACTIVE']==1?1:0,
                'WIKENDSMSACTIVE' => $post['WIKENDSMSACTIVE']==1?1:0,
             );
            if (!empty($post['ID'])) {
                $db->update(
                        'TBL_IDM_SETTING',
                        $arraydetail,
                        'ID=\'' . $post['ID'].'\''
                );
            } else {
                $ins = $db->insert('TBL_IDM_SETTING', $arraydetail);
            }
            return true;
        } catch (Exception $e) {

            echo $e->getMessage();
            return null;
        }
        return $result;
    }
    public function saveinfo($post) {
        $db = Zend_Registry::get("db");
        try {
            $arraydetail = array(
                'KINDSET' => $post['KINDSET'],
                'NAMESET' => $post['NAMESET'],
                'IPSET' => $post['IPSET'],
                'IPHOST' => $post['IPHOST'],
                'PORTSET' => $post['PORTSET'],
                'GPSSET' => $post['GPSSET'],
                'NUMSET' => $post['NUMSET'],
                 'GETDATATYPE' => $post['GETDATATYPE'],
                 'DELETEDATA' => $post['DELETEDATA']==1?1:0,
                'STATUSSET' => $post['STATUSSET']);
            if (!empty($post['ID'])) {
                $db->update(
                        'TBL_KHC_DEVICE',
                        $arraydetail,
                        'ID=\'' . $post['ID'].'\''
                );
            } else {
                $ins = $db->insert('TBL_KHC_DEVICE', $arraydetail);
            }
            return true;
        } catch (Exception $e) {

            echo $e->getMessage();
            return null;
        }
        return $result;
    }

    public function deletedvc($post) {
        $db = Zend_Registry::get("db");
        try {
            $db->delete('TBL_KHC_DEVICE', 'ID=\'' . $post['ID'] . '\'');
            return true;
        } catch (Exception $e) {
            echo $e->getMessage();
            return null;
        }
        return $result;
    }

}
