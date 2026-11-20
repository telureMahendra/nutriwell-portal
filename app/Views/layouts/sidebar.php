<?php
$activeMenu  = strtolower(class_basename(service('router')->controllerName()));

$controllerName  = class_basename(service('router')->controllerName());
$session = session();


//print'<pre>';
//print_r($session->get());
//die();



?>
<!-- BEGIN SIDEBAR -->
<div class="page-sidebar-wrapper">
    <!-- DOC: Set data-auto-scroll="false" to disable the sidebar from auto scrolling/focusing -->
    <!-- DOC: Change data-auto-speed="200" to adjust the sub menu slide up/down speed -->
    <div class="page-sidebar navbar-collapse collapse">
        <!-- BEGIN SIDEBAR MENU -->
        <!-- DOC: Apply "page-sidebar-menu-light" class right after "page-sidebar-menu" to enable light sidebar menu style(without borders) -->
        <!-- DOC: Apply "page-sidebar-menu-hover-submenu" class right after "page-sidebar-menu" to enable hoverable(hover vs accordion) sub menu mode -->
        <!-- DOC: Apply "page-sidebar-menu-closed" class right after "page-sidebar-menu" to collapse("page-sidebar-closed" class must be applied to the body element) the sidebar sub menu mode -->
        <!-- DOC: Set data-auto-scroll="false" to disable the sidebar from auto scrolling/focusing -->
        <!-- DOC: Set data-keep-expand="true" to keep the submenues expanded -->
        <!-- DOC: Set data-auto-speed="200" to adjust the sub menu slide up/down speed -->

        <?php
         if (session()->get('usertype') == 'mentor') { ?>
            <ul class="page-sidebar-menu " data-keep-expanded="false" data-auto-scroll="true" data-slide-speed="200">
            <li class="sidebar-search-wrapper <?php echo (($activeMenu == 'index_con') ? 'start active open' : ''); ?>">
                        <a href="<?= base_url('mentor'); ?>">
                            <i class="fa fa-dashboard"></i>
                            <span class="title">Dashboard</span>
                            <span class="selected"></span>
                        </a>
                    </li>

            <li class="sidebar-search-wrapper <?php echo (($activeMenu == 'appointment') ? 'start active open' : ''); ?>">
                    <a href="<?= base_url('mentor/appointment'); ?>">
                        <i class="fa fa-calendar"></i>
                        <span class="title">Appointment</span>
                        <span class="selected"></span>
                    </a>
                </li>
                <li class="sidebar-search-wrapper <?php echo (($activeMenu == 'client') ? 'start active open' : ''); ?>">
                    <a href="<?= base_url('mentor/clients'); ?>">
                        <i class="fa fa-users"></i>
                        <span class="title">Clients</span>
                        <span class="selected"></span>
                    </a>
                </li>
                <li class="sidebar-search-wrapper <?php echo (($activeMenu == 'challenges') ? 'start active open' : ''); ?>">
                    <a href="<?= base_url('mentor/challenges'); ?>">
                        <i class="fa fa-trophy"></i>
                        <span class="title">Challenges</span>
                        <span class="selected"></span>
                    </a>
                </li>
            </ul>
        <?php } else { ?>
            <ul class="page-sidebar-menu " data-keep-expanded="false" data-auto-scroll="true" data-slide-speed="200">

                <!-- DOC: To remove the search box from the sidebar you just need to completely remove the below "sidebar-search-wrapper" LI element -->
                <?php /*<li class="sidebar-search-wrapper">
                    <!-- BEGIN RESPONSIVE QUICK SEARCH FORM -->
                    <!-- DOC: Apply "sidebar-search-bordered" class the below search form to have bordered search box -->
                    <!-- DOC: Apply "sidebar-search-bordered sidebar-search-solid" class the below search form to have bordered & solid search box -->
                    <form class="sidebar-search " action="extra_search.html" method="POST">
                        <a href="javascript:;" class="remove">
                        <i class="icon-close"></i>
                        </a>
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Search...">
                            <span class="input-group-btn">
                            <a href="javascript:;" class="btn submit"><i class="icon-magnifier"></i></a>
                            </span>
                        </div>
                    </form>
                    <!-- END RESPONSIVE QUICK SEARCH FORM -->
                </li>
                */ ?>


                <?php
                $menuArray = session()->get('menuArray');
                if ($menuArray) {

                    foreach ($menuArray as $mainMenu) {
                        
                        if (isset($mainMenu["sub"]) && !empty($mainMenu["sub"])) {

                            $mainIcon  = 'fa-dashboard';
                            if ($mainMenu["icon"] != "") {
                                $mainIcon  = $mainMenu["icon"];
                            }

                            $controller = strtolower($mainMenu["controller"]);

                            $activeClass = '';
                            if ($activeMenu == $controller) {
                                $activeClass = 'start active open';
                            } ?>

                            <li class="sidebar-search-wrapper <?php echo $activeClass;?>">
                                <a href="javascript:void(0);">
                                    <i class="fa <?php echo $mainIcon; ?>"></i>
                                    <span class="title"><?php echo $mainMenu["menuTitle"]; ?></span>
                                    <span class="arrow"></span>
                                </a>

                                <?php ?>
                                <ul class="sub-menu">
                                    <?php foreach ($mainMenu["sub"] as $subMenu) {
                                        $subIcon  = 'fa-arrow-right';
                                        if ($subMenu["icon"] != "") {
                                            $subIcon  = $subMenu["icon"];
                                        }

                                        //$controller = strtolower($subMenu["controller"]);
                                        $url = base_url($controller);
                                        if ($subMenu["methodName"] != "index") {
                                            $url = base_url($controller . '/' . $subMenu["methodName"]);
                                        }


                                    ?>
                                        <li>
                                            <a href="<?php echo $url; ?>"><i class="fa <?php echo $subIcon; ?>" aria-hidden="true"></i><?php echo $subMenu["menuTitle"]; ?></a>
                                        </li>
                                    <?php } ?>

                                </ul>
                            </li>

                <?php }
                    }
                } ?>

                <!-- Challenges — always visible for admin -->
                <li class="sidebar-search-wrapper <?php echo (($activeMenu == 'challenges') ? 'start active open' : ''); ?>">
                    <a href="<?= base_url('challenges'); ?>">
                        <i class="fa fa-trophy"></i>
                        <span class="title">Challenges</span>
                        <span class="selected"></span>
                    </a>
                </li>

                <!-- Monitoring — always visible for admin -->
                <li class="sidebar-search-wrapper <?php echo (($activeMenu == 'monitoring') ? 'start active open' : ''); ?>">
                    <a href="<?= base_url('monitoring'); ?>">
                        <i class="fa fa-bar-chart"></i>
                        <span class="title">Monitoring</span>
                        <span class="selected"></span>
                    </a>
                </li>


                <?php /*

                <!-- 
                <?php if (CheckAccess('Index_con', "index")) { ?>
                    <li class="sidebar-search-wrapper <?php echo (($activeMenu == 'index_con') ? 'start active open' : ''); ?>">
                        <a href="<?= base_url(); ?>">
                            <i class="fa fa-dashboard"></i>
                            <span class="title">Dashboard</span>
                            <span class="selected"></span>
                        </a>
                    </li>
                <?php } ?>

                <?php if (CheckAccess('Users', "index")) { ?>
                <li <?php echo (($activeMenu == 'users') ? 'class="start active open"' : ''); ?>>
                    <a href="javascript:;">
                        <i class="fa fa-users"></i>
                        <span class="title">User Info</span>
                        <span class="arrow "></span>
                    </a>
                    <ul class="sub-menu">
                        <li>
                            <a href="<?= base_url(); ?>users/"><i class="fa fa-list" aria-hidden="true"></i>
                                User List</a>
                        </li>
                        <li>
                            <a href="<?= base_url(); ?>users/add"><i class="fa fa-plus"></i>
                                Add User</a>
                        </li>
                    </ul>
                </li>
                <?php } ?>

                

                <?php if (CheckAccess('Medical_condition', "index")) { ?>
                <li <?php echo (($activeMenu == 'medical_condition') ? 'class="start active open"' : ''); ?>>
                    <a href="javascript:;">
                        <i class="fa fa-medkit"></i>
                        <span class="title">Medical Condition</span>
                        <span class="arrow "></span>
                    </a>
                    <ul class="sub-menu">
                        <li>
                            <a href="<?= base_url(); ?>medical_condition/"><i class="fa fa-list" aria-hidden="true"></i>
                                Medical Condition List</a>
                        </li>
                        <li>
                            <a href="<?= base_url(); ?>medical_condition/add"><i class="fa fa-plus"></i>
                                Add Medical Condition</a>
                        </li>
                    </ul>
                </li>
                <?php } ?>

                <?php if (CheckAccess('Language', "index")) { ?>
                <li <?php echo (($activeMenu == 'language') ? 'class="start active open"' : ''); ?>>
                    <a href="javascript:;">
                        <i class="fa fa-language"></i>
                        <span class="title">Language Info</span>
                        <span class="arrow "></span>
                    </a>
                    <ul class="sub-menu">
                        <li>
                            <a href="<?= base_url(); ?>language/"><i class="fa fa-list" aria-hidden="true"></i>
                                Language List</a>
                        </li>
                        <li>
                            <a href="<?= base_url(); ?>language/add"><i class="fa fa-plus"></i>
                                Add Language</a>
                        </li>
                    </ul>
                </li>
                <?php } ?>

                <?php if (CheckAccess('Faq', "index")) { ?>
                <li <?php echo (($activeMenu == 'faq') ? 'class="start active open"' : ''); ?>>
                    <a href="javascript:;">
                        <i class="fa fa-question-circle"></i>
                        <span class="title">FAQ Info</span>
                        <span class="arrow "></span>
                    </a>
                    <ul class="sub-menu">
                        <li>
                            <a href="<?= base_url(); ?>faq/"><i class="fa fa-list" aria-hidden="true"></i>
                                FAQ List</a>
                        </li>
                        <li>
                            <a href="<?= base_url(); ?>faq/add"><i class="fa fa-plus"></i>
                                Add FAQ</a>
                        </li>
                    </ul>
                </li>
                <?php } ?>


                <?php if (CheckAccess('Testimonial', "index")) { ?>
                <li <?php echo (($activeMenu == 'testimonial') ? 'class="start active open"' : ''); ?>>
                    <a href="javascript:;">
                        <i class="fa fa-comments"></i>
                        <span class="title">Testimonial Info</span>
                        <span class="arrow "></span>
                    </a>
                    <ul class="sub-menu">
                        <li>
                            <a href="<?= base_url(); ?>testimonial/"><i class="fa fa-list" aria-hidden="true"></i>
                                Testimonial List</a>
                        </li>
                        <li>
                            <a href="<?= base_url(); ?>testimonial/add"><i class="fa fa-plus"></i>
                                Add Testimonial</a>
                        </li>
                    </ul>
                </li>
                <?php } ?>

                <?php if (CheckAccess('City', "index")) { ?>
                <li <?php echo (($activeMenu == 'city') ? 'class="start active open"' : ''); ?>>
                    <a href="javascript:;">
                        <i class="fa fa-building"></i>
                        <span class="title">City Info</span>
                        <span class="arrow "></span>
                    </a>
                    <ul class="sub-menu">
                        <li>
                            <a href="<?= base_url(); ?>city/"><i class="fa fa-list" aria-hidden="true"></i>
                                City List</a>
                        </li>
                        <li>
                            <a href="<?= base_url(); ?>city/add"><i class="fa fa-plus"></i>
                                Add City</a>
                        </li>
                    </ul>
                </li>
                <?php } ?>

                <?php if (CheckAccess('Goal', "index")) { ?>
                <li <?php echo (($activeMenu == 'goal') ? 'class="start active open"' : ''); ?>>
                    <a href="javascript:;">
                        <i class="fa fa-bullseye"></i>
                        <span class="title">Goal Info</span>
                        <span class="arrow "></span>
                    </a>
                    <ul class="sub-menu">
                        <li>
                            <a href="<?= base_url(); ?>goal/"><i class="fa fa-list" aria-hidden="true"></i>
                                Goal List</a>
                        </li>
                        <li>
                            <a href="<?= base_url(); ?>goal/add"><i class="fa fa-plus"></i>
                                Add Goal</a>
                        </li>
                    </ul>
                </li>
                <?php } ?>

                <?php if (CheckAccess('Goal', "index")) { ?>
                <li <?php echo (($activeMenu == 'lifestyle') ? 'class="start active open"' : ''); ?>>
                    <a href="javascript:;">
                        <i class="fa fa-users"></i>
                        <span class="title">Lifestyle Info</span>
                        <span class="arrow "></span>
                    </a>
                    <ul class="sub-menu">
                        <li>
                            <a href="<?= base_url(); ?>lifestyle/"><i class="fa fa-list" aria-hidden="true"></i>
                                Lifestyle List</a>
                        </li>
                        <li>
                            <a href="<?= base_url(); ?>lifestyle/add"><i class="fa fa-plus"></i>
                                Add Lifestyle</a>
                        </li>
                    </ul>
                </li>
                <?Php } ?>


                <li <?php echo (($activeMenu == 'food_type' || $activeMenu == 'food') ? 'class="start active open"' : ''); ?>>
                    <a href="javascript:;">
                        <i class="fa fa-cutlery"></i>
                        <span class="title">Food Info</span>
                        <span class="arrow "></span>
                    </a>
                    <ul class="sub-menu">
                        <li>
                            <a href="<?= base_url(); ?>food_type/"><i class="fa fa-list" aria-hidden="true"></i>
                                Food Type List</a>
                        </li>
                        <li>
                            <a href="<?= base_url(); ?>food_type/add"><i class="fa fa-plus"></i>
                                Add Food Type</a>
                        </li>
                        <li>
                            <a href="<?= base_url(); ?>food/"><i class="fa fa-list" aria-hidden="true"></i>
                                Food List</a>
                        </li>
                        <li>
                            <a href="<?= base_url(); ?>food/add"><i class="fa fa-plus"></i>
                                Add Food</a>
                        </li>
                    </ul>
                </li>
                <li <?php echo (($activeMenu == 'coupon') ? 'class="start active open"' : ''); ?>>
                    <a href="javascript:;">
                        <i class="fa fa-tags"></i>
                        <span class="title">Coupon Info</span>
                        <span class="arrow "></span>
                    </a>
                    <ul class="sub-menu">
                        <li>
                            <a href="<?= base_url(); ?>coupon/"><i class="fa fa-list" aria-hidden="true"></i>
                                Coupon List</a>
                        </li>
                        <li>
                            <a href="<?= base_url(); ?>coupon/add"><i class="fa fa-plus"></i>
                                Add Coupon</a>
                        </li>
                    </ul>
                </li>
                <li <?php echo (($activeMenu == 'doctor') ? 'class="start active open"' : ''); ?>>
                    <a href="javascript:;">
                        <i class="fa fa-user-md"></i>
                        <span class="title">Mentor Info</span>
                        <span class="arrow "></span>
                    </a>
                    <ul class="sub-menu">
                        <li>
                            <a href="<?= base_url(); ?>doctor/"><i class="fa fa-list" aria-hidden="true"></i>
                                Mentor List</a>
                        </li>
                        <li>
                            <a href="<?= base_url(); ?>doctor/add"><i class="fa fa-plus"></i>
                                Add Mentor</a>
                        </li>
                    </ul>
                </li>
                <li <?php echo (($activeMenu == 'clients') ? 'class="start active open"' : ''); ?>>
                    <a href="javascript:;">
                        <i class="fa fa-user-plus"></i>
                        <span class="title">Client Info</span>
                        <span class="arrow "></span>
                    </a>
                    <ul class="sub-menu">
                        <li>
                            <a href="<?= base_url(); ?>clients/"><i class="fa fa-list" aria-hidden="true"></i>
                                Client List</a>
                        </li>
                        <li>
                            <a href="<?= base_url(); ?>clients/add"><i class="fa fa-plus"></i>
                                Add Client</a>
                        </li>
                    </ul>
                </li>
                <li <?php echo (($activeMenu == 'plan') ? 'class="start active open"' : ''); ?>>
                    <a href="javascript:;">
                        <i class="fa fa-file-text"></i>
                        <span class="title">Plan Info</span>
                        <span class="arrow "></span>
                    </a>
                    <ul class="sub-menu">
                        <li>
                            <a href="<?= base_url(); ?>plan/"><i class="fa fa-list" aria-hidden="true"></i>
                                Plan List</a>
                        </li>
                        <li>
                            <a href="<?= base_url(); ?>plan/add"><i class="fa fa-plus"></i>
                                Add Plan</a>
                        </li>
                    </ul>
                </li>
                <li <?php echo (($activeMenu == 'appointment') ? 'class="start active open"' : ''); ?>>
                    <a href="javascript:;">
                        <i class="fa fa-calendar"></i>
                        <span class="title">Appointment Info</span>
                        <span class="arrow "></span>
                    </a>
                    <ul class="sub-menu">
                        <li>
                            <a href="<?= base_url(); ?>appointment/"><i class="fa fa-list" aria-hidden="true"></i>
                                Appointment List</a>
                        </li>
                    </ul>
                </li>
                <li <?php echo (($activeMenu == 'booking') ? 'class="start active open"' : ''); ?>>
                    <a href="javascript:;">
                        <i class="fa fa-check-circle"></i>
                        <span class="title">Booking Info</span>
                        <span class="arrow "></span>
                    </a>
                    <ul class="sub-menu">
                        <li>
                            <a href="<?= base_url(); ?>booking/"><i class="fa fa-list" aria-hidden="true"></i>
                                Booking List</a>
                        </li>
                    </ul>
                </li> -->

            </ul>*/ ?>
            <?php } ?>

    </div>
</div>