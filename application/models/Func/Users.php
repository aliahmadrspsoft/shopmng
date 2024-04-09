<?php

/**
 * Description of users
 *
 * @author Client 5
 */
class Application_Model_Func_Users {

    public function getallusersbyname($name = null) {
        $db = Zend_Registry::get('db');
        $select = $db->select()->from(array('u' => 'TBL_IDM_USER'), array('name', 'password', 'acl', 'active','forceabsentreport'));

        $select = $select->where('name=?', array($name));

        //echo $select->__toString();
        $stmt = $db->query($select);
        $result = $stmt->fetchAll();
        return $result;
    }

    public function getallusers($name = null) {
        $db = Zend_Registry::get('db');
        $select = $db->select()->from(array('u' => 'TBL_IDM_USER'), array('name', 'password', 'acl', 'active','forceabsentreport'));
        if (isset($name)) {
            $select = $select->where('name=?', array($name));
        }
        //echo $select->__toString();
        $stmt = $db->query($select);
        $result = $stmt->fetchAll();
        return $result;
    }

    public function checkpass($user, $pass) {
        $db = Zend_Registry::get('db');
        $select = $db->select()->from(array('u' => 'TBL_IDM_USER'));
        $select = $select->where('name=?', array($user))->where('password=?', array(hash('sha512', $pass)));
        // echo $select->__toString();
        // exit;
        $stmt = $db->query($select);
        $result = $stmt->fetchAll();
        //  var_export($result);exit;
        return $result;
    }

    public function saveusers($post) {
        $db = Zend_Registry::get("db");
        try {
            $result = array();
            if (isset($post['name'])) {
                $result = $this->getallusers($post['name']);
            }
            if (count($result) > 0) {
                $arrayupdate = array();
                if (isset($post['active'])) {
                    $arrayupdate['active'] = $post['active'] == 'true' ? 1 : 0;
                }
                if (isset($post['force'])) {
                    $arrayupdate['forceabsentreport'] = $post['force'] == 'true' ? 1 : 0;
                }
                if (isset($post['access'])) {
                    $arrayupdate['acl'] = $post['access'];
                }
                if (isset($post['password'])) {
                    $arrayupdate['password'] = hash('sha512', $post['password']);
                }
        
                $db->update(
                        'TBL_IDM_USER',
                        $arrayupdate,
                        '[NAME]=\'' . trim($post['name']) . '\''
                );
            } else {
                $db->insert('TBL_IDM_USER', array('forceabsentreport'=>$post['force'],'active' => $post['active'], 'acl' => $post['access'], 'password' => hash('sha512', $post['password']), 'name' => $post['name']));
            }
            return true;
        } catch (Exception $e) {
            echo $e->getMessage();
            return null;
        }
        return $result;
    }

    public function deleteusr($post) {
        $db = Zend_Registry::get("db");
        try {
            $db->delete('TBL_IDM_USER', 'name=\'' . $post['name'] . '\'');
            return true;
        } catch (Exception $e) {
            echo $e->getMessage();
            return null;
        }
        return $result;
    }

}
