<?
	header('Content-Type: text/html; charset=UTF-8');
	session_start();
	set_time_limit(0);

	require_once($_SERVER['DOCUMENT_ROOT'].'/core/vk.api.php');
	$conn = mysql_connect('127.0.0.1', 'targetolog', 'upaTLNX2a2VeXWAH');
	mysql_select_db('targetolog');
    mysql_query("SET character_set_client = 'utf8'", $conn);
    mysql_query("SET character_set_connection = 'utf8'", $conn);
    mysql_query("SET character_set_results = 'utf8'", $conn);
	mysql_query("SET NAMES 'utf8'", $conn);

	if (!empty($_SESSION['token']))
		define('TOKEN', $_SESSION['token']);
	else {
		header('Location: /');
		die();
	}

	if (empty($_SESSION['user']['id'])){
		$vk_result_id = json_decode(vk_query('https://api.vk.com/method/users.get', array(
			'fields' => 'id, screen_name, photo_50, photo_200',
			'access_token' => TOKEN,
			'v' => '5.37'
		)), true);

		$uid = $_SESSION['user']['id'] = $vk_result_id['response'][0]['id'];
		$name = $_SESSION['user']['name'] = $vk_result_id['response'][0]['first_name'].' '.$vk_result_id['response'][0]['last_name'];
		$_SESSION['user']['phote'] = $vk_result_id['response'][0]['photo_50'];
		if (isset($vk_result_id['response'][0]['photo_200']))
			$_SESSION['user']['phote_big'] = $vk_result_id['response'][0]['photo_200'];
		else
			$_SESSION['user']['phote_big'] = $vk_result_id['response'][0]['photo_50'];
		$email = $_SESSION['email'];

		$sql = "INSERT INTO `tlog_users` (`uid`, `name`, `email`, `lastlogin`, `login_count`) VALUES($uid, '$name', '$email', UNIX_TIMESTAMP(NOW()), 1) ON DUPLICATE KEY UPDATE `lastlogin` = UNIX_TIMESTAMP(NOW()), `login_count` = `login_count` + 1;";
		mysql_query($sql);
	}


	if (in_array($_SESSION['user']['id'], array(16478739, 265150893))){
		die('Для Вашего аккаунта доступ заблокирован.');
	}


	if (empty($_GET['m']))
		$_GET['m'] = 'c_group';
?>
<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>ТаргетоLOG &mdash; уникальный инструмент таргетирования</title>
   	<link rel="shortcut icon" href="/favicon.png" type="image/x-icon">
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="../../plugins/datatables/dataTables.bootstrap.css">
	<link rel="stylesheet" type="text/css" href="/js/dataTables/tabletools/css/dataTables.tableTools.min.css"/>
    <!-- Theme style -->
    <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
    <!-- AdminLTE Skins. We have chosen the skin-blue for this starter
          page. However, you can choose any other skin. Make sure you
          apply the skin class to the body tag so the changes take effect.
    -->
    <link rel="stylesheet" href="dist/css/skins/skin-blue.min.css">
    <link rel="stylesheet" href="css/main.css" type="text/css" />
	<link rel="stylesheet" type="text/css" href="/css/jquery-ui-1.10.4.custom.min.css">
	<link rel="stylesheet" href="/css/jquery.ui.datepicker.min.css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <!-- jQuery 2.1.4 -->
    <script src="../../plugins/jQuery/jQuery-2.1.4.min.js"></script>
    <style>
      .example-modal .modal {
        position: relative;
        top: auto;
        bottom: auto;
        right: auto;
        left: auto;
        display: block;
        z-index: 1;
      }
      .example-modal .modal {
        background: transparent !important;
      }
    </style>
  </head>
  <!--
  BODY TAG OPTIONS:
  =================
  Apply one or more of the following classes to get the
  desired effect
  |---------------------------------------------------------|
  | SKINS         | skin-blue                               |
  |               | skin-black                              |
  |               | skin-purple                             |
  |               | skin-yellow                             |
  |               | skin-red                                |
  |               | skin-green                              |
  |---------------------------------------------------------|
  |LAYOUT OPTIONS | fixed                                   |
  |               | layout-boxed                            |
  |               | layout-top-nav                          |
  |               | sidebar-collapse                        |
  |               | sidebar-mini                            |
  |---------------------------------------------------------|
  -->
  <body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">

      <!-- Main Header -->
      <header class="main-header">

        <!-- Logo -->
        <a href="/main.php" class="logo">
          <!-- mini logo for sidebar mini 50x50 pixels -->
          <span class="logo-mini"><b>TL</b>og</span>
          <!-- logo for regular state and mobile devices -->
          <span class="logo-lg">
          	<img src="/images/TL_logo.png" width="35" height="35" border="0">
          	<b>Таргето</b>LOG
          </span>
        </a>

        <!-- Header Navbar -->
        <nav class="navbar navbar-static-top" role="navigation">
          <!-- Sidebar toggle button-->
          <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
          </a>
          <!-- Navbar Right Menu -->
          <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
              <!-- User Account Menu -->
              <li class="dropdown user user-menu">
                <!-- Menu Toggle Button -->
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                  <!-- The user image in the navbar-->
                  <img src="<?=$_SESSION['user']['phote']?>" class="user-image" alt="User Image">
                  <!-- hidden-xs hides the username on small devices so only the image appears. -->
                  <span class="hidden-xs"><?=$_SESSION['user']['name']?></span>
                </a>
                <ul class="dropdown-menu">
                  <!-- The user image in the menu -->
                  <li class="user-header">
                    <img src="<?=$_SESSION['user']['phote_big']?>" class="img-circle" alt="User Image">
                    <p>
                      <?=$_SESSION['user']['name']?>
                      <small>Лицензия тестировщика</small>
                    </p>
                  </li>
                  <!-- Menu Body -->
                  <!--<li class="user-body">
                    <div class="col-xs-4 text-center">
                      <a href="#">Followers</a>
                    </div>
                    <div class="col-xs-4 text-center">
                      <a href="#">Sales</a>
                    </div>
                    <div class="col-xs-4 text-center">
                      <a href="#">Friends</a>
                    </div>
                  </li>!-->
                  <!-- Menu Footer-->
                  <li class="user-footer">
                    <!--<div class="pull-left">
                      <a href="#" class="btn btn-default btn-flat">Profile</a>
                    </div>!-->
                    <div class="pull-right">
                      <a href="/logout.php" class="btn btn-default btn-flat">Выйти</a>
                    </div>
                  </li>
                </ul>
              </li>
            </ul>
          </div>
        </nav>
      </header>
      <!-- Left side column. contains the logo and sidebar -->
      <aside class="main-sidebar">

        <!-- sidebar: style can be found in sidebar.less -->
        <section class="sidebar">

          <!-- Sidebar user panel (optional) -->
          <div class="user-panel">
            <div class="pull-left image">
              <img src="<?=$_SESSION['user']['phote']?>" class="img-circle" alt="User Image">
            </div>
            <div class="pull-left info">
              <p><?=$_SESSION['user']['name']?></p>
              <!-- Status -->
              <a href="javascript://"><i class="fa fa-circle text-success"></i> активная лицензия</a>
            </div>
          </div>

          <!-- Sidebar Menu -->
          <ul class="sidebar-menu">
            <li class="header">Инструменты</li>
            <!-- Optionally, you can add icons to the links -->
            <li <?if (in_array($_GET['m'], array('audience','c_group')) || empty($_GET['m'])){?>class="active"<?}?> class="treeview">
              <a href="#"><i class="fa fa-users"></i> <span>Целевая аудитория</span> <i class="fa fa-angle-left pull-right"></i></a>
              <ul class="treeview-menu">
                <?if ($_GET['m'] == 'c_group'){?><li class="active"><a href="/main.php?m=c_group">Поиск похожих групп</a></li><?}else{?><li><a href="/main.php?m=c_group">Поиск похожих групп</a></li><?}?>
                <?if ($_GET['m'] == 'audience'){?><li class="active"><a href="/main.php?m=c_group">Поиск целевой аудитории</a></li><?}else{?><li><a href="/main.php?m=audience">Поиск целевой аудитории</a></li><?}?>
              </ul>
            </li>
            <li <?if (in_array($_GET['m'], array('aktivnost', 'best_p', 'active_in_gr', 'post_auditoriya', 'get_obsujdeniya', 'analytics', 'adm_contact', 'popular_post'))){?>class="active"<?}?> class="treeview">
              <a href="#"><i class="fa fa-bullhorn"></i> <span>Сообщества и группы</span> <i class="fa fa-angle-left pull-right"></i></a>
              <ul class="treeview-menu">
                <?if ($_GET['m'] == 'aktivnost'){?><li class="active"><a href="/main.php?m=aktivnost">Активности</a></li><?}else{?><li><a href="/main.php?m=aktivnost">Активности</a></li><?}?>
                <?if ($_GET['m'] == 'best_p'){?><li class="active"><a href="/main.php?m=best_p">Популярные люди</a></li><?}else{?><li><a href="/main.php?m=best_p">Популярные люди</a></li><?}?>
                <?if ($_GET['m'] == 'active_in_gr'){?><li class="active"><a href="/main.php?m=active_in_gr">Стена</a></li><?}else{?><li><a href="/main.php?m=active_in_gr">Стена</a></li><?}?>
                <?if ($_GET['m'] == 'post_auditoriya'){?><li class="active"><a href="/main.php?m=post_auditoriya">Аудитория постов</a></li><?}else{?><li><a href="/main.php?m=post_auditoriya">Аудитория постов</a></li><?}?>
                <?if ($_GET['m'] == 'get_obsujdeniya'){?><li class="active"><a href="/main.php?m=get_obsujdeniya">Обсуждения</a></li><?}else{?><li><a href="/main.php?m=get_obsujdeniya">Обсуждения</a></li><?}?>
                <?if ($_GET['m'] == 'analytics'){?><li class="active"><a href="/main.php?m=analytics">Аналитика</a></li><?}else{?><li><a href="/main.php?m=analytics">Аналитика</a></li><?}?>
                <?if ($_GET['m'] == 'popular_post'){?><li class="active"><a href="/main.php?m=popular_post">Популярные посты</a></li><?}else{?><li><a href="/main.php?m=popular_post">Популярные посты</a></li><?}?>
                <?if ($_GET['m'] == 'adm_contact'){?><li class="active"><a href="/main.php?m=adm_contact">Контакты администрации</a></li><?}else{?><li><a href="/main.php?m=adm_contact">Контакты администрации</a></li><?}?>
              </ul>
            </li>
            <li <?if (in_array($_GET['m'], array('active_in_user', 'interes', 'all_social_net', 'get_frend_follower', 'user_pary', 'beasdey', 'get_comment'))){?>class="active"<?}?> class="treeview">
              <a href="#"><i class="fa fa-user"></i> <span>Пользователи</span> <i class="fa fa-angle-left pull-right"></i></a>
              <ul class="treeview-menu">
                <?if ($_GET['m'] == 'active_in_user'){?><li class="active"><a href="/main.php?m=active_in_user">Активности на странице</a></li><?}else{?><li><a href="/main.php?m=active_in_user">Активности на странице</a></li><?}?>
                <?if ($_GET['m'] == 'interes'){?><li class="active"><a href="/main.php?m=interes">Интересы</a></li><?}else{?><li><a href="/main.php?m=interes">Интересы</a></li><?}?>
                <?if ($_GET['m'] == 'all_social_net'){?><li class="active"><a href="/main.php?m=all_social_net">Другие соц. сети</a></li><?}else{?><li><a href="/main.php?m=all_social_net">Другие соц. сети</a></li><?}?>
                <?if ($_GET['m'] == 'get_frend_follower'){?><li class="active"><a href="/main.php?m=get_frend_follower">Друзья и подписчики</a></li><?}else{?><li><a href="/main.php?m=get_frend_follower">Друзья и подписчики</a></li><?}?>
                <?if ($_GET['m'] == 'user_pary'){?><li class="active"><a href="/main.php?m=user_pary">Пары</a></li><?}else{?><li><a href="/main.php?m=user_pary">Пары</a></li><?}?>
                <?if ($_GET['m'] == 'beasdey'){?><li class="active"><a href="/main.php?m=beasdey">Дни рождения</a></li><?}else{?><li><a href="/main.php?m=beasdey">Дни рождения</a></li><?}?>
                <?if ($_GET['m'] == 'get_comment'){?><li class="active"><a href="/main.php?m=get_comment">Комментарии</a></li><?}else{?><li><a href="/main.php?m=get_comment">Комментарии</a></li><?}?>
              </ul>
            </li>
            <li <?if (in_array($_GET['m'], array('convert_id', 'base_do', 'reports', 's_report'))){?>class="active"<?}?> class="treeview">
              <a href="#"><i class="fa fa-gears"></i> <span>Инструменты</span> <i class="fa fa-angle-left pull-right"></i></a>
              <ul class="treeview-menu">
                <?if ($_GET['m'] == 'reports' || $_GET['m'] == 's_report'){?><li class="active"><a href="/main.php?m=reports">Отчеты</a></li><?}else{?><li><a href="/main.php?m=reports">Отчеты</a></li><?}?>
                <?if ($_GET['m'] == 'convert_id'){?><li class="active"><a href="/main.php?m=convert_id">Преобразование ID</a></li><?}else{?><li><a href="/main.php?m=convert_id">Преобразование ID</a></li><?}?>
                <?if ($_GET['m'] == 'base_do'){?><li class="active"><a href="/main.php?m=base_do">Работа с базами</a></li><?}else{?><li><a href="/main.php?m=base_do">Работа с базами</a></li><?}?>
              </ul>
            </li>
          </ul><!-- /.sidebar-menu -->
        </section>
        <!-- /.sidebar -->
      </aside>

      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
			<?if ($_GET['m'] == 'c_group'){?>
				Поиск похожих групп
			<?}elseif ($_GET['m'] == 'audience'){?>
				Поиск целевой аудитории
			<?}elseif ($_GET['m'] == 'aktivnost'){?>
				Активности
			<?}elseif ($_GET['m'] == 'best_p'){?>
				Популярные люди
			<?}elseif ($_GET['m'] == 'active_in_gr'){?>
				Стена
			<?}elseif ($_GET['m'] == 'post_auditoriya'){?>
				Аудитория постов
			<?}elseif ($_GET['m'] == 'get_obsujdeniya'){?>
				Обсуждения
			<?}elseif ($_GET['m'] == 'analytics'){?>
				Аналитика
			<?}elseif ($_GET['m'] == 'popular_post'){?>
				Популярные посты
			<?}elseif ($_GET['m'] == 'adm_contact'){?>
				Контакты администрации
			<?}elseif ($_GET['m'] == 'active_in_user'){?>
				Активности на странице
			<?}elseif ($_GET['m'] == 'interes'){?>
				Интересы
			<?}elseif ($_GET['m'] == 'all_social_net'){?>
				Другие соц. сети
			<?}elseif ($_GET['m'] == 'get_frend_follower'){?>
				Друзья и подписчики
			<?}elseif ($_GET['m'] == 'user_pary'){?>
				Пары
			<?}elseif ($_GET['m'] == 'beasdey'){?>
				Дни рождения
			<?}elseif ($_GET['m'] == 'convert_id'){?>
				Преобразование ID
			<?}elseif ($_GET['m'] == 'base_do'){?>
				Работа с базами
			<?}elseif ($_GET['m'] == 'reports' || $_GET['m'] == 's_report'){?>
				Отчеты
			<?}elseif ($_GET['m'] == 'get_comment'){?>
				Комментарии
			<?}else{?>
				Новости и обновления
            <?}?>
            <!--<small>Optional description</small>!-->
          </h1>
          <ol class="breadcrumb">
            <li><a href="/main.php"><i class="fa fa-dashboard"></i> ТаргетоLOG</a></li>
			<?if ($_GET['m'] == 'c_group'){?>
				<li class="active">поиск похожих групп</li>
			<?}elseif ($_GET['m'] == 'audience'){?>
				<li class="active">поиск целевой аудитории</li>
			<?}elseif ($_GET['m'] == 'aktivnost'){?>
				<li class="active">активности</li>
			<?}elseif ($_GET['m'] == 'best_p'){?>
				<li class="active">популярные люди</li>
			<?}elseif ($_GET['m'] == 'active_in_gr'){?>
				<li class="active">стена</li>
			<?}elseif ($_GET['m'] == 'post_auditoriya'){?>
				<li class="active">аудитория постов</li>
			<?}elseif ($_GET['m'] == 'get_obsujdeniya'){?>
				<li class="active">обсуждения</li>
			<?}elseif ($_GET['m'] == 'analytics'){?>
				<li class="active">аналитика</li>
			<?}elseif ($_GET['m'] == 'popular_post'){?>
				<li class="active">популярные посты</li>
			<?}elseif ($_GET['m'] == 'adm_contact'){?>
				<li class="active">контакты администрации</li>
			<?}elseif ($_GET['m'] == 'active_in_user'){?>
				<li class="active">активности на странице</li>
			<?}elseif ($_GET['m'] == 'interes'){?>
				<li class="active">интересы</li>
			<?}elseif ($_GET['m'] == 'all_social_net'){?>
				<li class="active">другие соц. сети</li>
			<?}elseif ($_GET['m'] == 'get_frend_follower'){?>
				<li class="active">друзья и подписчики</li>
			<?}elseif ($_GET['m'] == 'user_pary'){?>
				<li class="active">пары</li>
			<?}elseif ($_GET['m'] == 'beasdey'){?>
				<li class="active">дни рождения</li>
			<?}elseif ($_GET['m'] == 'convert_id'){?>
				<li class="active">преобразование ID</li>
			<?}elseif ($_GET['m'] == 'base_do'){?>
				<li class="active">работа с базами</li>
			<?}elseif ($_GET['m'] == 'reports' || $_GET['m'] == 's_report'){?>
				<li class="active">отчеты</li>
			<?}elseif ($_GET['m'] == 'get_comment'){?>
				<li class="active">комментарии</li>
			<?}else{?>
				<li class="active">новости и обновления</li>
            <?}?>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">

<?
if ($_GET['m'] == 'analytics'){
	require_once($_SERVER['DOCUMENT_ROOT'].'/forms/f_analytics.php');
} else if ($_GET['m'] == 'interes'){
	require_once($_SERVER['DOCUMENT_ROOT'].'/forms/f_interes.php');
} else if ($_GET['m'] == 'c_group'){
	require_once($_SERVER['DOCUMENT_ROOT'].'/forms/f_c_group.php');
} else if ($_GET['m'] == 'audience'){
	require_once($_SERVER['DOCUMENT_ROOT'].'/forms/f_audience.php');
} else if ($_GET['m'] == 'best_p'){
	require_once($_SERVER['DOCUMENT_ROOT'].'/forms/f_best_p.php');
} else if ($_GET['m'] == 'aktivnost'){
	require_once($_SERVER['DOCUMENT_ROOT'].'/forms/f_aktivnost.php');
} else if ($_GET['m'] == 'post_auditoriya'){
	require_once($_SERVER['DOCUMENT_ROOT'].'/forms/f_post_auditoriya.php');
} else if ($_GET['m'] == 'popular_post'){
	require_once($_SERVER['DOCUMENT_ROOT'].'/forms/f_popular_post.php');
} else if ($_GET['m'] == 'all_social_net'){
	require_once($_SERVER['DOCUMENT_ROOT'].'/forms/f_all_social_net.php');
} else if ($_GET['m'] == 'adm_contact'){
	require_once($_SERVER['DOCUMENT_ROOT'].'/forms/f_adm_contact.php');
} else if ($_GET['m'] == 'active_in_gr'){
	require_once($_SERVER['DOCUMENT_ROOT'].'/forms/f_active_in_gr.php');
} else if ($_GET['m'] == 'get_frend_follower'){
	require_once($_SERVER['DOCUMENT_ROOT'].'/forms/f_get_frend_follower.php');
} else if ($_GET['m'] == 'active_in_user'){
	require_once($_SERVER['DOCUMENT_ROOT'].'/forms/f_active_in_user.php');
} else if ($_GET['m'] == 'user_pary'){
	require_once($_SERVER['DOCUMENT_ROOT'].'/forms/f_user_pary.php');
} else if ($_GET['m'] == 'base_do'){
	require_once($_SERVER['DOCUMENT_ROOT'].'/forms/f_base_do.php');
} else if ($_GET['m'] == 'beasdey'){
	require_once($_SERVER['DOCUMENT_ROOT'].'/forms/f_beasdey.php');
} else if ($_GET['m'] == 'convert_id'){
	require_once($_SERVER['DOCUMENT_ROOT'].'/forms/f_convert_id.php');
} else if ($_GET['m'] == 'get_obsujdeniya'){
	require_once($_SERVER['DOCUMENT_ROOT'].'/forms/f_get_obsujdeniya.php');
} else if ($_GET['m'] == 'reports'){
	require_once($_SERVER['DOCUMENT_ROOT'].'/forms/f_reports.php');
} else if ($_GET['m'] == 's_report'){
	require_once($_SERVER['DOCUMENT_ROOT'].'/reports.php');
} else if ($_GET['m'] == 'get_comment'){
	require_once($_SERVER['DOCUMENT_ROOT'].'/forms/f_get_comment.php');
} else
	require_once($_SERVER['DOCUMENT_ROOT'].'/news.php');
?>

		<div id="data_result"></div>

          <div id="progress" class="example-modal">
            <div class="modal">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h4 class="modal-title">Выполняется анализ...</h4>
                  </div>
                  <div align="center" class="modal-body">
                    <img src="/images/ajax-loader.gif" width="32" height="32" border="0">
                    <div id="p">0%</div>
                  </div>
                  <div class="modal-footer">
                  </div>
                </div><!-- /.modal-content -->
              </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->
          </div><!-- /.example-modal -->

        </section><!-- /.content -->
      </div><!-- /.content-wrapper -->
      <!-- Main Footer -->
      <footer class="main-footer">
        <!-- To the right -->
        <div class="pull-right hidden-xs">
          автор: <a target="_blank" href="https://www.facebook.com/lenid.chernyadyev">Чернядьев Л.В.</a>
        </div>
        <!-- Default to the left -->
        <strong>ТаргетоLOG &copy; 2015</strong> поддержка: <a href="mailto:admin@w7.ru">admin@w7.ru</a>, группа: <a target="_blank" href="https://vk.com/targetolog_com">targetolog_com</a>
      </footer>

      <!-- Add the sidebar's background. This div must be placed
           immediately after the control sidebar -->
      <div class="control-sidebar-bg"></div>
    </div><!-- ./wrapper -->

    <!-- REQUIRED JS SCRIPTS -->

    <!-- Bootstrap 3.3.5 -->
    <script src="../../bootstrap/js/bootstrap.min.js"></script>
    <!-- DataTables -->
    <script src="../../plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="../../plugins/datatables/dataTables.bootstrap.min.js"></script>
	<script type="text/javascript" src="/js/dataTables/tabletools/js/dataTables.tableTools.min.js"></script>
    <!-- FastClick -->
    <script src="../../plugins/fastclick/fastclick.min.js"></script>
    <!-- AdminLTE App -->
    <script src="../../dist/js/app.min.js"></script>
	<script src="/js/jquery-ui-1.10.4.custom.min.js"></script>
	<script src="/js/jquery.ui.datepicker-ru.min.js"></script>
    <script src="/js/chart/Chart.min.js"></script>
	<script>
	function popup(data) {
	    $("body").append('<form id="exportform" action="export.php" method="post" target="_blank"><input type="hidden" id="exportdata" name="exportdata" /></form>');
	    $("#exportdata").val(data);
	    $("#exportform").submit().remove();
	    return true;
	}
	function getRandomArbitary(min, max){
		return Math.random() * (max - min) + min;
	}
	</script>
	<!-- Yandex.Metrika counter --><script type="text/javascript"> (function (d, w, c) { (w[c] = w[c] || []).push(function() { try { w.yaCounter34502865 = new Ya.Metrika({ id:34502865, clickmap:true, trackLinks:true, accurateTrackBounce:true, webvisor:true, trackHash:true }); } catch(e) { } }); var n = d.getElementsByTagName("script")[0], s = d.createElement("script"), f = function () { n.parentNode.insertBefore(s, n); }; s.type = "text/javascript"; s.async = true; s.src = "https://mc.yandex.ru/metrika/watch.js"; if (w.opera == "[object Opera]") { d.addEventListener("DOMContentLoaded", f, false); } else { f(); } })(document, window, "yandex_metrika_callbacks");</script><noscript><div><img src="https://mc.yandex.ru/watch/34502865" style="position:absolute; left:-9999px;" alt="" /></div></noscript><!-- /Yandex.Metrika counter -->
</html>
