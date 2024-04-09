<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

/**
 * Description of access
 *
 * @author Client 5
 */
class Application_Model_Func_Excel {

    public $filepath = null;
    public $fileArray;

    public function __construct() {
        $this->fileUpload();
        $this->readfiletoarray($this->filepath);
    }

    public function fileUpload() {
        if (isset($_FILES['fileexcel'])) {
            $errors = array();
            $file_name = $_FILES['fileexcel']['name'];
            $file_size = $_FILES['fileexcel']['size'];
            $file_tmp = $_FILES['fileexcel']['tmp_name'];
            $file_type = $_FILES['fileexcel']['type'];
            $file_ext = strtolower(end(explode('.', $_FILES['fileexcel']['name'])));

            $extensions = array("xls", "xlsx");

            if (in_array($file_ext, $extensions) === false) {
                $errors[] = "فقط فرمت استاندارد فایل اکسل قابل قبول است";
                $txtres='<div style=\"text-align:center;background:red; font-weight:bold\">'.'فقط فرمت استاندارد فایل اکسل قابل قبول است'.'</div>';
            }

            if ($file_size > 2097152) {
                $errors[] = ' 2 MB حداکثر اندازه فایل';
                $txtres='<div style=\"text-align:center;background:red; font-weight:bold\">'.' 2 MB حداکثر اندازه فایل'.'</div>';
            }

            if (empty($errors) == true) {
                move_uploaded_file($file_tmp, "file/" . $file_name);
                $txtres= "<div style=\"text-align:center;background:green; font-weight:bold\">"."فایل با  موفقیت بارگذاری شد در حال انجام عملیات درج اطلاعات..."."</div>";
                $this->filepath = "file/" . $file_name;
            } else {
                print_r($errors);
            }
            
            echo '<script  language="javascript" >parent.document.getElementById("noticDiv").innerHTML +="<br/>'.$txtres.'";'
                    . '</script>';
        }
    }

    public function readfiletoarray($inputFileName) {
        try {
            $myobject = new PHPExcel();
            $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch (Exception $e) {
            die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
        }

//  Get worksheet dimensions
        $sheet = $objPHPExcel->getSheet(0);

        $rowData = array();
//  Loop through each row of the worksheet in turn
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

// loop through the rows and columns of the sheet
        for ($row = 2; $row <= $highestRow; $row++) {
            for ($col = 'A'; $col <= $highestColumn; $col++) {
                // read the cell value and store it in the array
                $rowData[$row][$col] = $sheet->getCell($col . $row)->getValue();
            }
        }
        $this->fileArray = $rowData;
    }

    public function fetchbycode($code) {
        $db = Zend_Registry::get("db");
        $select = $db->select()
                ->from(array('m' => ' TBL_KHC_STU_INFO_BASE'),
                        array('NAME_FAMILY', 'CODE'))
                ->where('m.code=?', array($code));

        $stmt = $db->query($select);
        $result = $stmt->fetchAll();
        return $result;
    }

    public function saveStudent($student) {
        $db = Zend_Registry::get("db");
        try {
        
            $result = array();
            $result = $this->fetchbycode($student['A']);

            if (count($result) > 0) {
                return'<div style=\"text-align:center;background:yellow; font-weight:bold\">  دانشجو' . $student['B'] . '  ' . $student['A'] . '  قبلا در سیستم اضافه شده است</div>';
            } else {
                $db->insert('TBL_KHC_STU_INFO_BASE',
                        array(
                            'CODE' => $student['A'],
                            'NAME_FAMILY' => $student['B'] ,
                            'FATHERNAME' => $student['C'] ,
                            'SEX' => $student['D'] ,
                ));
                return'<div style=\"text-align:center;background:green; font-weight:bold\">  دانشجو' . $student['B'] . '  ' . $student['A'] . ' به سیستم اضافه شد</div>';
            }
        } catch (Exception $e) {
            var_export($student);
            echo $student['A']. $e->getMessage();
            return'<div style=\"text-align:center;background:red; font-weight:bold\">  دانشجو' . $student['B'] . '  ' . $student['A'] . ' به سیستم اضافه نشد</div>';
        }
       
    }

}
