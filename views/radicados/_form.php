<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\TipoTramite;
use app\models\User;
use app\models\Entidades;
use yii\helpers\ArrayHelper;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use kartik\file\FileInput;
use app\models\MotivoCertificado;
/* @var $this yii\web\View */
/* @var $model app\models\Radicados */
/* @var $form yii\widgets\ActiveForm */

// 'disabled' => true
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
<?php $form = ActiveForm::begin(); ?>
<div class="radicados-form">
  <div class="row">
    <div class="col-lg-7">
      <div class="box box-primary">
        <div class="col-lg-8">




          <?php
            if(Yii::$app->user->identity->id_rol == 4 || Yii::$app->user->identity->id_rol == 2 ){
              $tramite = TipoTramite::find()->asArray()->all();
              $tipoList=ArrayHelper::map($tramite,'id_tipo_tramite','descripcion');
              echo $form->field($model, 'id_tipo_tramite')->dropDownList($tipoList,['prompt'=>'Seleccione el tipo de tramite','disabled'=> true,
                'onchange' => 'ocultar()'
              ]);
            }else{
              $tramite = TipoTramite::find()->asArray()->all();
              $tipoList=ArrayHelper::map($tramite,'id_tipo_tramite','descripcion');
              echo $form->field($model, 'id_tipo_tramite')->dropDownList($tipoList,['prompt'=>'Seleccione el tipo de tramite',
                'onchange' => 'ocultar()'
              ]); //<?= $form->field($model, 'id_entidad_radicado')->textInput()
              }
            ?>
        </div>
      <div class = "col-lg-5">
       <?php
       if(Yii::$app->user->identity->id_rol == 4 || Yii::$app->user->identity->id_rol == 2){
          echo $form->field($model, 'n_radicado_interno')->textInput(['disabled' => true]);
        }else{
          echo $form->field($model, 'n_radicado_interno')->textInput();
        } ?>
      </div>
      <?php Pjax::begin(); ?>

        <div id="prueba" class="col-lg-8">


          <?php  // id = select2-radicados-id_entidad_radicado-container

          if(Yii::$app->user->identity->id_rol == 4 || Yii::$app->user->identity->id_rol == 2){
            
            echo $form->field($model, 'id_entidad_radicado')->widget(Select2::classname(), ['disabled' => true,
            'data' => ArrayHelper::map($entidades,'id_entidad','nombre_entidad'),
            'language' => 'es',
            'options' => [
              'placeholder' => 'Seleccione una Entidad',

              ],
            'pluginOptions' => [
                'allowClear' => true,
                'minimumInputLength' => 4,
            ],
            ]);
          }else{
            if($model->id_tipo_tramite !=3){
                echo $form->field($model, 'id_entidad_radicado')->widget(Select2::classname(), [
                'data' => ArrayHelper::map($entidades,'id_entidad','nombre_entidad'),
                'language' => 'es',
                'options' => [
                  'placeholder' => 'Seleccione una Entidad',

                  ],
                'pluginOptions' => [
                    'allowClear' => true,
                    'minimumInputLength' => 4,
                ],
                ]);
              }
            }
        ?>
        </div>

      <?php Pjax::end(); ?>
      <div class="col-lg-8">
      <?php
       if(Yii::$app->user->identity->id_rol == 4 ){
          echo $form->field($model, 'descripcion')->textarea(['rows' => 3, 'disabled' => true]);
       }else{
          echo $form->field($model, 'descripcion')->textarea(['rows' => 3]);
       }
       ?>
      </div>

      <div class="col-lg-6">
<?php
  if(Yii::$app->user->identity->id_rol == 2){
      echo $form->field($model, "estado")->dropDownList([2 =>"Tramite",3=>"Finalizado",4=>"Devolución"],["prompt"=>"Seleccione el estado"]);
      }
  if(Yii::$app->user->identity->id_rol == 1){
      echo $form->field($model, "estado")->dropDownList([1 =>"Reparto",2 =>"Tramite",3=>"Finalizado",4=>"Devolución"],["prompt"=>"Seleccione el estado"]);
      }
      if( ($model->id_tipo_tramite == 4 || $model->id_tipo_tramite == 15 || $model->id_tipo_tramite == 17 || $model->id_tipo_tramite == 18|| $model->id_tipo_tramite == 19) &&
       (Yii::$app->user->identity->id_rol == 2 || Yii::$app->user->identity->id_rol == 1 || Yii::$app->user->identity->id_rol == 4 )){
          $motivos = MotivoCertificado::find()->asArray()->all();
          $motivoslist=ArrayHelper::map($motivos,'id_motivo','nombre_motivo');
          echo $form->field($model, "id_motivo")->dropDownList($motivoslist,["prompt"=>"Seleccione el estado"]);
          //echo print_r($model);
        }

?>
    </div>
  <div class="col-lg-8">
  <?php
      if(Yii::$app->user->identity->id_rol == 4 ||  Yii::$app->user->identity->id_rol == 1 ){
        $user = User::find()->where(['id_rol'=>2])->asArray()->all();
        $tipoList=ArrayHelper::map($user,'id','nombre_funcionario');
        if($model->estado != 3 && $model->estado != 4){
        echo $form->field($model, 'id_usuario_tramita')->dropDownList($tipoList,['prompt'=>'Seleccione el usuario']);
        }
        //echo $form->field($model, 'id_usuario_tramita')->textInput();
        }
  ?>
  <?= $form->field($model, 'file')->widget(FileInput::classname(), [
    'pluginOptions'=>[
    'allowedFileExtensions'=>['doc','pdf','docx'],
    'showUpload' => false,
    ]
  ]); ?>
  </div>
  <div class="col-lg-8">
    <div class="col-lg-6">
        <?php
         if(Yii::$app->user->identity->id_rol == 4 || Yii::$app->user->identity->id_rol == 2){
          echo $form->field($model, 'sade')->textInput(['disabled' => true]);
          }else {
          echo $form->field($model, 'sade')->textInput();
          }
         ?>
      </div>
      <div class="col-lg-6">
        <?php
        if(Yii::$app->user->identity->id_rol == 4 || Yii::$app->user->identity->id_rol == 2){
        echo $form->field($model, 'ubicacion')->textInput(['disabled' => true]);
        }else{
        echo $form->field($model, 'ubicacion')->textInput();
        }
        ?>
      </div>
    </div>
    <div class="col-lg-8">
      <div class="form-group">
          <center><?= Html::submitButton($model->isNewRecord ? 'Crear' : 'Actualizar', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary','data' => [
                        'confirm' => '¿Usted esta seguro que desea realizar este proceso?',
                        'method' => 'post'],]) ?></center>
      </div>
      </div>
    </div>

    <?php ActiveForm::end();  ?>
    </div> 
</div>

<script>


     function ocultar() {
        var elemento = document.getElementById("radicados-id_tipo_tramite");
        var valor = document.getElementById("radicados-id_tipo_tramite").value;

        //alert(valor);
        $.ajax({
        //url: "/radicados/prueba",
        dataType:"html",
        //data : valor,
        type: "post",
        success: function(data){
          if(valor == 3){
            $('#prueba').hide(true);
          }else{
            $('#prueba').show(true);
          }
        }
    });
    }

</script>
