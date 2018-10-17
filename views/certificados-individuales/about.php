<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use app\models\Dignatarios;
use app\models\Entidades;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
$entidad = Entidades::findOne($model[0]["id_entidad"]);
$this->title = 'Descargue aqui Certificados individuales';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-about">
    <h1><?= Html::encode($this->title) ?></h1>

    <h2>Seleccione un dignatario de la entidad</h2>
    <br>
    <?php
    echo $entidad['nombre_entidad'];
       //print_r($model);

       for ($i = 0 ; $i < sizeof($model);$i++) {
        // print($model[$i]["nombre_dignatario"]);
        echo "
        <br>
          <div class='col-sm-12'>
            <div class='col-sm-6'>
            <a href='?r=certificados-individuales%2Fview&id=".$model[$i]["id_entidad"]."&dig=".$model[$i]["id_dignatario"]."'
              <div class='info-box'>
                <span class='info-box-icon bg-aqua'><i class='fa fa-download'></i></span>
              <div class='info-box-content'>
                <span class='info-box-number'>".$model[$i]["cedula_dignatario"]." - ".$model[$i]["nombre_dignatario"]."</span>
              </div> </a>
            </div>
            </div>
          </div>
          <br>";
       }

     ?>


    <!--<code><?//= __FILE__ ?></code>-->
</div>
