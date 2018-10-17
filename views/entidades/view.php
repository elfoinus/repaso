<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\models\Municipios;
use app\models\Resoluciones;
use app\models\ClaseEntidad;
use app\models\TipoEntidad;
use app\models\TipoResolucion;
use yii\helpers\Url;
use kartik\file\FileInput;
use app\models\Dignatarios;
/* @var $this yii\web\View */
/* @var $model app\models\Entidades */
$representante = Dignatarios::find()->where(['and',['id_entidad' => $model->id_entidad],['id_cargo'=> 1],['estado'=>1]])->one();
$this->title = $model->nombre_entidad;
$this->params['breadcrumbs'][] = ['label' => 'Entidades', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$resolucion = Resoluciones::find()->where(['and',['id_entidad' => $model->id_entidad],['id_tipo_resolucion' => 1]])->one();
$resoluciones = Resoluciones::find()->where(['id_entidad' => $model->id_entidad])->all();
//print_r( $representante);
if(!isset($msg)){
  $msg = null;
}
?>
    <?php if ($msg !== null){  ?>
    <div class="row">
          <div class="box box-primary box-solid">
            <div class="box-header with-border">
              <h3 class="box-title">Información</h3>

              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
              </div>
              <!-- /.box-tools -->
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <?php print $msg; ?>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
          </div>
    <?php } ?>
<div class="entidades-view" >

    <h1><?= Html::encode($this->title) ?></h1>

    <p>



         <?php
         // <?= Html::a('descargar datos digitales', ['download','file' => $model->datos_digitales], ['class' => 'btn btn-success'])
         //Html::a('Actualizar', ['update', 'id' => $model->id_entidad], ['class' => 'btn btn-primary'])
       echo "<a href='?r=dignatarios'> <i, class='btn btn-info'></i> <span>Dignatarios</span>  </a>
       ";
       // http://localhost:8080?r=resoluciones%2Findex&id=1
      /* $session = Yii::$app->session;
       $dignatarios = $session->get('dignatarios');
       if ($dignatarios == 1){
         echo "<a href='?r=dignatarios%2Findex1&id=".$model->id_entidad."'> <i, class='btn btn-info'></i> <span>Continuar Tramite</span>  </a>
         ";
       }*/
        ?>

        <?php
         $mun= Municipios::findOne($model->municipio_entidad);
         $tip= TipoEntidad::findOne($model->id_tipo_entidad);
         $clas= ClaseEntidad::findOne($model->id_clase_entidad);
    ?>

    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id_entidad',
            'personeria_year',
            'personeria_n',
            'nombre_entidad',
            'fecha_reconocimiento',
             [   'attribute' => 'municipio_entidad',
                'label' => 'Municipio',
                'value'=> $mun['municipio'],

               ],
            'direccion_entidad',
            'telefono_entidad',
            'fax_entidad',
            'email_entidad:email',
            [  'attribute'=> 'id_tipo_entidad',
                'label'=>'Tipo Entidad',
                'value'=>$tip['tipo_entidad'],

            ],
            [   'attribute'=> 'id_clase_entidad',
                'label'=> 'Clase Entidad',
                'value'=> $clas['clase_entidad'],

            ],
            'objetivos_entidad:ntext',
            'fecha_estatutos',
            'ubicacion_archivos_entidad',
            'fecha_gaceta',
            //'datos_digitales',
            //'estado_entidad',
            [   'attribute'=>'estado_entidad',
                'label'=> 'Estado de la Entidad',
                'value'=> function($model){
                    if ($model->estado_entidad == 1) {
                        return 'Activo';
                    }elseif ($model->estado_entidad==2) {
                        return 'Inactivo';
                    }else{
                        return 'Observación';
                }
   },
            ],
            'periodo_entidad',
        ],
    ]) ?>
</div>
    <?php
    if($representante){
      $municipio = Municipios::findOne($representante->id_municipio_expedicion);
      echo " <div class='box box-widget widget-user'>

            <div class='widget-user-header bg-aqua-active'>
              <h3 class='widget-user-username'> Representante Legal </h3>
              <h5 class='widget-user-desc'>Fecha de Ingreso: ".$representante->fecha_ingreso."</h5>
            </div>
            <div class='widget-user-image'>
              <img class='img-circle' src='img/representante.jpeg' alt='User Avatar'>
            </div>
            <div class='box-footer'>
              <div class='row'>

                <div class='col-sm-4 border-right'>
                  <div class='description-block'>
                    <h5 class='description-header'>Cedula</h5>
                    <span class='description-text'>".$representante->cedula_dignatario."</span>
                  </div>
                </div>

                <div class='col-sm-4 border-right'>
                  <div class='description-block'>
                    <h5 class='description-header'>Nombre</h5>
                    <span class='description-text'>".$representante->nombre_dignatario."</span>
                  </div>
                </div>

                <div class='col-sm-4'>
                  <div class='description-block'>
                    <h5 class='description-header'>Lugar de expedición</h5>
                    <span class='description-text'>".$municipio['municipio']."</span>
                  </div>
                </div>

              </div>

              <div class='row'>

                <div class='col-sm-6 border-right'>
                  <div class='description-block'>
                    <h5 class='description-header'>Fecha Inicio Periodo</h5>
                    <span class='description-text'>".$representante->inicio_periodo."</span>
                  </div>
                </div>


                <div class='col-sm-6'>
                  <div class='description-block'>
                    <h5 class='description-header'>Fecha Fin Periodo</h5>
                    <span class='description-text'>".$representante->fin_periodo."</span>
                  </div>
                </div>

              </div>

            </div>
          </div>";
    }
    ?>

    <?php

    if(isset(Yii::$app->user->identity->id_rol) ){
        if(isset($resolucion)){
       ?>
       <table>

      <?php
        echo "


            <div class='col-sm-12'>
            <a href='?r=entidades%2Fre&id=".$model->id_entidad."'
            <div class='info-box'>
              <span class='info-box-icon bg-aqua'><i class='fa fa-download'></i></span>
              <div class='info-box-content'>
                <span class='info-box-number'>Resolución ".$resolucion['ano_resolucion']." - ".$resolucion['numero_resolucion'] ." de Reconocimiento de Personería jurídica</span> </div>
              </div></a>
            </div>"
       ;
     }
        ?>



<?php
          if(isset($resoluciones)){

            for ($i=0; $i <count($resoluciones) ; $i++) {
              $tipos = TipoResolucion::findOne($resoluciones[$i]['id_tipo_resolucion']);
              $tipo = $tipos['nombre_tipo_resolucion'];
              if($resoluciones[$i]['id_tipo_resolucion'] != 1){
                echo "
                <div class='col-sm-12'>
                    <div class='col-sm-6'>
                    <a href='?r=entidades%2Fres&id=".$resoluciones[$i]['id_resolucion']."'
                      <div class='info-box'>
                        <span class='info-box-icon bg-aqua'><i class='fa fa-download'></i></span>
                      <div class='info-box-content'>
                        <span class='info-box-number'>Resolución ".$resoluciones[$i]['ano_resolucion']." - ".$resoluciones[$i]['numero_resolucion']."  $tipo </span>
                      </div> </a>
                    </div>
                    </div>
                  </div>
                ";
              }

            }
          }

       ?>

      <?php
echo "
<h1> ARCHIVOS DE LA ENTIDAD $model->nombre_entidad </h1> ";

      if (!file_exists($model->id_entidad)){
        mkdir($model->id_entidad);
      }else{
        $archivos = scandir($model->id_entidad);
        unset($archivos[0],$archivos[1]);
        foreach ($archivos as $key => $value) {
          echo
         "
         <a class='btn btn-lg btn-default fa fa-download ' style='width:250px; height:75px;  font-size: 15px; font-weight: 550; ' href='?r=entidades%2Fdownload&file=$value'>    Descargar <br>  $value</a> &nbsp"
          ;
        }
      }

    }

   ?>
