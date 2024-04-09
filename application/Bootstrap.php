<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
  public function _initAutoload() {
	       $authNamespace = new Zend_Session_Namespace('Auth');
           Zend_Registry::set("authNamespace", $authNamespace);
  }
      public function _initDb() {
        // include (APPLICATION_PATH . '/configs/config.php');

        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', 'staging');
        $param = $config->resources->db->params->toArray();

        //t(5905)
        if (array_key_exists('encrypt', $param)) {

            $data['host'] = $param['host'];
            $data['username'] = $param['username'];
            $data['password'] = $param['password'];
            $data['dbname'] = $param['dbname'];

            if ($param['encrypt'] == 1) {
                $pos = strpos($data['host'], '==');

                if ($pos) {
                    array_pop($param);
                    $data = $this->decrypt_AES($data);
                    $param['host'] = $data['host'];
                    $param['username'] = $data['username'];
                    $param['password'] = $data['password'];
                    $param['dbname'] = $data['dbname'];
                } else {
                    echo "<meta charset=\"utf-8\"/><div align=center style=\"font-size:50px\" >پارامترهای اتصال به پایگاه داده رمزگذاری نشده است</div></meta>";
                }
            } else if ($param['encrypt'] == 0) {
                $pos = strpos($data['host'], '==');
                if ($pos) {
                    echo "<meta charset=\"utf-8\"/><div align=center style=\"font-size:50px\" >مراحل رمزگذاری به درستی انجام نشده است</div></meta>";
                } else {
                    array_pop($param);
                }
            }
        } else {
            $pos = strpos($param['host'], '==');
            if ($pos) {
                echo "<meta charset=\"utf-8\"/><div align=center style=\"font-size:50px\" >مراحل رمزگذاری به درستی انجام نشده است</div></meta>";
            }
        }
        $itsok = true;
        if ($param && count($param) > 0) {
            $options = array(
                Zend_Db::AUTO_QUOTE_IDENTIFIERS => $config->resources->db->options
            );    
           
            $param['driver_options'] = array("CharacterSet" => "utf-8");
            $param['options'] = $options;
            $db = Zend_Db::factory($config->resources->db->adapter, $param);					
            $profiler = new Zend_Db_Profiler_Firebug("All query");
            $profiler->setEnabled(true);
            $db->setProfiler($profiler);
            Zend_Registry::set("db", $db);
			try {  
			$db->getConnection();
		
			} catch( Exception $e ) {			
				echo $e->getMessage(). "<meta charset=\"utf-8\"/><div align=center style=\"font-size:50px\" > اتصال به بانک اطلاعاتی امکان پذیر نمی باشد</div></meta>";
                exit;
			}
        }
    }
    public function decrypt_AES($data) {

        $key = '6kl4r6k66uko4dq2jmic122f09c00ew9';
        $iv = '73045h837209u66l';
        $data['host'] = str_replace(" ", '+', $data['host']);
        $param_tmp['host'] = openssl_decrypt(base64_decode($data['host']), 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        $data['username'] = str_replace(" ", '+', $data['username']);
        $param_tmp['username'] = openssl_decrypt(base64_decode($data['username']), 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        $data['password'] = str_replace(" ", '+', $data['password']);
        $param_tmp['password'] = openssl_decrypt(base64_decode($data['password']), 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        $data['dbname'] = str_replace(" ", '+', $data['dbname']);
        $param_tmp['dbname'] = openssl_decrypt(base64_decode($data['dbname']), 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return $param_tmp;
    }


}

