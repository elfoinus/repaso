<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use dosamigos\datepicker\DatePicker;
use app\models\Entidades;
use app\models\Municipios;
use app\models\ClaseEntidad;
use app\models\TipoEntidad;
use yii\grid\GridView;
use app\models\Autocomplete;
use kartik\file\FileInput;
use app\models\Dignatarios;
use app\models\Cargos;
use app\models\GruposCargos;
use kartik\select2\Select2;
/* @var $this yii\web\View */
/* @var $model app\models\Entidades */
/* @var $form yii\widgets\ActiveForm */
/*
$model_dignatarios = Dignatarios::find()->where( ['and',['id_entidad' => $model->id_entidad], ['id_cargo' => 1], ['estado' => 1] ])->one();


if ( !isset($model_dignatarios)  ){
    $model_dignatarios = new Dignatarios();
}
*/
//print_r($model_dignatarios);

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

<div class="entidades-form">
    <div class="row">
        <div class="col-lg-6">
            <div class="box box-primary">
                <?php $form = ActiveForm::begin(['enableAjaxValidation' => true]); //  ?>

                <div class="rows">

                <div class="">
                    <div class="col-lg-12">
                        <div class="col-lg-10">
                            <?php
                            if($file){
                                echo $form->field($model, 'nombre_entidad')->textInput(['disabled' => true,'maxlength' => true,'placeholder' =>'Ingrese el nombre de la entidad.']); 
                            }else{
                                echo $form->field($model, 'nombre_entidad')->textInput(['maxlength' => true,'placeholder' =>'Ingrese el nombre de la entidad.']); 
                            }

                            ?>
                        </div>
                        <div class="col-lg-12">

                        </div>
                        <div class="col-lg-6">
                            <?php 
                             if($file){
                               echo $form->field($model, 'fecha_reconocimiento')->textInput(['disabled' => true]);
                            }else{
                               echo $form->field($model, 'fecha_reconocimiento')->widget(
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
                                ]);
                            }
                            ?>

                        </div>
                        <div class="col-lg-6">
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

                <div class="rows, col-lg-12">
                    <div class="col-lg-10">
                    <?php
                        $municipioEntidades=Entidades::find()->all();
                        $mun = Municipios::find()->where(['departamento_id' => 76])->asArray()->all();
                        for ($i=0; $i < count($mun) ; $i++) {
                          $mun[$i]['municipio'] = $mun[$i]['municipio'].' - '.Municipios::getNombreDepartamento(76);
                        }
                        $municipioEntidadesList=ArrayHelper::map($mun,'id_municipio','municipio');
                        if($file){
                        echo $form->field($model, 'municipio_entidad')->dropDownList($municipioEntidadesList,['prompt'=>'Seleccione el municipio de  la entidad','disabled' => true]);
                        }else{
                           echo $form->field($model, 'municipio_entidad')->dropDownList($municipioEntidadesList,['prompt'=>'Seleccione el municipio de  la entidad']);      
                        }
                        ?>
                    </div>
                </div>

                <div class="rows">
                    <div class="col-lg-12">

                        <div class="col-lg-10">
                        <?php
                            if($file){
                            echo $form->field($model, 'direccion_entidad')->textInput(['maxlength' => true, 'placeholder'=>'Ingrese la dirección de la entidad.','disabled' => true]);
                        }else{
                             echo $form->field($model, 'direccion_entidad')->textInput(['maxlength' => true, 'placeholder'=>'Ingrese la dirección de la entidad.']);
                        }
                        ?>
                        </div>
                        <div class="col-lg-6">
                            <?php
                            if($file){
                                echo $form->field($model, 'telefono_entidad')->textInput(['maxlength' => true,'disabled' => true]);
                            }else{
                                echo $form->field($model, 'telefono_entidad')->textInput(['maxlength' => true]);
                            }
                             ?>
                            
                        </div>
                        <div class="col-lg-6">
                            <?php 
                            if($file){
                               echo $form->field($model, 'fax_entidad')->textInput(['maxlength' => true,'disabled' => true]);
                            }else{
                               echo $form->field($model, 'fax_entidad')->textInput(['maxlength' => true]);
                            }
                             ?>
                            
                        </div>
                        <div class="col-lg-10">
                            <?php
                            if($file){ 
                                echo $form->field($model, 'email_entidad')->textInput(['maxlength' => true,'disabled' => true]);
                                }else{
                                echo $form->field($model, 'email_entidad')->textInput(['maxlength' => true]);
                                } 
                            ?>
                        </div>
                    </div>
                </div>

                <div class="rows, col-lg-12">
                    <div class="col-lg-10">
                        <?php
                            $tipoEntidades=Entidades::find()->all();
                            $tipoEn =TipoEntidad::find()->where(['activo' => 1])->asArray()->all();
                            //[1,7,5,8,9,11,19]
                            $tipoEntidadesList=ArrayHelper::map($tipoEn,'id_tipo_entidad','tipo_entidad');
                            if($file){
                             echo $form->field($model, 'id_tipo_entidad')->dropDownList($tipoEntidadesList,['prompt'=>'Seleccione el tipo de entidad','disabled' => true]);
                            }else{
                                echo $form->field($model, 'id_tipo_entidad')->dropDownList($tipoEntidadesList,['prompt'=>'Seleccione el tipo de entidad']);
                            }
                            ?>

                        <?php
                            $clasesEntidades=Entidades::find()->all();
                            $clsse =ClaseEntidad::find()->asArray()->all();

                            $clasesEntidadesList=ArrayHelper::map($clsse,'id_clase_entidad','clase_entidad');
                            if($file){
                              echo $form->field($model, 'id_clase_entidad')->dropDownList($clasesEntidadesList,['prompt'=>'Seleccione la clase de entidad','disabled' => true]);  
                            }else{
                                echo $form->field($model, 'id_clase_entidad')->dropDownList($clasesEntidadesList,['prompt'=>'Seleccione la clase de entidad']);  
                             }
                                
                            
                            ?>
                            </div>
                          

                    </div>

                <div class="rows">
                    <div class="col-lg-12">
                        <div class="col-lg-12">
                            <?php 
                            if($file){
                                echo $form->field($model, 'objetivos_entidad')->textarea(['rows' => 3,'disabled' => true]);
                            }else{
                                 echo $form->field($model, 'objetivos_entidad')->textarea(['rows' => 3]);
                            }
                                

                             ?>
                        </div>
                        <div class="col-lg-8">
                              <?php 
                              if($file){
                                echo $form->field($model, 'fecha_estatutos')->textarea(['rows' => 1,'disabled' => true]);
                              }else{
                              echo $form->field($model, 'fecha_estatutos')->widget(
                                DatePicker::className(), [
                                    // inline too, not bad
                                     'inline' => false,
                                     // modify template for custom rendering
                                    //'template' => '<div class="well well-sm" style="background-color: #fff; width:250px">{input}</div>',
                                    'clientOptions' => [
                                        'autoclose' => true,
                                        'format' => 'yyyy-m-d'
                                    ]
                                 ]);
                                }
                            ?>

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

                        <div class="col-lg-6">
                            <?php 
                            if($file){
                                 echo $form->field($model, 'ubicacion_archivos_entidad')->textInput(['maxlength' => true,'disabled' => true]);
                            }else{
                                echo $form->field($model, 'ubicacion_archivos_entidad')->textInput(['maxlength' => true]);
                            }
                             ?>
                        </div>
                        <div class="col-lg-8">

                            <?php 
                            if($file){
                                echo $form->field($model, 'fecha_gaceta')->textInput(['maxlength' => true,'disabled' => true]);
                            }else{
                                echo $form->field($model, 'fecha_gaceta')->widget(
                                    DatePicker::className(), [
                                        // inline too, not bad
                                         'inline' => false,
                                         // modify template for custom rendering
                                        //'template' => '<div class="well well-sm" style="background-color: #fff; width:250px">{input}</div>',
                                        'clientOptions' => [
                                            'autoclose' => true,
                                            'format' => 'yyyy-m-d'
                                        ]
                                ]);
                                }
                            ?>

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

                        <div class="col-lg-10">
                            <?= $form->field($model, 'file')->widget(FileInput::classname(), [
                              'pluginOptions'=>[
                              'allowedFileExtensions'=>['doc','pdf','docx'],
                              'showUpload' => false,
                              ]
                            ]); ?>
                        <?php
                        $var = [ 1 => 'Activa', 2 => 'Inactiva',  3 => 'Observación'];
                        $periodo = [ 0=>'∞ INDEFINIDO',1 => '1 AÑO', 2 => '2 AÑOS',  3 => '3 AÑOS',4 =>'4 AÑOS',5=>'5 AÑOS',6=>'6 aÑOS',7=>'7 AÑOS',8=>'8 AÑOS',9=>'9 AÑOS',10=>'10 AÑOS'];
                        if($update ){
                        echo $form->field($model, 'estado_entidad')->dropDownList($var, ['prompt' => 'Seleccione el estado' ]);
                        }else {
                          echo $form->field($model, 'estado_entidad')->dropDownList($var, ['prompt' => 'Seleccione el estado','disabled' => true ]);
                        }

                        if($file){
                            echo $form->field($model, 'periodo_entidad')->dropDownList($periodo, ['prompt' => 'Seleccione el periodo de la entidad','disabled' => true ]);
                        }else{
                        echo $form->field($model, 'periodo_entidad')->dropDownList($periodo, ['prompt' => 'Seleccione el periodo de la entidad' ]);
                        }
                        ?>
                        </div>
                    </div>
                <center><div class="form-group">

                        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Crear') : Yii::t('app', 'Actualizar'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary','data' => [
                            'confirm' => '¿Usted esta seguro que desea realizar este proceso?',
                            'method' => 'post'],]) ?>

                </div></center>


                <?php ActiveForm::end(); ?>
                    </div>
                </div>
                <!-- form for the creation of the legal representative -->



                </div>
            </div>

        </div>

    </div>
