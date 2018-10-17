<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
use app\models\TipoEntidad;
use app\models\ClaseEntidad;
use yii\helpers\Url;
use app\models\User;
use kartik\daterange\DateRangePicker;
/* @var $this yii\web\View */
/* @var $searchModel app\models\EntidadesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Entidades';

$this->params['breadcrumbs'][] = $this->title;
if(!isset($msg)){
  $msg = null;
}
?>

<div class="entidades-index">
    <div style="position:absolute; left: 70%;   ">
        <table style="width: 300px;" border="1">
            <tbody>
                <tr>
                    <td style="width: 150px;" rowspan="3"><center><b>Estado de <br>la Entidad</b></center></td>
                    <td style="width: 150px; background:#90EE90;"><center><b>Activa</b></center></td>
                </tr>
                <tr>
                    <td style="width: 150px; background:#F08080;"><center><b>Inactiva</b></center></td>
                </tr>
                <tr>
                    <td style="width: 150px; background:#F4FA58;"><center><b>Inspección</b></center></td>
                </tr>
            </tbody>
        </table>
    </div>
    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>

        <?php
        if( isset(Yii::$app->user->identity->id_rol) && Yii::$app->user->identity->id_rol == 1){
          echo "
          <a class='btn btn-success' href='?r=entidades%2Fcreate'>Crear Entidades</a>";
        }
        ?>

    </p>
    <?php
        if( isset(Yii::$app->user->identity->id_rol) && Yii::$app->user->identity->id_rol == User::ROL_SUPERUSER){
         ?>
        <?= Html::a(Yii::t('app', 'Generar PDF'), ['samplepdf'], ['class' => 'btn btn-danger']) ?>
        <?= Html::a(Yii::t('app', 'Crear Excel'), ['metodoexcel'], ['target'=>"_blank",'data-pjax' => 0,'class' => 'btn btn-success']) ?>
        <?php
    }
        ?>
<br><br>
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
<?php Pjax::begin(); ?>
  <?php
      if( isset(Yii::$app->user->identity->id_rol ) ){
          if( Yii::$app->user->identity->id_rol == 1 ){
  ?>

<?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,

        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'id_entidad',
            'personeria_year',
            'personeria_n',
            'nombre_entidad',
            // 'fecha_reconocimiento',
            // 'municipio_entidad',
            // 'direccion_entidad',
            // 'telefono_entidad',
            // 'fax_entidad',
            // 'email_entidad:email',
            [
                'attribute' => 'rango_fecha',
                'headerOptions' => ['style' => 'color:#337ab7'],
                'header' => 'Reconocimiento '.Html::tag('span', '<small>
                            <span class="fa fa-info-circle" tool-tip-toggle="tooltip-demo"</span>
                            </small>',
                            [
                                'title'=>'Usted debe seleccionar un rango de fecha:
                                Ej: 2018-01-01 – 2018-12-31',
                                'data-toggle'=>'tooltip',
                                'style'=>'text-decoration: underline; cursor:pointer;'
                            ]),
                'value' => 'fecha_reconocimiento',
                'format'=>'raw',
                'options' => ['style' => 'width: 25%;'],
                'filter' => DateRangePicker::widget([
                    'model' => $searchModel,
                    'attribute' => 'rango_fecha',
                    'useWithAddon'=>false,
                    'convertFormat'=>true,
                    'pluginOptions'=>[
                        'locale'=>['format'=>'Y-m-d']
                    ],
                ])
            ],
            
            [   'header' => 'Tipo Entidad',
                'headerOptions' => ['style' => 'color:#337ab7'],
                'value'=> function($model){
                    return $model->NombreTipoEntidad();
                },
                 'filter'=>
                Html::activeDropDownList($searchModel, 'id_tipo_entidad', ArrayHelper::map(TipoEntidad::find()->all(),'id_tipo_entidad','tipo_entidad'),
                [ 'prompt'=>'-- > Seleccione <--',])
            ],
            [   'header' => 'Clase Entidad',
                'headerOptions' => ['style' => 'color:#337ab7'],
                'value'=> function($model){
                    return $model->NombreClaseEntidad();
                },
                 'filter'=>
                Html::activeDropDownList($searchModel, 'id_clase_entidad', ArrayHelper::map(ClaseEntidad::find()->all(),'id_clase_entidad','clase_entidad'),
                [ 'prompt'=>'-- > Seleccione <--',])
            ],
            // 'objetivos_entidad:ntext',
            // 'fecha_estatutos',
            // 'ubicacion_archivos_entidad',
            // 'fecha_gaceta',
            // 'datos_digitales',
            // 'estado_entidad',

            ['class' => 'yii\grid\ActionColumn',
                'header' => 'Opc',
                'headerOptions' => ['style' => 'color:#337ab7'],

                'contentOptions'=>function($model){
                    if ($model->estado_entidad == 1) {
                        return ['style'=> 'background-color:#90EE90'];
                    }elseif ($model->estado_entidad==2) {
                        return ['style'=> 'background-color:#F08080'];
                    }else{

                        return ['style'=> 'background-color:#F4FA58'];
                    }
                  },
                'template'=>'{view} {update} {dignatario} {historial}',
                'buttons' => [
                    'dignatario' => function ($url, $model, $key) {
                        return $model->id_entidad !=  '' ? Html::a(
                    '<span title="Adicionar Dignatarios" class="fa fa-user-plus"</span>',

                    ['dignatario', 'id' => $model->id_entidad]):' ';

                    },
                    'historial' => function ($url, $model, $key) {
                        return $model->id_entidad !=  '' ? Html::a(
                    '<span title="Historial Cambios" class="fa fa-clock-o"</span>',

                    ['historial', 'id' => $model->id_entidad]):' ';

                    },

                ]
            ],


        ],
    ]); ?>
    <?php
  } else {
?>

    <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,

            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                'id_entidad',
                'personeria_year',
                'personeria_n',
                'nombre_entidad',
                // 'fecha_reconocimiento',
                // 'municipio_entidad',
                // 'direccion_entidad',
                // 'telefono_entidad',
                // 'fax_entidad',
                // 'email_entidad:email',
                [
                    'attribute' => 'rango_fecha',
                    'headerOptions' => ['style' => 'color:#337ab7'],
                    'header' => 'fecha reco '.Html::tag('span', '<small>
                                <span class="fa fa-info-circle" tool-tip-toggle="tooltip-demo"</span>
                                </small>',
                                [
                                    'title'=>'Usted debe seleccionar un rango de fecha:
                                    Ej: 2018-01-01 – 2018-12-31',
                                    'data-toggle'=>'tooltip',
                                    'style'=>'text-decoration: underline; cursor:pointer;'
                                ]),
                    'value' => 'fecha_reconocimiento',
                    'format'=>'raw',
                    'options' => ['style' => 'width: 25%;'],
                    'filter' => DateRangePicker::widget([
                        'model' => $searchModel,
                        'attribute' => 'rango_fecha',
                        'useWithAddon'=>false,
                        'convertFormat'=>true,
                        'pluginOptions'=>[
                            'locale'=>['format'=>'Y-m-d']
                        ],
                    ])
                ],
                [   'header' => 'Tipo Entidad',
                    'headerOptions' => ['style' => 'color:#337ab7'],
                    'value'=> function($model){
                        return $model->NombreTipoEntidad();
                    },
                     'filter'=>
                    Html::activeDropDownList($searchModel, 'id_tipo_entidad', ArrayHelper::map(TipoEntidad::find()->all(),'id_tipo_entidad','tipo_entidad'),
                    [ 'prompt'=>'-- > Seleccione <--',])
                ],
                [   'header' => 'Clase Entidad',
                    'headerOptions' => ['style' => 'color:#337ab7'],
                    'value'=> function($model){
                        return $model->NombreClaseEntidad();
                    },
                     'filter'=>
                    Html::activeDropDownList($searchModel, 'id_clase_entidad', ArrayHelper::map(ClaseEntidad::find()->all(),'id_clase_entidad','clase_entidad'),
                    [ 'prompt'=>'-- > Seleccione <--',])
                ],
                // 'objetivos_entidad:ntext',
                // 'fecha_estatutos',
                // 'ubicacion_archivos_entidad',
                // 'fecha_gaceta',
                // 'datos_digitales',
                // 'estado_entidad',

                ['class' => 'yii\grid\ActionColumn',
                    'header' => 'Opc',
                    'headerOptions' => ['style' => 'color:#337ab7'],

                    'contentOptions'=>function($model){
                        if ($model->estado_entidad == 1) {
                            return ['style'=> 'background-color:#90EE90'];
                        }elseif ($model->estado_entidad==2) {
                            return ['style'=> 'background-color:#F08080'];
                        }else{

                            return ['style'=> 'background-color:#F4FA58'];
                        }
                      },
                    'template'=>'{view} {dignatario} {historial}',
                    'buttons' => [
                        'dignatario' => function ($url, $model, $key) {
                            return $model->id_entidad !=  '' ? Html::a(
                        '<span title="Historial Cambios" class="fa fa-user-plus"</span>',

                        ['dignatario', 'id' => $model->id_entidad]):' ';

                        },
                        'historial' => function ($url, $model, $key) {
                            return $model->id_entidad !=  '' ? Html::a(
                        '<span title ="Adicionar Dignatarios" class="fa fa-clock-o"</span>',

                        ['historial', 'id' => $model->id_entidad]):' ';

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
                'id_entidad',
                'personeria_year',
                'personeria_n',
                'nombre_entidad',
            [
                'attribute' => 'rango_fecha',
                'headerOptions' => ['style' => 'color:#337ab7'],
                'header' => 'fecha reco '.Html::tag('span', '<small>
                            <span class="fa fa-info-circle" tool-tip-toggle="tooltip-demo"</span>
                            </small>',
                            [
                                'title'=>'Usted debe seleccionar un rango de fecha:
                                Ej: 2018-01-01 – 2018-12-31',
                                'data-toggle'=>'tooltip',
                                'style'=>'text-decoration: underline; cursor:pointer;'
                            ]),
                'value' => 'fecha_reconocimiento',
                'format'=>'raw',
                'options' => ['style' => 'width: 25%;'],
                'filter' => DateRangePicker::widget([
                    'model' => $searchModel,
                    'attribute' => 'rango_fecha',
                    'useWithAddon'=>false,
                    'convertFormat'=>true,
                    'pluginOptions'=>[
                        'locale'=>['format'=>'Y-m-d']
                    ],
                ])
            ],
                // 'municipio_entidad',
                // 'direccion_entidad',
                // 'telefono_entidad',
                // 'fax_entidad',
                // 'email_entidad:email',
                [   'header' => 'Tipo Entidad',
                    'headerOptions' => ['style' => 'color:#337ab7'],
                    'value'=> function($model){
                        return $model->NombreTipoEntidad();
                    },
                     'filter'=>
                    Html::activeDropDownList($searchModel, 'id_tipo_entidad', ArrayHelper::map(TipoEntidad::find()->all(),'id_tipo_entidad','tipo_entidad'),
                    [ 'prompt'=>'-- > Seleccione <--',])
                ],
                [   'header' => 'Clase Entidad',
                    'headerOptions' => ['style' => 'color:#337ab7'],
                    'value'=> function($model){
                        return $model->NombreClaseEntidad();
                    },
                     'filter'=>
                    Html::activeDropDownList($searchModel, 'id_clase_entidad', ArrayHelper::map(ClaseEntidad::find()->all(),'id_clase_entidad','clase_entidad'),
                    [ 'prompt'=>'-- > Seleccione <--',])
                ],
                // 'objetivos_entidad:ntext',
                // 'fecha_estatutos',
                // 'ubicacion_archivos_entidad',
                // 'fecha_gaceta',
                // 'datos_digitales',
                // 'estado_entidad',

                ['class' => 'yii\grid\ActionColumn',
                    'header' => 'Opc',
                    'headerOptions' => ['style' => 'color:#337ab7'],

                    'contentOptions'=>function($model){
                        if ($model->estado_entidad == 1) {
                            return ['style'=> 'background-color:#90EE90'];
                        }elseif ($model->estado_entidad==2) {
                            return ['style'=> 'background-color:#F08080'];
                        }else{

                            return ['style'=> 'background-color:#F4FA58'];
                        }
                      },
                    'template'=>'{view} {dignatario}',
                    'buttons' => [
                        'dignatario' => function ($url, $model, $key) {
                            return $model->id_entidad !=  '' ? Html::a(
                        '<span title="Adicionar Dignatarios" class="fa fa-user-plus"</span>',

                        ['dignatario', 'id' => $model->id_entidad]):' ';

                        },

                    ]
                ],


            ],
        ]); ?>

    <?php
    }
     ?>
</div>
<?php Pjax::end(); ?>
