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
class Application_Model_Func_Access {

    public $access;

    public function getAccess() {
        $this->setAccess();
        return $this->access;
    }

    public function getMenu($admin = false) {
        $authNamespace = Zend_Registry::get("authNamespace");
        $db = Zend_Registry::get("db");
        $select = $db->select()
                ->from(array('m' => 'TBL_IDM_MENU'));
        //var_export($select->__toString());exit;
        $stmt = $db->query($select);
        $menus = $stmt->fetchAll();
        $menutxt = '<ul class="sidebar-menu">
                        <li class="header">منوی اصلی</li><li><ul>';
        foreach ($menus as $item) {
            if ($authNamespace->ACL[$item['PARENT'] . '_' . $item['NAME']] == 1 || $admin):
                if ($item['TYPE'] == 'parent') {
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
        $menutxt = '</ul></li>';
        return $menutxt;
    }

    public function checkAccess($request, $isallow = false) {
        $authNamespace = Zend_Registry::get("authNamespace");
        $timestamp = mktime(date("H"), date("i"), 0, date('m'), date('d'), date('Y'));

        if ($timestamp - $authNamespace->loginTime >= 3600) {

            return array('result' => false, 'error' => 2);
            // echo ' <script language="javascript" type="text/javascript"> parent.window.location.href = "/index?error=2";</script> ';
            // exit;
        }

        if ($authNamespace->ACL[$request->getControllerName() . '_' . $request->getActionName()] != 1 && !$isallow):
            return array('result' => false, 'error' => 3);
// echo ' <script language="javascript" type="text/javascript">  bootbox.alert({message: \'شما به این قسمت دسترسی ندارید\', locale: \'fa\'});</script> ';
//  exit;
        endif;
        $authNamespace->loginTime = mktime(date("H"), date("i"), 0, date('m'), date('d'), date('Y'));
        return array('result' => true, 'error' => 0);
    }

    public function setAccess() {
        $this->access = array(
            0 => array('title' => 'داشبورد', 'controller' => 'dashboard', 'actions' => array(
                    array('title' => 'خانه', 'action' => 'index'),
                    array('title' => ' دریافت ترافیک آنلاین', 'action' => 'getonlinetrafic'),
                    array('title' => 'ویرایش نوع تردد', 'action' => 'editkindtrafic'),
                    array('title' => 'دکمه ثبت تردد دستی', 'action' => 'addrow'),
                    array('title' => 'ذخیره ثبت تردد دستی', 'action' => 'addrowsave'),
                )),
            1 => array('title' => 'کنترل حضورغیاب', 'controller' => 'report', 'actions' => array(
                    array('title' => 'گزارش حاضرین', 'action' => 'persent'),
                    array('title' => 'دریافت گزارش حاضرین', 'action' => 'getpersenrlist'),
                    array('title' => 'گزارش غائبین', 'action' => 'absent'),
                    array('title' => 'دریافت گزارش غائبین', 'action' => 'getabsentlist'),
                    array('title' => 'امکان درج در پرونده', 'action' => 'insertabsentlist'),
                    array('title' => 'امکان ارسال اس ام اس', 'action' => 'sendsmsabsentlist'),
                    array('title' => 'گزارش مرخصی روزانه', 'action' => 'wikend'),
                    array('title' => 'دریافت گزارش مرخصی روزانه', 'action' => 'getwikendtlist'),
                    array('title' => 'گزارش مرخصی ساعتی', 'action' => 'wikendhourly'),
                    array('title' => 'دریافت گزارش مرخصی ساعتی', 'action' => 'getwikendhourlylist'),
                    array('title' => 'گزارش حراست', 'action' => 'gard'),
                    array('title' => 'دریافت گزارش حراست', 'action' => 'getgardlist'),
                )),
            2 => array('title' => 'مدیریت اطلاعات', 'controller' => 'manager', 'actions' => array(
                    array('title' => 'تعریف کد دانشجویی', 'action' => 'index'),
                    array('title' => 'مشاهده اطلاعات دانشجو', 'action' => 'fetchdata'),
                    array('title' => ',ویرایش اطلاعات دانشجو', 'action' => 'edit'),
                    array('title' => 'بررسی کد دانشجویی تکراری', 'action' => 'chkval'),
                    array('title' => 'دریافت اطلاعات دانشجویان', 'action' => 'getinfo'),
                    array('title' => ' ورود دیتای دانشجویان', 'action' => 'inputexcel'),
                    array('title' => 'ورود اطلاعات اکسل', 'action' => 'insertexcel'),
                )),
            3 => array('title' => 'مدیریت کاربران', 'controller' => 'user', 'actions' => array(
                    array('title' => 'مشاهده کاربران سیستم', 'action' => 'index'),
                    array('title' => 'دریافت اطلاعات کاربران سیستم', 'action' => 'getalluserlist'),
                    array('title' => 'فعال /غیر فعال کردن کاربر', 'action' => 'updateactive'),
                    array('title' => 'اجبار در گزارش غیبت', 'action' => 'forceabsentreport'),
                    array('title' => 'حذف کاربران', 'action' => ' deleteusers'),
                    array('title' => 'صفحه افزودن کاربر', 'action' => 'adduser'),
                    array('title' => 'ذخیره افزودن کاربر', 'action' => 'saveaddusers'),
                    array('title' => 'صفحه تغییر پسورد', 'action' => 'changepassword'),
                    array('title' => 'ذخیره تغییرات پسورد', 'action' => 'savepasswordusers'),
                    array('title' => 'ذخیره اطلاعات کاربری', 'action' => 'saveusers'),
                    array('title' => 'تغییر دسترسی کاربران', 'action' => 'editaccess'),
                    array('title' => 'ذخیره دسترسی کاربران', 'action' => 'saveaccess'),
                )),
            5 => array('title' => 'تنظیمات سیستم', 'controller' => 'device', 'actions' => array(
                    array('title' => 'مشاهده دستگاههای تردد', 'action' => 'index'),
                    array('title' => 'دریافت اطلاعات دستگاههای تردد', 'action' => 'getinfo'),
                    array('title' => 'دریافت مشخصات دستگاه', 'action' => 'fetchdata'),
                    array('title' => 'ویرایش مشخصات دستگاه', 'action' => 'edit'),
                    array('title' => 'حذف دستگاه', 'action' => 'deletedvc'),
                    array('title' => 'تنظیمات سیستم', 'action' => 'setting'),
                ))
        );
    }

}
