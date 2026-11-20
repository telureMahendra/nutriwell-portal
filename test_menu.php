<?php
$db = new mysqli('localhost', 'root', '', 'ci4');
$menuSql = "SELECT * FROM adminmenu WHERE parentID=0 AND isActive=1 AND isDeleted=0 ORDER BY displayOrder ASC";
$menuResult = $db->query($menuSql)->fetch_all(MYSQLI_ASSOC);
$menuArray = [];
foreach ($menuResult as $menu) {
    $subSql = "SELECT userrole.rID,adminmenu.* FROM userrole INNER JOIN adminmenu ON userrole.menuID=adminmenu.menuID WHERE adminmenu.parentID=" . $menu['menuID'] . " AND userrole.userID='1' AND adminmenu.isActive=1 AND adminmenu.isDeleted=0 ORDER BY adminmenu.displayOrder ASC";
    $subResult = $db->query($subSql)->fetch_all(MYSQLI_ASSOC);
    if ($subResult) {
        foreach ($subResult as $sub) {
            if ($sub['displayOrder'] > 0) {
                $menu['sub'][] = $sub;
            }
        }
        $menuArray[] = $menu;
    }
}
$mon = array_filter($menuArray, function($v) {
    return $v['menuTitle'] == 'Monitoring' || $v['menuTitle'] == 'Banners';
});
print_r($mon);
