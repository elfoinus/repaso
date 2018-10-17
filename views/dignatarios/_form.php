<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use dosamigos\datepicker\DatePicker;
use app\models\Entidades;
use app\models\Cargos;
use app\models\GruposCargos;
use app\models\Municipios;
use yii\helpers\ArrayHelper;
use kartik\select2\Select2;
use app\models\Dignatarios;
/* @var $this yii\web\View */
/* @var $model app\models\Dignatarios */
/* @var $form yii\widgets\ActiveForm */
$session = Yii::$app->session;
$id = $session->get('id_entidad');
//$model = new Dignatarios();
$model->id_entidad = $id;
$representante = Dignatarios::find()->where(['and',['id_entidad' => $id],['id_cargo'=> 1]])->one();
$form = ActiveForm::begin();
if($representante){
?>

<div class="dignatarios-form">
    <div class="row">
        <?php
            $ent= Entidades::findOne($model->id_entidad);
            /*         <?= $form->field($model, 'fecha_ingreso')->widget(
                        DatePicker::className(), [
                        // inline too, not bad
                        'inline' => false,
                        'language'=> 'es',
                        // modify template for custom rendering
                        //'template' => '<div class="well well-sm" style="background-color: #fff; width:250px">{input}</div>',
                            'clientOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-m-d'
                                ]
                        ]);?>


                    <?= $form->field($model, 'id_entidad')->textInput() ?>
                    */
                      $model->estado =1;
         ?>



        <div class="col-lg-6">
            <div class="col-lg-5">
                <?= $form->field($model, 'cedula_dignatario')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-lg-8">
                <?= $form->field($model, 'nombre_dignatario')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-lg-6">

                <?php
                $var = [ 1 => 'Activo', 0 => 'Inactivo'];
                if($update ){
                echo $form->field($model, 'estado')->dropDownList($var, ['prompt' => 'Seleccione el estado' ]);
                }else {
                  echo $form->field($model, 'estado')->dropDownList($var, ['prompt' => 'Seleccione el estado','disabled' => true ]);
                }
                ?>
            </div>
            <div class="col-lg-10">
                <?php
                    $mun = Municipios::find()->asArray()->all();
                    for ($i=0; $i < count($mun) ; $i++) {
                      $mun[$i]['municipio'] = $mun[$i]['municipio'].' - '.Municipios::getNombreDepartamento($mun[$i]['departamento_id']);
                    }

                        echo $form->field($model, 'id_municipio_expedicion')->widget(Select2::classname(), [
                        'data' => ArrayHelper::map($mun,'id_municipio','municipio'),
                        'language' => 'es',
                        'options' => [
                          'placeholder' => 'Seleccione una Entidad',
                          ],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'minimumInputLength' => 3,
                        ],

                    ])

                  ?>


                <?php
                    $car = Cargos::find()->asArray()->all();

                        echo $form->field($model, 'id_cargo')->widget(Select2::classname(), [
                        'data' => ArrayHelper::map($car,'id_cargo','nombre_cargo'),
                        'language' => 'es',
                        'options' => [
                          'placeholder' => 'Seleccione un cargo',
                          ],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'minimumInputLength' => 3,
                        ],

                    ])
                            ?>
                <?php
                    $grup = GruposCargos::find()->asArray()->all();

                        echo $form->field($model, 'id_grupo_cargos')->widget(Select2::classname(), [
                        'data' => ArrayHelper::map($grup,'id_grupo_cargos','nombre_grupo_cargo'),
                        'language' => 'es',
                        'options' => [
                          'placeholder' => 'Seleccione un cargo',
                          ],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'minimumInputLength' => 3,
                        ],

                    ])  
                ?>
            </div>
            <div class ="col-lg-6">
                <?= $form->field($model, 'tarjeta_profesiona')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-lg-8">
            <?= $form->field($model, 'inicio_periodo')->widget(
                DatePicker::className(), [
                // inline too, not bad
                'inline' => false,
                'language'=> 'es',
                 // modify template for custom rendering
                //'template' => '<div class="well well-sm" style="background-color: #fff; width:250px">{input}</div>',
                'clientOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-m-d'
                ]
            ]);?>
            </div>
            <div class="col-lg-4">
                <?php
                echo Html::tag('span', '<h3> <span class="fa fa-info-circle" tool-tip-toggle="tooltip-demo"</span></h3>', [
                        'title'=>'Formato fechas: año-mes-día
                        2018-12-31',
                        'data-toggle'=>'tooltip',
                        'style'=>'text-decoration: underline; cursor:pointer;'
                        ]);
                 ?>
            </div>
            <div class="col-lg-8">
        <?= $form->field($model, 'fin_periodo')->widget(
            DatePicker::className(), [
            // inline too, not bad
            //'label'=> 'Fecha Final de periodo',
            'inline' => false,
            'language'=> 'es',

             // modify template for custom rendering
            //'template' => '<div class="well well-sm" style="background-color: #fff; width:250px">{input}</div>',
            'clientOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-m-d'
            ]
    ]);?>


                <div class="form-group">
                    <?= Html::submitButton($model->isNewRecord ? 'Crear' : 'Actualizar', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary','data' => [
                        'confirm' => '¿Usted esta seguro que desea realizar este proceso?',
                        'method' => 'post'],]) ?>
                </div>
                </div>
                <div class="col-lg-4">
                <?php
                echo Html::tag('span', '<h3> <span class="fa fa-info-circle" tool-tip-toggle="tooltip-demo"</span></h3>', [
                        'title'=>'Formato fechas: año-mes-día
                        2018-12-31',
                        'data-toggle'=>'tooltip',
                        'style'=>'text-decoration: underline; cursor:pointer;'
                        ]);
                 ?>
            </div>
              </div>
            </div>
         </div>



    </div>
</div>
<?php }else{  ?>

  <div class="col-lg-6">
    <h2>Representante Legal</h2>

    <div class="box box-primary">
        <div class="col-lg-10">
            <?= $form->field($model, 'cedula_dignatario')->textInput(['maxlength' => true,'placeholder' =>'Ingrese N° de documento de identidad dignatario']) ?>
        </div>
        <div class="col-lg-10">
            <?= $form->field($model, 'nombre_dignatario')->textInput(['maxlength' => true,'placeholder' =>'Ingrese nombres y apellidos del dignatario']) ?>
        </div>
        <div class="col-lg-10">
            <?php
                $mun = Municipios::find()->asArray()->all();
                for ($i=0; $i < count($mun) ; $i++) {
                  $mun[$i]['municipio'] = $mun[$i]['municipio'].' - '.Municipios::getNombreDepartamento($mun[$i]['departamento_id']);
                }
                $munList=ArrayHelper::map($mun,'id_municipio','municipio');
                    //echo $form->field($model, 'id_municipio_expedicion')->dropDownList($munList,['prompt'=>'Seleccione el Municipio']);

                    echo $form->field($model, 'id_municipio_expedicion')->widget(Select2::classname(), [
                    'data' => ArrayHelper::map($mun,'id_municipio','municipio'),
                    'language' => 'es',

                    'options' => [
                      'placeholder' => 'Seleccione un Municipio',
                      ],
                    'pluginOptions' => [
                        'allowClear' => true,
                      //  'minimumInputLength' => 3,
                    ],

                ])

              ?>

            <?php
                $car = Cargos::find()->asArray()->all();
                $model->id_cargo = 1;
                $model->estado =1;
                $session = Yii::$app->session;
                $session->set('repre',true);
                $cargosList=ArrayHelper::map($car,'id_cargo','nombre_cargo');
                    echo $form->field($model, 'id_cargo')->dropDownList($cargosList,['prompt'=>'Seleccione el cargo','disabled' => true]);

                        ?>

        </div>
        <div class="col-lg-8">
        <?= $form->field($model, 'inicio_periodo')->widget(
            DatePicker::className(), [
            // inline too, not bad
            'inline' => false,
            'language'=> 'es',
             // modify template for custom rendering
            //'template' => '<div class="well well-sm" style="background-color: #fff; width:250px">{input}</div>',
            'clientOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-m-d'
                //'minDate' => '2018-02-22',
                //'maxDate' => '9999-99-99',
            ]
        ]);?>
        </div>
        <div class="col-lg-3">
            <?php
            echo Html::tag('span', '<h3> <span class="fa fa-info-circle" tool-tip-toggle="tooltip-demo"</span></h3>', [
                    'title'=>'Formato fechas: año-mes-día
                    2018-12-31',
                    'data-toggle'=>'tooltip',
                    'style'=>'text-decoration: underline; cursor:pointer;'
                    ]);
             ?>
        </div>
        <div class="col-lg-8">
        <?= $form->field($model, 'fin_periodo')->widget(
            DatePicker::className(), [
            // inline too, not bad
            'inline' => true,
            'language'=> 'es',
             // modify template for custom rendering
            //'template' => '<div class="well well-sm" style="background-color: #fff; width:250px">{input}</div>',
            'clientOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-m-d',
                //'minDate' => '2018-02-22',
                //'maxDate' => '9999-99-99',
            ]
        ]);?>
        </div>
        <div class="col-lg-3">
        <?php
        echo Html::tag('span', '<h3> <span class="fa fa-info-circle" tool-tip-toggle="tooltip-demo"</span></h3>', [
                'title'=>'Formato fechas: año-mes-día
                2018-12-31',
                'data-toggle'=>'tooltip',
                'style'=>'text-decoration: underline; cursor:pointer;'
                ]);
         ?>
         <div class="form-group">
             <?= Html::submitButton($model->isNewRecord ? 'Crear' : 'Actualizar', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary','data' => [
                 'confirm' => '¿Usted esta seguro que desea realizar este proceso?',
                 'method' => 'post'],]) ?>
         </div>
    </div>

        </div>
<?php }
ActiveForm::end();
// dignatarios-inicio_periodo

?>

<script>

 document.getElementById("dignatarios-cedula_dignatario").addEventListener("change", buscar);

 var elemento = document.getElementById("dignatarios-cedula_dignatario");
 var valor = document.getElementById("dignatarios-cedula_dignatario").value;

function act(valor){
  $("#dignatarios-id_municipio_expedicion").prop('selectedIndex',valor);
}


function buscar(){
var valor = document.getElementById("dignatarios-cedula_dignatario").value;
//alert(valor);

        $.ajax({
          //http://localhost:8080/index.php?r=radicados%2Fview&id=8
          url: "?r=dignatarios%2Fbuscar",
          dataType: 'json',
          data : {
                dignatario:valor,
              },
          type: "post",
          success: function(data){
           //document.getElementById("dignatarios-nombre_dignatario").val(data);
           var respuesta = data.split(",");
           if(respuesta.length > 0){
            $("#dignatarios-nombre_dignatario").val(respuesta[0]);
            act(respuesta[1]);
            $("#dignatarios-fin_periodo").focus();

          }else{
            
          }
            //alert(data);

          },
          error: function (request, status, error) {
            //alert("No se pudo realizar la operación error '"+request.responseText+"' Comuniquese con un administrador");
            console.log("No se pudo realizar la operación error '"+request.responseText+"' Comuniquese con un administrador");
            }
        });

        //http://localhost/juridica/web/index.php?r=entidades%2Fview&id=11426
        //window.location="?r=dignatarios%2Fbuscar&dignatario="+valor;

}


</script>
