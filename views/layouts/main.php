<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use yii\bootstrap\Nav;
use app\assets\AppAsset;
use app\models\Radicados;
use yii\widgets\ActiveForm;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <?php $this->registerLinkTag(['rel' => 'icon', 'type' => 'image/png', 'href' => '/web/img/favicon.ico']); ?>
</head>
<body class="hold-transition skin-black-light sidebar-mini">
  <!--Pag init -->
  <?php $this->beginBody() ?>
    <div class="wrapper">
      <header class="main-header">
      <!-- Logo -->
        <a href="index.php" class="logo">
        <!-- mini logo for sidebar mini 50x50 pixels -->
        <!--<span class="logo-mini"><b>P.</b>J</span> -->
          <img class="logo-mini" src="img/escudovallenew.png" alt="Logo Gobernación"  />
        <!-- logo for regular state and mobile devices -->
        <!-- <span class="logo-lg"><b>Gobernación</b>DelValle</span> -->
          <img class="logo-lg" src="img/logoGoberNew.png" alt="Logo Gobernación"  />
        </a>
      <!-- Header Navbar: style can be found in header.less -->

      <nav class="navbar navbar-static-top">
          <!-- Sidebar toggle button-->
          <a id='miboton' href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <span class="sr-only">Toggle navigation</span>
          </a>

          <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
              <!--sidebar data-toogle button task Radicados -->

              <!-- User Account: style can be found in dropdown.less -->
              <?php
              try {
                $idRol = Yii::$app->user->identity->id_rol;
                switch ($idRol) {
                  // SuperUser Index Menu Up
                  case 1:
                    echo Nav::widget([
                        'options' => ['class' => 'navbar-nav navbar-right'],
                        'encodeLabels' => false,
                        'items' => [
                            //Fin New
                            ['label' => 'Acerca de', 'url' => ['/site/about']],
                            ['label' => 'Contáctenos', 'url' => ['/site/contact']],
                            Yii::$app->user->isGuest ? (
                                ['label' => 'Iniciar sesión', 'url' => ['/site/login']]
                            ) : (
                                '<li>'
                                . Html::beginForm(['/site/logout'], 'post')
                                . Html::submitButton(
                                    'Cerrar sesión (' . Yii::$app->user->identity->nombre_funcionario . ')',
                                    ['class' => 'btn btn-default logout']
                                )
                                . Html::endForm()
                                . '</li>'
                            ),
                            ['label'=>'   '],
                        ],
                    ]);

                    break;
                    // Use menu Up Func
                    case 2:
                    $session = Yii::$app->session;
                    $radicados = $session->get('radicados');
                    $nradicados = count($radicados);
                  //  $nradicados = $nradicados1;
            /*        try {
                      for($i = 0 ; $i <$nradicados1;$i++){
                        $tmp = Radicados::find()->where(['and', ['id_radicado' =>$radicados[$i] ],[ 'or',['estado' => 3],['estado' => 4]]])->one();
                        if(isset($tmp)){
                          unset($radicados[$i]);
                          $nradicados = $nradicados - 1;
                        }

                      }
                      $session = Yii::$app->session;
                      $session->set('radicados',$radicados);
                    } catch (Exception $e) {
                      //identificar la linea del error
                    }
*/


                    ?>

                        <?php
                        echo "

                        <li class='dropdown tasks-menu'>
                          <a href='#' class='dropdown-toggle' data-toggle='dropdown'>
                            <i class='fa fa-bell-o'></i>
                            <span class='label label-info'>$nradicados</span>
                          </a>
                          <ul class='dropdown-menu'>
                        <li class='header'>Usted tiene $nradicados Radicados en tramite</li>"
                        ?>
                        <li>
                          <!-- inner menu: contains the actual data -->
                          <ul class="menu">
                        <?php

                        for($i = 0; $i <$nradicados;$i++ ){
                          if($i == 0){
                            $a = "a";
                            $b = "b";
                          }else{
                            $a = $i;
                            $b = $i * -1;
                          }
                          if(isset($radicados[$i])){
                            echo"
                            <li><!-- Task item -->
                              <a href='?r=radicados%2Fview&id=$radicados[$i]'>
                                <h3>
                                  <i class='fa  fa-circle-o-notch text-aqua'></i> Radicado No. <strong> $radicados[$i]</strong>
                                    <small class='pull-right'>
                                        <label>
                                          <input type='radio' id ='$a' value='$radicados[$i]' name = 'radicado-checkbox$i' onclick ='desactivar(this)' >
                                            Finalizado
                                        </label>
                                        <label>
                                          <input type='radio' id ='$b' value='$radicados[$i]' name = 'radicado-checkbox$i' onclick ='desactivar(this)' >
                                            Devolución
                                        </label>
                                    </small>
                                </h3>
                              </a>
                            </li>";
                            }
                            }
                          ?>
                          </ul>
                        </li>
                        <li class="footer">
                          <a href="?r=radicados">Ir a Radicados.</a>
                        </li>
                      </ul>
                    </li>

                    <?php
                    $session = Yii::$app->session;
                    $id = $session->get('id');

                    if(isset($id) && $id != 'x'){
                      echo Nav::widget([
                          'options' => ['class' => 'navbar-nav navbar-right'],
                          'items' => [
                              ['label' => 'Acerca de', 'url' => ['/site/about']],
                              ['label' => 'Contáctenos', 'url' => ['/site/contact']],
                              Yii::$app->user->isGuest ? (
                                  ['label' => 'Iniciar sesión', 'url' => ['/site/login']]
                              ) : (
                                  '<li>'
                                  . Html::beginForm(['/site/logout'], 'post')
                                  . Html::submitButton(
                                      'Cerrar sesión (' . Yii::$app->user->identity->nombre_funcionario . ')',
                                      ['class' => 'btn btn-default logout',
                                      'data' => [
                                          //'confirm' => "se encuentra realizando el radicado N° $id ¿Esta seguro que desea salir?",
                                          'method' => 'post',
                                      ],
                                      ]
                                  )
                                  . Html::endForm()
                                  . '</li>'
                              ),
                              ['label'=>'   '],
                          ],
                      ]);
                    }else{
                        echo Nav::widget([
                            'options' => ['class' => 'navbar-nav navbar-right'],
                            'items' => [
                                ['label' => 'Acerca de', 'url' => ['/site/about']],
                                ['label' => 'Contáctenos', 'url' => ['/site/contact']],
                                Yii::$app->user->isGuest ? (
                                    ['label' => 'Iniciar sesión', 'url' => ['/site/login']]
                                ) : (
                                    '<li>'
                                    . Html::beginForm(['/site/logout'], 'post')
                                    . Html::submitButton(
                                        'Cerrar sesión (' . Yii::$app->user->identity->nombre_funcionario . ')',
                                        ['class' => 'btn btn-default logout']
                                    )
                                    . Html::endForm()
                                    . '</li>'
                                ),
                                ['label'=>'   '],
                            ],
                        ]);
                      }
                      break;
                      // Radic User Menu Up
                      case 3:
                      echo Nav::widget([
                          'options' => ['class' => 'navbar-nav navbar-right'],
                          'items' => [
                              ['label' => 'Acerca de', 'url' => ['/site/about']],
                              ['label' => 'Contáctenos', 'url' => ['/site/contact']],
                              Yii::$app->user->isGuest ? (
                                  ['label' => 'Iniciar sesión', 'url' => ['/site/login']]
                              ) : (
                                  '<li>'
                                  . Html::beginForm(['/site/logout'], 'post')
                                  . Html::submitButton(
                                      'Cerrar sesión (' . Yii::$app->user->identity->nombre_funcionario . ')',
                                      ['class' => 'btn btn-default logout']
                                  )
                                  . Html::endForm()
                                  . '</li>'
                              ),
                              ['label'=>'   '],
                          ],
                      ]);
                      break;
                      // User Rep menu Up
                      case 4:
                      echo Nav::widget([
                          'options' => ['class' => 'navbar-nav navbar-right'],
                          'items' => [
                              ['label' => 'Acerca de', 'url' => ['/site/about']],
                              ['label' => 'Contáctenos', 'url' => ['/site/contact']],
                              Yii::$app->user->isGuest ? (
                                  ['label' => 'Iniciar sesión', 'url' => ['/site/login']]
                              ) : (
                                  '<li>'
                                  . Html::beginForm(['/site/logout'], 'post')
                                  . Html::submitButton(
                                      'Cerrar sesión (' . Yii::$app->user->identity->nombre_funcionario . ')',
                                      ['class' => 'btn btn-default logout']
                                  )
                                  . Html::endForm()
                                  . '</li>'
                              ),
                              ['label'=>'   '],
                          ],
                      ]);

                      break;
                      case ($idRol > 2 && $idRol < 1):
                      echo Nav::widget([
                          'options' => ['class' => 'navbar-nav navbar-right'],
                          'items' => [
                              ['label' => 'Acerca de', 'url' => ['/site/about']],
                              ['label' => 'Contáctenos', 'url' => ['/site/contact']],
                              Yii::$app->user->isGuest ? (
                                  ['label' => 'Iniciar sesión', 'url' => ['/site/login']]
                              ) : (
                                  '<li>'
                                  . Html::beginForm(['/site/logout'], 'post')
                                  . Html::submitButton(
                                      'Cerrar sesión (' . Yii::$app->user->identity->nombre_funcionario . ')',
                                      ['class' => 'btn btn-default logout']
                                  )
                                  . Html::endForm()
                                  . '</li>'
                              ),
                              ['label'=>'   '],
                          ],
                      ]);
                break;
              }
            } catch (Exception $e) {
              echo Nav::widget([
                  'options' => ['class' => 'navbar-nav navbar-right'],
                  'encodeLabels' => false,
                  'items' => [
                      //['label' => 'Inicio', 'url' => ['/site/index']],
                //New

                //Fin New
                      ['label' => 'Acerca de', 'url' => ['/site/about']],
                      ['label' => 'Contáctenos', 'url' => ['/site/contact']],
                      Yii::$app->user->isGuest ? (
                          ['label' => '<span class="glyphicon glyphicon-log-in"></span> Iniciar sesión', 'url' => ['/site/login']]
                      ) : (
                          '<li>'
                          . Html::beginForm(['/site/logout'], 'post')
                          . Html::submitButton(
                              'Cerrar sesión (' . Yii::$app->user->identity->nombre_funcionario . ')',
                              ['class' => 'btn btn-default logout']
                          )
                          . Html::endForm()
                          . '</li>'
                      ),['label'=>'   '],
                  ],
              ]);
            }
            ?>

            </ul>
          </div>
      </nav>
<!-- So much iimportant // aside axis-->
    </header>

     <?php
          try {
        $idRol = Yii::$app->user->identity->id_rol;

        switch ($idRol) {
          case 4:
          ?>
<aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
  <section class="sidebar">
      <!-- sidebar menu: : style can be found in sidebar.less -->
    <ul class="sidebar-menu" data-widget="tree">
      <li class="header">MENÚ DE NAVEGACIÓN</li>
       <!-- Entidades -->
       <li><a href="?r=radicados"><i class="fa fa-barcode"></i> <span>Radicados</span></a></li>
      <li class="header">VALORES</li>
        <li class="treeview">
          <a href="#">
            <i class="fa fa-credit-card"></i>
            <span>Valores</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="?r=valores"><i class="glyphicon glyphicon-barcode"></i> <span>Estampillas</span></a></li>
            <li><a href="?r=precios-tramites"><i class="fa fa-money"></i>Precios Tramites</a></li>
          </ul>
        </li>
      <li class="header">DOCUMENTACIÓN</li>
      <!--Doc-->
        <?php
        $url = Yii::$app->request->baseUrl . '/video/1.webm';
        echo "<li class='bg-green'><a href=".$url." style='background-color: rgb(50, 137, 255);'><i class='fa fa-book' style='color: rgb(255, 255, 255);'></i> <span style='color: rgb(255, 255, 255);'>Documentación</span></a></li>"
        ?>
    </ul>
  </section>
    <!-- /.sidebar -->
  </aside>
    <div class="content-wrapper">
      <section class="content-header">
        <div class="top-bar">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4">
                    </div>
                    <div class="col-xs-8 col-sm-3 col-md-3 col-lg-3 ">
                        <img src="img/escudocolombiano.png" alt="Escudo" width="25%" />
                    </div>
                    <div class="col-xs-4 col-md-4 col-lg-4">

                      <div class="text-right">
                          <a href="https://www.elvalleestaenvos.com/" class="logo">

                          <img src="img/valle.png" alt="Logo Gobernación" width="40%" class="text-center" />
                          </a>
                          <a href="https://www.facebook.com/GobValle/" title="Facebook" class="text-right btn btn-social-icon btn-facebook"><i class="fa fa-facebook"></i></a>
                          <a href="https://twitter.com/GobValle" title="Twitter" class="btn btn-social-icon btn-twitter"><i class="fa fa-twitter"></i></a>
                          <a href="https://www.youtube.com/user/VideosGobValle" title="Youtube" class="btn btn-social-icon btn-google"><i class="fa fa-youtube-square"></i></a>


                    </div>
                    </div>

                </div>
            </div><!--/.container-->
        </div><!--/.top-bar-->

        <div class="container">

          <?= Breadcrumbs::widget([
              'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
          ]) ?>
        </div>
        <section class="content">
          <?= $content ?>
        </section>
      </section>
   <!-- Main content -->
    </div>
<?php
          break;
          case 3:
          ?>
<aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
  <section class="sidebar">
    <!-- sidebar menu: : style can be found in sidebar.less -->
    <ul class="sidebar-menu" data-widget="tree">
      <li class="header">MENÚ DE NAVEGACIÓN</li>
       <!-- Entidades -->
       <li><a href="?r=radicados"><i class="fa fa-barcode"></i> <span>Radicados</span></a></li>
      <!--/. Entidades -->
      <!--Resoluciones-->

      <li class="header">VALORES</li>
       <li class="treeview">
        <a href="#">
          <i class="fa fa-credit-card"></i>
          <span>Valores</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu">
          <li><a href="?r=valores"><i class="glyphicon glyphicon-barcode"></i> <span>Estampillas</span></a></li>
          <li><a href="?r=precios-tramites"><i class="fa fa-money"></i>Precios Tramites</a></li>
        </ul>
      </li>
      <li class="header">DOCUMENTACIÓN</li>
      <!--Doc-->
        <?php
        $url = Yii::$app->request->baseUrl . '/video/1.webm';
        echo "<li class='bg-green'><a href=".$url." style='background-color: rgb(50, 137, 255);'><i class='fa fa-book' style='color: rgb(255, 255, 255);'></i> <span style='color: rgb(255, 255, 255);'>Documentación</span></a></li>"
        ?>
    </ul>
  </section>
    <!-- /.sidebar -->
</aside>
  <div class="content-wrapper">
    <section class="content-header">
      <div class="top-bar">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4">
                    </div>
                    <div class="col-xs-8 col-sm-3 col-md-3 col-lg-3 ">
                        <img src="img/escudocolombiano.png" alt="Escudo" width="25%" />
                    </div>
                    <div class="col-xs-4 col-md-4 col-lg-4">

                      <div class="text-right">
                          <a href="https://www.elvalleestaenvos.com/" class="logo">

                          <img src="img/valle.png" alt="Logo Gobernación" width="40%" class="text-center" />
                          </a>
                          <a href="https://www.facebook.com/GobValle/" title="Facebook" class="text-right btn btn-social-icon btn-facebook"><i class="fa fa-facebook"></i></a>
                          <a href="https://twitter.com/GobValle" title="Twitter" class="btn btn-social-icon btn-twitter"><i class="fa fa-twitter"></i></a>
                          <a href="https://www.youtube.com/user/VideosGobValle" title="Youtube" class="btn btn-social-icon btn-google"><i class="fa fa-youtube-square"></i></a>
                      </div>
                    </div>

                </div>
            </div><!--/.container-->
        </div><!--/.top-bar-->

      <div class="container">

        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
      </div>
        <section class="content">
          <?= $content ?>
        </section>
    </section>
 <!-- Main content -->
  </div>

<?php
          break;
          case 2:
    ?>
<aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
  <section class="sidebar">
  <!-- sidebar menu: : style can be found in sidebar.less -->
    <ul class="sidebar-menu" data-widget="tree">
      <li class="header">MENÚ DE NAVEGACIÓN</li>
     <!-- Entidades -->
      <li><a href="?r=radicados"><i class="fa fa-barcode"></i> <span>Radicados</span></a></li>
      <li><a href="?r=entidades"><i class="fa fa-bank"></i> <span>Entidades</span></a></li>
    <!--/. Entidades -->
    <!--Resoluciones-->

      <li class="header">CONFIGURACIÓN </li>
      <li class="treeview">
        <a href="#">
          <i class="fa fa fa-cogs"></i>
          <span>Cargos</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu">
          <li><a href="?r=cargos"><i class="fa fa-cog"></i>Cargos</a></li>
          <li><a href="?r=grupos-cargos"><i class="fa fa-cog"></i>Grupo de Cargos</a></li>
        </ul>
      </li>
      <li class="header">VALORES</li>
      <!--
      <li><a href="?r=valores"><i class="fa fa-credit-card"></i> <span>Valores</span></a></li> -->
      <!--    <li><a href="?r=certificados-existencia"><i class="fa fa-file-word-o"></i> <span>Certificados</span></a></li> -->
      <li class="treeview">
        <a href="#">
          <i class="fa fa-credit-card"></i>
          <span>Valores</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu">
          <li><a href="?r=valores"><i class="glyphicon glyphicon-barcode"></i> <span>Estampillas</span></a></li>
          <li><a href="?r=precios-tramites"><i class="fa fa-money"></i>Precios Tramites</a></li>
        </ul>
      </li>
      <!--/.Anexos-->
    <!--Informes
    <li><a href="#"><i class="fa fa-line-chart"></i> <span>Informes</span></a></li> -->
      <li class="header">DOCUMENTACIÓN</li>
    <!--Doc-->
        <?php
        $url = Yii::$app->request->baseUrl . '/video/1.webm';
        echo "<li class='bg-green'><a href=".$url." style='background-color: rgb(50, 137, 255);'><i class='fa fa-book' style='color: rgb(255, 255, 255);'></i> <span style='color: rgb(255, 255, 255);'>Documentación</span></a></li>"
        ?>
    </ul>
  </section>
  <!-- /.sidebar -->
</aside>
  <div class="content-wrapper">
    <section class="content-header">
    <div class="top-bar">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4">
                      </div>
                    <div class="col-xs-8 col-sm-3 col-md-3 col-lg-3 ">
                        <img src="img/escudocolombiano.png" alt="Escudo" width="25%" />
                    </div>
                    <div class="col-xs-4 col-md-4 col-lg-4">

                      <div class="text-right">
                          <a href="https://www.elvalleestaenvos.com/" class="logo">

                          <img src="img/valle.png" alt="Logo Gobernación" width="40%" class="text-center" />
                          </a>
                          <a href="https://www.facebook.com/GobValle/" title="Facebook" class="text-right btn btn-social-icon btn-facebook"><i class="fa fa-facebook"></i></a>
                          <a href="https://twitter.com/GobValle" title="Twitter" class="btn btn-social-icon btn-twitter"><i class="fa fa-twitter"></i></a>
                          <a href="https://www.youtube.com/user/VideosGobValle" title="Youtube" class="btn btn-social-icon btn-google"><i class="fa fa-youtube-square"></i></a>


                    </div>
                    </div>

                </div>
            </div><!--/.container-->
        </div><!--/.top-bar-->

      <div class="container">

        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
      </div>
      <section class="content">
        <?= $content ?>
      </section>
    </section>
  <!-- Main content -->
  </div>

  <?php

          break;
          case 1:
?>

<aside class="main-sidebar">
  <section class="sidebar">
    <ul class="sidebar-menu" data-widget="tree">
      <li class="header">MENÚ DE NAVEGACIÓN</li>
      <!-- Documentacion-->
      <li><a href="?r=user"><i class="fa fa-user"></i> <span>Usuarios</span></a></li>
      <li><a href="?r=historial"><i class="glyphicon glyphicon-list"></i> <span>Historial</span></a></li>
      <li><a href="?r=radicados"><i class="fa fa-barcode"></i> <span>Radicados</span></a></li>
      <li><a href="?r=entidades"><i class="fa fa-bank"></i> <span>Entidades</span></a></li>

    <!-- <li><a href="#"><i class="fa fa-file-text-o"></i> <span>Resoluciones</span></a></li> -->
    <!--/. Resoluciones-->
    <!-- Certificados -->

      <li class="treeview">
        <a href="#">
          <i class="fa fa fa-cogs"></i>
          <span>Configuraciones</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu">
          <li><a href="?r=cargos"><i class="fa fa-cog"></i>Cargos</a></li>
          <li><a href="?r=grupos-cargos"><i class="fa fa-cog"></i>Grupo de Cargos</a></li>
          <li><a href="?r=tipo-entidad"><i class="glyphicon glyphicon-list"></i> <span>Tipo de Entidades</span></a></li>
          <li><a href="?r=profesional%2Fview&id=1"><i class="fa fa-user"></i>Profesional</a></li>
          <li><a href="?r=motivo-certificado"><i class="fa fa-question"></i>Motivos Certificados</a></li>

        </ul>
      </li>
      <li class="header">VALORES</li>
      <li class="treeview">
        <a href="#">
          <i class="fa fa-credit-card"></i>
          <span>Valores</span>
          <span class="pull-right-container">
          <i class="fa fa-angle-left pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu">
          <li><a href="?r=valores"><i class="glyphicon glyphicon-barcode"></i> <span>Estampillas</span></a></li>
          <li><a href="?r=precios-tramites"><i class="fa fa-money"></i>Precios Tramites</a></li>
        </ul>
      </li>
      <li class="header">DOCUMENTACIÓN</li>
          <!-- Documentacion-->
        <?php
          $url = Yii::$app->request->baseUrl . '/video/1.webm';
          echo "<li class='bg-green'><a href=".$url." style='background-color: rgb(50, 137, 255);'><i class='fa fa-book' style='color: rgb(255, 255, 255);'></i> <span style='color: rgb(255, 255, 255);'>Documentación</span></a></li>"
        ?>
    </ul>
  </section>
</aside>
  <div class="content-wrapper">
  <!--Contenido del destino-->
    <section class="content-header">
      <div class="top-bar">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4">
                    </div>
                    <div class="col-xs-8 col-sm-3 col-md-3 col-lg-3 ">
                        <img src="img/escudocolombiano.png" alt="Escudo" width="25%" />
                    </div>
                    <div class="col-xs-4 col-md-4 col-lg-4">

                      <div class="text-right">
                          <a href="https://www.elvalleestaenvos.com/" class="logo">

                          <img src="img/valle.png" alt="Logo Gobernación" width="40%" class="text-center" />
                          </a>
                          <a href="https://www.facebook.com/GobValle/" title="Facebook" class="text-right btn btn-social-icon btn-facebook"><i class="fa fa-facebook"></i></a>
                          <a href="https://twitter.com/GobValle" title="Twitter" class="btn btn-social-icon btn-twitter"><i class="fa fa-twitter"></i></a>
                          <a href="https://www.youtube.com/user/VideosGobValle" title="Youtube" class="btn btn-social-icon btn-google"><i class="fa fa-youtube-square"></i></a>


                    </div>
                    </div>

                </div>
            </div><!--/.container-->
        </div><!--/.top-bar-->

      <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
      </div>
    </section>

    <!-- Main content -->
    <section class="content">
      <?= $content ?>
    </section>
    </div>
<?php
  break;
            }//fin Try
          }//fin sswitch
        catch (Exception $e) {
?>
           <aside class="main-sidebar">

        <section class="sidebar">

<ul class="sidebar-menu" data-widget="tree">
  <li class="header">MENÚ DE NAVEGACIÓN</li>
       <!-- Documentacion-->
       <li><a href="?r=entidades"><i class="fa fa-bank"></i> <span>Entidades</span></a></li>
       <?php
        $url = Yii::$app->request->baseUrl . '/video/1.webm';
        echo "<li class='bg-green'><a href=".$url." style='background-color: rgb(50, 137, 255);'><i class='fa fa-book' style='color: rgb(255, 255, 255);'></i> <span style='color: rgb(255, 255, 255);'>Documentación</span></a></li>"
        ?>

</ul>
        </section>
        </aside>
        <div class="content-wrapper">
          <!--Contenido del destino-->
            <section class="content-header">

                    <div class="top-bar">
                      <div class="container">
                          <div class="row">
                              <div class="col-lg-4">
                              </div>
                              <div class="col-xs-8 col-sm-3 col-md-3 col-lg-3 ">
                                  <img src="img/escudocolombiano.png" alt="Escudo" width="25%" />
                              </div>
                              <div class="col-xs-4 col-md-4 col-lg-4">
                                  <div class="text-right">
                                      <a href="https://www.elvalleestaenvos.com/" class="logo">

                                      <img src="img/valle.png" alt="Logo Gobernación" width="40%" class="text-center" />
                                      </a>
                                      <a href="https://www.facebook.com/GobValle/" title="Facebook" class="text-right btn btn-social-icon btn-facebook"><i class="fa fa-facebook"></i></a>
                                      <a href="https://twitter.com/GobValle" title="Twitter" class="btn btn-social-icon btn-twitter"><i class="fa fa-twitter"></i></a>
                                      <a href="https://www.youtube.com/user/VideosGobValle" title="Youtube" class="btn btn-social-icon btn-google"><i class="fa fa-youtube-square"></i></a>
                                  </div>
                              </div>
                          </div>
                      </div><!--/.container-->
                    </div><!--/.top-bar-->
                    <div class="container">
                    <?= Breadcrumbs::widget([
                        'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                    ]) ?>
                    </div>
            </section>

    <!-- Main content -->

              <section class="content">
                <?= $content ?>
              </section>

        </div>

<?php
    }
        ?>
    <!-- End Content Header (Page header) -->
</div>
<footer class="main-footer">

    <div class="pull-right hidden-xs">
      <!--<b>Version</b> 2.1.0-->
      <br>
      <img class="logo-lg" src="img/logoGoberNew.png" alt="Logo Gobernación" />
    </div>
     <!-- <p class="pull-left">&copy; Personeria juridica <?= date('Y') ?></p> -->

    <div class="container">

      <div class=" col-sm-12 col-md-8">
        <div class="pull-right">
            <div class="contenido1">
               <p style="line-height: 15px"><strong>Gobernación del Valle del Cauca, Santiago de Cali - Colombia</strong><br>
                Dirección: Carrera 6 entre calles 9 y 10 Edificio Palacio de San Francisco <br>Codigo Postal: 760045<br>
                Conmutador: (57-2) 620 00 00 - 886 00 00 - Fax: 886 0150<br> Línea Gratuita: 01-8000972033<br>
                Correo: Contactenos@valledelcauca.gov.co</p>
            </div>
        </div>
      </div>
    </div>
</footer>
  <?php $this->endBody() ?>
</body>
</html>
  <?php $this->endPage() ?>

  <script>
   document.getElementById("miboton").click();
  function desactivar(element){
      //$(element).attr('disabled', true);
    //  alert($(element).val());  // asi se obtiene el valor
    //  alert(element.id);     //  asi se obtiene el id
      // valor es la id del radicado y el id es a/b 1 o -1, para identificarlos cual se presiona
      if(element.id == "a" || element.id > 0){
          $.ajax({
            //http://localhost:8080/index.php?r=radicados%2Fview&id=8
            url: "?r=radicados%2Ffinalizado",
            dataType:"html",
            data : {
                  id:$(element).val(),
                },
            type: "post",
            success: function(data){
              var x = element.getAttribute('id');
              if(x == "a" || x == "b"){
                document.getElementById("a").disabled = true;
                document.getElementById("b").disabled = true;
              }else{
                document.getElementById(x).disabled = true;
                x = x * -1;
                document.getElementById(x).disabled = true;
              }
            },
            error: function (request, status, error) {
              alert("No se pudo realizar la operación error '"+request.responseText+"' Comuniquese con un administrador");
              }
          });
      }else{

        $.ajax({
          //http://localhost:8080/index.php?r=radicados%2Fview&id=8 http://localhost:8080/index.php?r=radicados%2Findex
          url: "?r=radicados%2Frechazado",
          dataType:"html",
          data : {
                id:$(element).val(),
              },
          type: "post",
          success: function(data){
            var x = element.getAttribute('id');
            if(x == "a" || x == "b"){
              document.getElementById("a").disabled = true;
              document.getElementById("b").disabled = true;
            }else{
              document.getElementById(x).disabled = true;
              x = x * -1;
              document.getElementById(x).disabled = true;
            }
          },
          error: function (request, status, error) {
            alert("No se pudo realizar la operación error '"+request.responseText+"' Comuniquese con un administrador");
            }
        });

      }

  }


  </script>
