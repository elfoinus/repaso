<?php

use yii\helpers\Html;
use app\models\Radicados;
/* @var $this yii\web\View */
/* @var $model app\models\Entidades */

$this->title = 'Actualizar Entidad: ' . $model->id_entidad;
$this->params['breadcrumbs'][] = ['label' => 'Entidades', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id_entidad, 'url' => ['view', 'id' => $model->id_entidad]];
$this->params['breadcrumbs'][] = 'Update';
if(!isset($msg)){
  $msg = null;
}
$session = Yii::$app->session;
$x = $session->get('id_radicado');
$radicado = Radicados::findOne($x);
if ($radicado && $radicado->id_tipo_tramite == 21) {
	$file = true;
	$update = false;
}else{
	$file = false;
	$update = true;
}
?>
<div class="entidades-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?=
	
	    	$this->render('_form', [
	        'model' => $model,
	        'update' => $update,
	        'msg'=> $msg,
	        'file' => $file,
	    	])
    	
     ?>
    

</div>
