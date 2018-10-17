<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Radicados */

$this->title = 'Crear Radicado';
$this->params['breadcrumbs'][] = ['label' => 'Radicados', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
if(!isset($msg)){
  $msg = null;
}
?>
<div class="radicados-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'update' => false,
        'msg'=> $msg,
        'entidades' => $entidades,
    ]) ?>

</div>
