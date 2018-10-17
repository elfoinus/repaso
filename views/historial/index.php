<?php

use yii\helpers\Html;
use yii\grid\GridView;
use app\models\User;
use yii\helpers\ArrayHelper;
use dosamigos\datepicker\DatePicker;
use kartik\daterange\DateRangePicker;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\HistorialSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Historial'.$titulo;
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="historial-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?php
        if( isset(Yii::$app->user->identity->id_rol) && Yii::$app->user->identity->id_rol == User::ROL_SUPERUSER){
          echo "
          <a class='btn btn-danger' href='?r=historial%2Fsamplepdf'>Generar PDF</a>
          <a class='btn btn-success' href='?r=historial%2Fsampleexcel'>Crear Excel</a>";
        }
        ?>
    </p>
    <?php Pjax::begin(); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            //'id_historial',
            'nombre_evento',
            //'id_tabla_modificada',
            //'fecha_modificacion',
            /*[
                'attribute'=> 'fecha_modificacion',
                'value' =>'fecha_modificacion',
                'format'=> 'raw',
                'filter' =>DatePicker::widget([
                    'model' => $searchModel,
                    'attribute' => 'fecha_modificacion',
                        'clientOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd',
                        ]
                ]),

            ],
            */
            [
            'attribute' => 'rango_fecha',
            'headerOptions' => ['style' => 'color:#337ab7'],
            'header' => 'Rango fecha '.Html::tag('span', '<small>
                        <span class="fa fa-info-circle" tool-tip-toggle="tooltip-demo"</span>
                        </small>',
                        [
                            'title'=>'Usted debe seleccionar un rango de fecha:
                            Ej: 2018-01-01 – 2018-12-31',
                            'data-toggle'=>'tooltip',
                            'style'=>'text-decoration: underline; cursor:pointer;'
                        ]),
            'value' => 'fecha_modificacion',
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

          //  'nombre_campo_modificado',
            // 'valor_anterior_campo:ntext',
            // 'valor_nuevo_campo:ntext',
            //'id_usuario_modifica',
            [   'header' => 'Usuario',
                'headerOptions' => ['style' => 'color:#337ab7'],
                'value'=> function($model){
                    return $model->user();
                },
                'filter'=>
                Html::activeDropDownList($searchModel, 'id_usuario_modifica', ArrayHelper::map(User::find()->all(),'id','nombre_funcionario'),
                [ 'prompt'=>'-- > Seleccione <--',])
            ],
             'tabla_modificada',

            ['class' => 'yii\grid\ActionColumn',
            'template'=> '{view}',],
        ],
    ]); ?>
</div>
<?php Pjax::end(); ?>