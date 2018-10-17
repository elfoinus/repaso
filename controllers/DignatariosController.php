<?php

namespace app\controllers;

use Yii;
use app\models\Dignatarios;
use app\models\DignatariosSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use Carbon\Carbon;
use app\models\User;
use app\models\Cargos;
use app\models\Historial;
use app\models\Entidades;
use app\models\Resoluciones;
use app\models\GruposCargos;
use app\models\Radicados;
use DateTime;

/**
 * DignatariosController implements the CRUD actions for Dignatarios model.
 */
class DignatariosController extends Controller
{
    /**
     * @inheritdoc
     */
     public function behaviors()
   {
   //Aqui se agregan los sitios que tendran restricción de acceso
   $only = [ 'create', 'update','create1'];
   return [
         'access' => [
             'class' => AccessControl::className(),
             'only' => $only,
             'rules' => [
                 [
                     'actions' => ['create', 'update','create1'],
                     'allow' => true,
                     'roles' => ['@'],
         'matchCallback' => function ($rule, $action) {
           $valid_roles = [User::ROL_USER,User::ROL_SUPERUSER];
           return User::roleInArray($valid_roles) && User::isActive();
                   }
                 ],
             ],
         ],
   //End sitios

         'verbs' => [
             'class' => VerbFilter::className(),
             'actions' => [
                 'delete' => ['POST'],
             ],
         ],
     ];
   }

   public function actionHistorial($id)
   {
       $this->redirect(Yii::$app->request->baseUrl."?r=historial%2Findex2&id=".$id);
   }

    /**
     * Lists all Dignatarios models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new DignatariosSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $session = Yii::$app->session;
       //$rol = Yii::$app->user->identity->id_rol;

        if(  isset(Yii::$app->user->identity)&& (Yii::$app->user->identity->id_rol == 1 || Yii::$app->user->identity->id_rol == 2)   ){
          $session->set('editar',true);
        }else{
          $session->set('editar',false);
        }


        $id = $session->get('id_entidad');
        $cargos =Cargos::find()->all();
        $gcargos = GruposCargos::find()->all();
        $dataProvider->query->andWhere(['id_entidad'=>$id]);
        $titulo = Entidades::findOne($id);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'titulo' => $titulo['nombre_entidad'],
            'cargos' => $cargos,
            'gcargos' => $gcargos,

        ]);
    }

    public function actionIndex1($id)   // funcion llamada por el entidadescontroller para retornar la vista con los dignatarios desde el boton de usuarios
    {
        $searchModel = new DignatariosSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['id_entidad'=>$id]);

        // VERIFICA SI LOS DIGANTARIOS SE ENCUENTRAN DENTRO DEL INTERVALO DE SU PERIODO DE LO CONTRARIO LOS DESACTIVA
        $dignatarios = Dignatarios::find()->where(['id_entidad' => $id])->asArray()->all();
        $tiempo = Carbon::now('America/Bogota');

        $now = new DateTime($tiempo->toDateString());

        foreach ($dignatarios as $key) {

          $dignatario = Dignatarios::findOne($key['id_dignatario']);
          $fin = new DateTime($dignatario['fin_periodo']);
          $interval = $fin->diff($now);
          $a= $interval->format('%R%a'); // intervalo de tiempo entre el fecha fin y fecha actual
          if($a > 0){
            $dignatario->estado = 0;
            $dignatario->save();
          }
        }
        //print_r($dignatarios);
        //
        $cargos =Cargos::find()->all();
        $gcargos = GruposCargos::find()->all();
        $titulo = Entidades::findOne($id);
        $session = Yii::$app->session;
        $session->set('id_entidad',$id);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'titulo' => $titulo['nombre_entidad'],
            'cargos' => $cargos,
            'gcargos' => $gcargos,
        ]);
    }

    /**
     * Displays a single Dignatarios model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Dignatarios model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
      $session = Yii::$app->session;
      $id1 = $session->get('id_radicado');
      $id = $session->get('id_entidad');
      $radicado = Radicados::findOne($id1);
      if((Yii::$app->user->identity->id_rol == 1 || Yii::$app->user->identity->id_rol == 2) && $radicado ){
      $model = new Dignatarios();
      $model->id_entidad = $id;

      $tiempo = Carbon::now('America/Bogota');
      //$tiempo = Carbon::createFromDate(2017,11,21,'America/Bogota');  // cambiar el año para probar si funciona :V
      $año = $tiempo->year;
      //$año = 2018; // cambiar el año para probar si funciona lo del incrementable de las resoluciones
      $ultima_resolucion = Resoluciones::findOne(Resoluciones::find()->max('id_resolucion'));
      $numero_resolucion = 1;

      if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
          Yii::$app->response->format = 'json';
          return ActiveForm::validate($model);
        }

    if($ultima_resolucion['ano_resolucion'] == $año){
          $numero_resolucion = $ultima_resolucion['numero_resolucion'];
          $numero_resolucion = $numero_resolucion + 1;
      }
      $model->fecha_ingreso = $tiempo->toDateString();
      if ($model->load(Yii::$app->request->post())) {
        $tiempo = Carbon::now('America/Bogota');
        $fin = new DateTime($model['fin_periodo']);
        $now = new Datetime($tiempo->toDateString());
        $interval = $now->diff($fin);
        $a= $interval->format('%R%a');
        $model->estado = 1;
        $session = Yii::$app->session;
        $repre = $session->get('repre');
        $representante = false;
        if($repre){
          $model->id_cargo = 1;
          $representante = true;
        }
        if($a > 0 && $model->save()){


          $historial = new Historial();

          $historial->nombre_evento = "CREACIÓN DE DIGNATARIO";
          $historial->id_tabla_modificada = $model->id_dignatario;
          $historial->fecha_modificacion = $tiempo->toDateTimeString();
          $historial->id_usuario_modifica = Yii::$app->user->identity->id ;
          $historial->tabla_modificada = "DIGNATARIOS";
          $historial->save(false);

          $id_entidad = $model['id_entidad']; // id entidad del digantario que se creo :v :v :v :v
        $resoluci = Resoluciones::find()->where(['and',['id_tipo_resolucion' => 4],['id_entidad'=>$id_entidad],['fecha_creacion' => $tiempo->toDateString()]])->all();
          //$resoluci = Resoluciones::find()->where(['and',['id_tipo_resolucion' => 4],['id_entidad'=>$id_entidad],['fecha_creacion' => "2018-04-02"]])->all();
        $resolucio = Resoluciones::find()->where(['and',['id_tipo_resolucion' => 1],['id_entidad'=>$id_entidad],['fecha_creacion' => $tiempo->toDateString()]])->all();
        //  $resolucio = Resoluciones::find()->where(['and',['id_tipo_resolucion' => 1],['id_entidad'=>$id_entidad],['fecha_creacion' => "2018-04-02"]])->all();
          if(empty($resoluci))
          {
              $resoluci = false;
          }else {
            $resoluci = true;
          }

          if(empty($resolucio))
          {
              $resolucio = false;
          }else {
            $resolucio = true;
          }


          if ( $resoluci == false && $resolucio == false ){
            $nombre_entidad = Entidades::findOne($id_entidad);
            $resolucion = new Resoluciones();
            $resolucion->id_tipo_resolucion = 4; // inserto el id correspondiente a reconocimiento de personería jurídica
            $resolucion->nombre_entidad = $nombre_entidad['nombre_entidad'];
            $resolucion->id_entidad = $id_entidad;
            $resolucion->id_historial = $historial->id_historial;
            $resolucion->numero_resolucion = $numero_resolucion;
            $resolucion->ano_resolucion = $año;
            $resolucion->id_radicado = $radicado->id_radicado;
            $resolucion->fecha_creacion = $tiempo->toDateString();
            $resolucion->save(false);
          }
          if($representante){
            //redirgir a ventana de view de la entidad del representante :v :v
            $session = Yii::$app->session;
            $repre = $session->set('repre',false);
            $this->redirect(Yii::$app->request->baseUrl."?r=entidades%2Fview&id=".$model->id_entidad);

          }else{
            return $this->redirect(['view', 'id' => $model->id_dignatario]);
          }

        }else {
          return $this->render('create', [
              'model' => $model,'mensaje' => '<script type="text/javascript">alert("PERIODOS NO COINCIDEN");</script>',
          ]);
        }
      } else {
          return $this->render('create', [
              'model' => $model,
          ]);
      }
    }else{
        $searchModel = new DignatariosSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $session = Yii::$app->session;
        if(Yii::$app->user->identity->id_rol == 1 || Yii::$app->user->identity->id_rol == 2 ){
          $session->set('editar',true);
        }else{
          $session->set('editar',false);
        }

        $id = $session->get('id_entidad');

        $dataProvider->query->andWhere(['id_entidad'=>$id]);
        $titulo = Entidades::findOne($id);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'titulo' => $titulo['nombre_entidad'],
            'msg' => "No puede realizar esta operación sin un radicado correspondiente",
        ]);

      }
    }

    public function actionBuscar(){
      if(Yii::$app->request->post('dignatario')){
        $cedula = Yii::$app->request->post('dignatario');

        $model = Dignatarios::find()->where(['cedula_dignatario' => $cedula])->one();
        if(!empty($model)){
        $respuesta = $model->nombre_dignatario.",".$model->id_municipio_expedicion;
        echo json_encode("$respuesta");
        }

      }
    }
    /**
     * Updates an existing Dignatarios model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
      $session = Yii::$app->session;
      $id1 = $session->get('id_radicado');
      $radicado = Radicados::findOne($id1);
      if(Yii::$app->user->identity->id_rol == 1 || (Yii::$app->user->identity->id_rol == 2 && $radicado) ){
        $model = $this->findModel($id);
        $oldModel = Dignatarios::findOne($id);
        if ($model->load(Yii::$app->request->post())) {
            $tiempo = Carbon::now('America/Bogota');
            $fin = new DateTime($model['fin_periodo']);
            $inicio = new Datetime($model['inicio_periodo']);
            $interval = $fin->diff($inicio);
            $a= $interval->format('%R%a');
            if($a < 0){
              $model->save();
              if($model->cedula_dignatario != $oldModel->cedula_dignatario){
                $historial = new Historial();
                $historial->nombre_evento = "CAMBIO DE CÉDULA DIGNATARIO";
                $historial->id_tabla_modificada = $model->id_dignatario;
                $historial->fecha_modificacion = $tiempo->toDateTimeString();
                $historial->nombre_campo_modificado = "cedula_dignatario";
                $historial->valor_anterior_campo = $oldModel->cedula_dignatario;
                $historial->valor_nuevo_campo = $model->cedula_dignatario;
                $historial->id_usuario_modifica = Yii::$app->user->identity->id ;
                $historial->tabla_modificada = "DIGNATARIOS";
                $historial->save(false);
              }

              if($model->nombre_dignatario != $oldModel->nombre_dignatario){
                $historial = new Historial();
                $historial->nombre_evento = "CAMBIO DE NOMBRE DIGNATARIO";
                $historial->id_tabla_modificada = $model->id_dignatario;
                $historial->fecha_modificacion = $tiempo->toDateTimeString();
                $historial->nombre_campo_modificado = "nombre_dignatario";
                $historial->valor_anterior_campo = $oldModel->nombre_dignatario;
                $historial->valor_nuevo_campo = $model->nombre_dignatario;
                $historial->id_usuario_modifica = Yii::$app->user->identity->id ;
                $historial->tabla_modificada = "DIGNATARIOS";
                $historial->save(false);
              }

              if($model->estado != $oldModel->estado){
                $historial = new Historial();
                $historial->nombre_evento = "CAMBIO DE ESTADO DIGNATARIO";
                $historial->id_tabla_modificada = $model->id_dignatario;
                $historial->fecha_modificacion = $tiempo->toDateTimeString();
                $historial->nombre_campo_modificado = "estado";
                $historial->valor_anterior_campo = $oldModel->estado;
                $historial->valor_nuevo_campo = $model->estado;
                $historial->id_usuario_modifica = Yii::$app->user->identity->id ;
                $historial->tabla_modificada = "DIGNATARIOS";
                $historial->save(false);
              }

              if($model->id_municipio_expedicion != $oldModel->id_municipio_expedicion){
                $historial = new Historial();
                $historial->nombre_evento = "CAMBIO DE MUNICIPIO EXPEDICIÓN DIGNATARIO";
                $historial->id_tabla_modificada = $model->id_dignatario;
                $historial->fecha_modificacion = $tiempo->toDateTimeString();
                $historial->nombre_campo_modificado = "id_municipio_expedicion";
                $historial->valor_anterior_campo = $oldModel->id_municipio_expedicion;
                $historial->valor_nuevo_campo = $model->id_municipio_expedicion;
                $historial->id_usuario_modifica = Yii::$app->user->identity->id ;
                $historial->tabla_modificada = "DIGNATARIOS";
                $historial->save(false);
              }

              if($model->fecha_ingreso != $oldModel->fecha_ingreso){
                $historial = new Historial();
                $historial->nombre_evento = "CAMBIO DE FECHA INGRESO DIGNATARIO";
                $historial->id_tabla_modificada = $model->id_dignatario;
                $historial->fecha_modificacion = $tiempo->toDateTimeString();
                $historial->nombre_campo_modificado = "fecha_ingreso";
                $historial->valor_anterior_campo = $oldModel->fecha_ingreso;
                $historial->valor_nuevo_campo = $model->fecha_ingreso;
                $historial->id_usuario_modifica = Yii::$app->user->identity->id ;
                $historial->tabla_modificada = "DIGNATARIOS";
                $historial->save(false);
              }

              if($model->id_entidad != $oldModel->id_entidad){
                $historial = new Historial();
                $historial->nombre_evento = "CAMBIO DE ENTIDAD DIGNATARIO";
                $historial->id_tabla_modificada = $model->id_dignatario;
                $historial->fecha_modificacion = $tiempo->toDateTimeString();
                $historial->nombre_campo_modificado = "id_entidad";
                $historial->valor_anterior_campo = $oldModel->id_entidad;
                $historial->valor_nuevo_campo = $model->id_entidad;
                $historial->id_usuario_modifica = Yii::$app->user->identity->id ;
                $historial->tabla_modificada = "DIGNATARIOS";
                $historial->save(false);
              }

              if($model->id_cargo != $oldModel->id_cargo){
                $historial = new Historial();
                $historial->nombre_evento = "CAMBIO DE CARGO DIGNATARIO";
                $historial->id_tabla_modificada = $model->id_dignatario;
                $historial->fecha_modificacion = $tiempo->toDateTimeString();
                $historial->nombre_campo_modificado = "id_cargo";
                $historial->valor_anterior_campo = $oldModel->id_cargo;
                $historial->valor_nuevo_campo = $model->id_cargo;
                $historial->id_usuario_modifica = Yii::$app->user->identity->id ;
                $historial->tabla_modificada = "DIGNATARIOS";
                $historial->save(false);
              }

              if($model->id_grupo_cargos != $oldModel->id_grupo_cargos){
                $historial = new Historial();
                $historial->nombre_evento = "CAMBIO DE GRUPO CARGOS DIGNATARIO";
                $historial->id_tabla_modificada = $model->id_dignatario;
                $historial->fecha_modificacion = $tiempo->toDateTimeString();
                $historial->nombre_campo_modificado = "id_grupo_cargos";
                $historial->valor_anterior_campo = $oldModel->id_grupo_cargos;
                $historial->valor_nuevo_campo = $model->id_grupo_cargos;
                $historial->id_usuario_modifica = Yii::$app->user->identity->id ;
                $historial->tabla_modificada = "DIGNATARIOS";
                $historial->save(false);
              }

              if($model->inicio_periodo != $oldModel->inicio_periodo){
                $historial = new Historial();
                $historial->nombre_evento = "CAMBIO DE FECHA INICIO PERIODO DIGNATARIO";
                $historial->id_tabla_modificada = $model->id_dignatario;
                $historial->fecha_modificacion = $tiempo->toDateTimeString();
                $historial->nombre_campo_modificado = "inicio_periodo";
                $historial->valor_anterior_campo = $oldModel->inicio_periodo;
                $historial->valor_nuevo_campo = $model->inicio_periodo;
                $historial->id_usuario_modifica = Yii::$app->user->identity->id ;
                $historial->tabla_modificada = "DIGNATARIOS";
                $historial->save(false);
              }


              if($model->fin_periodo != $oldModel->fin_periodo){
                $historial = new Historial();
                $historial->nombre_evento = "CAMBIO DE FECHA FIN PERIODO DIGNATARIO";
                $historial->id_tabla_modificada = $model->id_dignatario;
                $historial->fecha_modificacion = $tiempo->toDateTimeString();
                $historial->nombre_campo_modificado = "fin_periodo";
                $historial->valor_anterior_campo = $oldModel->fin_periodo;
                $historial->valor_nuevo_campo = $model->fin_periodo;
                $historial->id_usuario_modifica = Yii::$app->user->identity->id ;
                $historial->tabla_modificada = "DIGNATARIOS";
                $historial->save(false);
              }

              if($model->tarjeta_profesiona != $oldModel->tarjeta_profesiona){
                $historial = new Historial();
                $historial->nombre_evento = "CAMBIO DE N° TARJETA PROFESIONAL DIGNATARIO";
                $historial->id_tabla_modificada = $model->id_dignatario;
                $historial->fecha_modificacion = $tiempo->toDateTimeString();
                $historial->nombre_campo_modificado = "tarjeta_profesional";
                $historial->valor_anterior_campo = $oldModel->tarjeta_profesiona;
                $historial->valor_nuevo_campo = $model->tarjeta_profesiona;
                $historial->id_usuario_modifica = Yii::$app->user->identity->id ;
                $historial->tabla_modificada = "DIGNATARIOS";
                $historial->save(false);
              }



              return $this->redirect(['view', 'id' => $model->id_dignatario]);
            }else{
              return $this->render('create', [
                  'model' => $model,'mensaje' => '<script type="text/javascript">alert("PERIODOS NO COINCIDEN");</script>',
              ]);
            }

        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }

      }else{
          $searchModel = new DignatariosSearch();
          $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
          $session = Yii::$app->session;
          if(Yii::$app->user->identity->id_rol == 1 || Yii::$app->user->identity->id_rol == 2 ){
            $session->set('editar',true);
          }else{
            $session->set('editar',false);
          }

          $id = $session->get('id_entidad');

          $dataProvider->query->andWhere(['id_entidad'=>$id]);
          $titulo = Entidades::findOne($id);
          return $this->render('index', [
              'searchModel' => $searchModel,
              'dataProvider' => $dataProvider,
              'titulo' => $titulo['nombre_entidad'],
              'msg' => "No puede realizar esta operación sin un radicado correspondiente",
          ]);

        }
    }

    /**
     * Deletes an existing Dignatarios model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */


    /**
     * Finds the Dignatarios model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Dignatarios the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Dignatarios::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
