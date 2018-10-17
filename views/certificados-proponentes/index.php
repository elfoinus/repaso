<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use app\models\TipoEntidad;
use app\models\ClaseEntidad;
/* @var $this yii\web\View */
/* @var $searchModel app\models\EntidadesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/png', 'href' => '/web/img/favicon.ico']);
$this->title = 'Certificados de Proponentes';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="entidades-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>

    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

          //  'id_entidad',
            'personeria_year',
            'personeria_n',
            'nombre_entidad',
          //  'fecha_reconocimiento',
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
            'header' => 'Doc',
            'headerOptions' => ['style' => 'color:#337ab7'],
            'template' => '{view}',
            'buttons' => [
              'view' => function ($url, $model, $key) {
                return $model->id_entidad !=  '' ? Html::a(
                '<span class="fa fa-file-word-o"</span>',
                ['view', 'id' => $model->id_entidad]):'';
                },

            ]
              ],
        ],
    ]); ?>
</div>
