<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
use app\models\Cargos;
use app\models\Radicados;
use app\models\GruposCargos;
use kartik\select2\Select2;
/* @var $this yii\web\View */
/* @var $searchModel app\models\DignatariosSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Dignatarios: '.$titulo;
$this->params['breadcrumbs'][] = $this->title;
if(!isset($msg)){
  $msg = null;
}
?>
<?php Pjax::begin(); ?>
<div class="dignatarios-index">
    <div style="position:absolute; left: 70%;   ">
        <table style="width: 300px;" border="1">
            <tbody>
                <tr>
                    <td style="width: 150px;" rowspan="3"><center><b>Estado del <br>Dignatario</b></center></td>
                    <td style="width: 150px; background:#90EE90;"><center><b>Activo</b></center></td>
                </tr>
                <tr>
                    <td style="width: 150px; background:#F08080;"><center><b>Inactivo</b></center></td>
                </tr>
            </tbody>
        </table>
    </div>
    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?php
        $session = Yii::$app->session;
        $id1 = $session->get('id_radicado');
        $radicado = Radicados::findOne($id1);

        
        if( isset(Yii::$app->user->identity->id_rol) && $radicado ){
          echo "
          <a class='btn btn-success' href='?r=dignatarios%2Fcreate'>Adicionar Dignatarios</a> ";
          //echo "<a class='btn btn-danger' href='?r=dignatarios%2Fcreate'>Establecer Fecha a dignatarios activos</a>";
        }
        ?>
    </p>
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
<?php
      $session = Yii::$app->session;
      $editar = $session->get('editar');
     if( isset(Yii::$app->user->identity->id_rol) ){
       if( Yii::$app->user->identity->id_rol == 1 || $editar == true ){
?>
  <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,

        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            //'id_dignatario',
            'cedula_dignatario',
            'nombre_dignatario',
            //  'estado:boolean',
            [  
             'header' => 'Cargo',
                 'headerOptions' => ['style' => 'color:#337ab7'],
                 'value'=> function($model){
                     return $model->NombreCargo();
                 },

             'attribute' => 'id_cargo',
             'filter'=> Select2::widget([
               'model' => $searchModel,
               'attribute' => 'id_cargo',
               'data' => ArrayHelper::map($cargos,'id_cargo','nombre_cargo'),
               'language' => 'es',
                   'options' => [
                     'placeholder' => 'Seleccione un cargo',

                     ],
                     'pluginOptions' => [
                         'allowClear' => true,
                        
                     ],
             ]), 
               
               
           ],
            //'id_municipio_expedicion',
            // 'fecha_ingreso',
            // 'id_entidad',
            // 'id_cargo',
             [  
             'header' => 'Grupo Cargos',
                 'headerOptions' => ['style' => 'color:#337ab7'],
                 'value'=> function($model){
                     return $model->NombreGrupoCargo();
                 },

             'attribute' => 'id_grupo_cargos',
             'filter'=> Select2::widget([
               'model' => $searchModel,
               'attribute' => 'id_grupo_cargos',
               'data' => ArrayHelper::map($gcargos,'id_grupo_cargos','nombre_grupo_cargo'),
               'language' => 'es',
                   'options' => [
                     'placeholder' => 'Seleccione un grupo cargo',

                     ],
                     'pluginOptions' => [
                         'allowClear' => true,
                        
                     ],
             ]), 
               
               
           ],

            ['class' => 'yii\grid\ActionColumn',
                'header' => 'Opc',
                'headerOptions' => ['style' => 'color:#337ab7'],
                'contentOptions'=>function($model){
                if ($model->estado == true) {
                    return ['style'=> 'background-color:#90EE90'];
                }else{

                    return ['style'=> 'background-color:#F08080' ];
                }
              },
                'template'=> '{view}{update}{historial}',
                'buttons' => [
                    'historial' => function ($url, $model, $key) {
                      return $model->id_dignatario !=  '' ? Html::a(
                      '<span title="Historial" class="fa fa-clock-o"</span>',

                      ['historial', 'id' => $model->id_dignatario]):' ';
                    },

                ]

            ],

        ],
    ]); ?>
    <?php
  }else{
?>

<?= GridView::widget([
      'dataProvider' => $dataProvider,
      'filterModel' => $searchModel,

      'columns' => [
          ['class' => 'yii\grid\SerialColumn'],

          //'id_dignatario',
          'cedula_dignatario',
          'nombre_dignatario',
          //  'estado:boolean',
          
          [  
             'header' => 'Cargo',
                 'headerOptions' => ['style' => 'color:#337ab7'],
                 'value'=> function($model){
                     return $model->NombreCargo();
                 },

             'attribute' => 'id_cargo',
             'filter'=> Select2::widget([
               'model' => $searchModel,
               'attribute' => 'id_cargo',
               'data' => ArrayHelper::map($cargos,'id_cargo','nombre_cargo'),
               'language' => 'es',
                   'options' => [
                     'placeholder' => 'Seleccione uns cargo',

                     ],
                     'pluginOptions' => [
                         'allowClear' => true,
                        
                     ],
             ]), 
               
               
           ],
          //'id_municipio_expedicion',
          // 'fecha_ingreso',
          // 'id_entidad',
          // 'id_cargo',
           [  
             'header' => 'Grupo Cargos',
                 'headerOptions' => ['style' => 'color:#337ab7'],
                 'value'=> function($model){
                     return $model->NombreGrupoCargo();
                 },

             'attribute' => 'id_grupo_cargos',
             'filter'=> Select2::widget([
               'model' => $searchModel,
               'attribute' => 'id_grupo_cargos',
               'data' => ArrayHelper::map($gcargos,'id_grupo_cargos','nombre_grupo_cargo'),
               'language' => 'es',
                   'options' => [
                     'placeholder' => 'Seleccione uns cargo',

                     ],
                     'pluginOptions' => [
                         'allowClear' => true,
                        
                     ],
             ]), 
               
               
           ],

          ['class' => 'yii\grid\ActionColumn',
              'header' => 'Opc',
              'headerOptions' => ['style' => 'color:#337ab7'],
              'contentOptions'=>function($model){
              if ($model->estado == true) {
                  return ['style'=> 'background-color:#90EE90'];
              }else{

                  return ['style'=> 'background-color:#F08080' ];
              }
            },
              'template'=> '{view} {historial}',
              'buttons' => [
                  'historial' => function ($url, $model, $key) {
                    return $model->id_dignatario !=  '' ? Html::a(
                    '<span title="Historial" class="fa fa-clock-o"</span>',

                    ['historial', 'id' => $model->id_dignatario]):' ';
                  },

              ]

          ],

      ],
  ]); ?>

<?php
  }
}else{
    ?>
      <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,

        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            //'id_dignatario',
            'cedula_dignatario',
            'nombre_dignatario',
            //  'estado:boolean',
            [  
             'header' => 'Cargo',
                 'headerOptions' => ['style' => 'color:#337ab7'],
                 'value'=> function($model){
                     return $model->NombreCargo();
                 },

             'attribute' => 'id_cargo',
             'filter'=> Select2::widget([
               'model' => $searchModel,
               'attribute' => 'id_cargo',
               'data' => ArrayHelper::map($cargos,'id_cargo','nombre_cargo'),
               'language' => 'es',
                   'options' => [
                     'placeholder' => 'Seleccione un cargo',

                     ],
                     'pluginOptions' => [
                         'allowClear' => true,
                        
                     ],
             ]), 
               
               
           ],
            //'id_municipio_expedicion',
            // 'fecha_ingreso',
            // 'id_entidad',
            // 'id_cargo',
            [  
             'header' => 'Grupo Cargos',
                 'headerOptions' => ['style' => 'color:#337ab7'],
                 'value'=> function($model){
                     return $model->NombreGrupoCargo();
                 },

             'attribute' => 'id_grupo_cargos',
             'filter'=> Select2::widget([
               'model' => $searchModel,
               'attribute' => 'id_grupo_cargos',
               'data' => ArrayHelper::map($gcargos,'id_grupo_cargos','nombre_grupo_cargo'),
               'language' => 'es',
                   'options' => [
                     'placeholder' => 'Seleccione un grupo cargos',

                     ],
                     'pluginOptions' => [
                         'allowClear' => true,
                        
                     ],
             ]), 
               
               
           ],

            ['class' => 'yii\grid\ActionColumn',
                'header' => 'Opc',
                'headerOptions' => ['style' => 'color:#337ab7'],
                'contentOptions'=>function($model){

                if ($model->estado == true) {
                    return ['style'=> 'background-color:#90EE90; text-align:center;' ];
                }else{

                    return ['style'=> 'background-color:#F08080;  text-align:center;' ];
                }
              },
                'template'=> '{view}',
            ],

        ],
    ]); ?>

    <?php

}
      ?>
<?php Pjax::end(); ?></div>
