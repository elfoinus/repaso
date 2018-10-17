<?php

namespace app\controllers;

use Yii;
use app\models\Historial;
use app\models\Radicados;
use app\models\Entidades;
use app\models\Resoluciones;
use app\models\RadicadosSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\User;
use yii\filters\AccessControl;
use app\models\TipoTramite;
use Carbon\Carbon;
use yii\web\UploadedFile;
use mPDF;
use yii\data\ActiveDataProvider;
/**
 * RadicadosController implements the CRUD actions for Radicados model.
 */
class RadicadosController extends Controller
{
    /**
     * @inheritdoc
     */
     public function behaviors()
     {
     //Aqui se agregan los sitios que tendran restricción de acceso
     $only = [ 'index','create', 'update','view','reporte','samplepdf','tramitar','finalizado','rechazado'];
     return [
         'access' => [
             'class' => AccessControl::className(),
             'only' => $only,
             'rules' => [
                 [
                     'actions' => ['index','create', 'update','view','reporte','samplepdf','tramitar','finalizado','rechazado'],
                     'allow' => true,
                     'roles' => ['@'],
         'matchCallback' => function ($rule, $action) {
           $valid_roles = [User::ROL_USER,User::ROL_RADICADOR,User::ROL_REPARTIDOR,User::ROL_SUPERUSER];
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

    /**
     * Lists all Radicados models.
     * @return mixed
     */
    public function actionIndex()
    {
        $session = Yii::$app->session;
        $searchModel = new RadicadosSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if(isset(Yii::$app->user->identity->id_rol)&& Yii::$app->user->identity->id_rol == 3){
          $dataProvider->query->orderBy([ 'id_radicado'=>SORT_ASC]);
        }else{
          $dataProvider->query->orderBy([ 'id_radicado'=>SORT_DESC])->orderBy([ 'estado'=>SORT_ASC]);
        }
        if( isset(Yii::$app->user->identity->id_rol)&& Yii::$app->user->identity->id_rol == 2){
          $id =Yii::$app->user->identity->id;
          $dataProvider->query->andWhere(['id_usuario_tramita'=>$id]);
        }
        $anterior = $session->get('mensaje');
        if(isset($anterior)){
          //$dataProvider->query = $session->get('query');
          $dataProvider = $searchModel->search($session->get('request_query'));
          if(isset(Yii::$app->user->identity->id_rol)&& Yii::$app->user->identity->id_rol == 3){
            $dataProvider->query->orderBy([ 'id_radicado'=>SORT_ASC]);
          }else{
            $dataProvider->query->orderBy([ 'id_radicado'=>SORT_DESC])->orderBy([ 'estado'=>SORT_ASC]);
          }
          if( isset(Yii::$app->user->identity->id_rol)&& Yii::$app->user->identity->id_rol == 2){
            $id =Yii::$app->user->identity->id;
            $dataProvider->query->andWhere(['id_usuario_tramita'=>$id]);
          }
        }else{
        $session->set('query',$dataProvider->query);
        $session->set('request_query',Yii::$app->request->queryParams);
        }
        $entidades = Entidades::find()->asArray()->all();
        $mensaje = $session->get('mensaje');
        $session->remove('mensaje');
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'msg'=> $mensaje,
            'entidades' => $entidades,
        ]);
    }

    /**
     * Displays a single Radicados model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
      $session = Yii::$app->session;
      $x = $session->get('id');
      // nuevo
      $session->set('id_radicado',$id);
      $modelo = $this->findModel($id);
      $session->set('id_entidad',$modelo->id_entidad_radicado);
      //nuevo
      $radicado = Radicados::findOne($id);
      //$session->set($radicado,');
      if($x != 'x' && $radicado['estado'] != 3  && $radicado['estado'] != 4){
        $texto = $id." Tramite: ".$radicado->getTipoTramite();
        $session->set('id',$texto);
      }
      $msg = $session->get('msg');
      $session->set('msg',null);
   
        return $this->render('view', [
            'model' => $this->findModel($id),
            'msg'=> $msg,
        ]);
    }

    /**
     * Creates a new Radicados model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Radicados();
        //$tiempo = Carbon::now('America/Bogota');
        $model->fecha_creacion = Carbon::now('America/Bogota')->toDateTimeString();//$tiempo->toDateString();
        $model->estado = 1;
        $model->id_usuario_creacion = Yii::$app->user->identity->id;
        if ($model->load(Yii::$app->request->post()) && $model->save(false)) {
           $model->file = UploadedFile::getInstance($model, 'file');

            if( $model->file != null){

              if(file_exists($model->id_entidad_radicado)){
                $model->file->saveAs( $model->id_entidad_radicado.'/Radicado ' .$model->id_radicado. '.' . $model->file->extension);
              }else{
                $model->file->saveAs( 'radicados/Radicado '.$model->id_radicado.'.'. $model->file->extension);
              }

            }
            $model->file = null;
            return $this->redirect(['view', 'id' => $model->id_radicado]);
        } else {
          $entidades = Entidades::find()->asArray()->all();
          for ($i=0; $i < count($entidades) ; $i++) {
            $entidades[$i]['nombre_entidad'] = $entidades[$i]['nombre_entidad'].' - '.$entidades[$i]['personeria_year'].'-'.$entidades[$i]['personeria_n'];
          }
            return $this->render('create', [
                'model' => $model,
                'entidades' =>$entidades,
            ]);
        }
    }

    /**
     * Updates an existing Radicados model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
      $prueba = false;
      $model = $this->findModel($id);
      $mensaje ="No se puede modificar el radicado N° $model->id_radicado porque su estado es rechazado o finalizado";

      switch ($model->estado) {
        case 2: // se puede modificar si esta en tramite
          if(Yii::$app->user->identity->id == $model->id_usuario_tramita){
            $prueba = true;
          }else{
            $mensaje = "No se puede modificar el radicado N° $model->id_radicado porque se encuentra en tramite y no eres el funcionario asignado a este";
          }
          break;

        case 1: // se puede modificar si esta en reparto
          $prueba = true;
          break;

      }
      if(Yii::$app->user->identity->id_rol == 1){
        $prueba = true;
      }
      if($prueba){

        $oldModel = Radicados::findOne($id);
        if ($model->load(Yii::$app->request->post()) ) {

            if($model->estado == 3 ||$model->estado == 4 ){
              $model->save(false);
              $session = Yii::$app->session;
              $session->remove('id');
              $radicados = $session->get('radicados');
              $nradicados = count($radicados);
              for ($i=0; $i < $nradicados; $i++) {
                if($model->id_radicado == $radicados[$i]){
                  unset($radicados[$i]);
                }
              }

              if($radicados){
                $nuevo = array_values($radicados);
                $session->set('radicados',$nuevo);
              }else{
                $session->set('radicados',array());
              }


            }else {
              if($model->id_usuario_tramita > 0){
                $model->estado = 2;

                $model->save(false);
              }else{
                $model->save(false);
              }

            }
            $model->file = UploadedFile::getInstance($model, 'file');

             if( $model->file != null){

               if(file_exists($model->id_entidad_radicado)){
                 $model->file->saveAs( $model->id_entidad_radicado.'/Radicado ' .$model->id_radicado. '.' . $model->file->extension);
               }else{
                 $model->file->saveAs( 'radicados/Radicado '.$model->id_radicado.'.'. $model->file->extension);
               }

             }
             $model->file = null;

            if($model->estado == 2){
            $session = Yii::$app->session;
            $session->set('id','x');
            }
            $tiempo = Carbon::now('America/Bogota');
            if($model->id_tipo_tramite != $oldModel->id_tipo_tramite){
              $historial = new Historial();
              $historial->nombre_evento = "CAMBIO DE TIPO DE TRÁMITE RADICADO";
              $historial->id_tabla_modificada = $model->id_radicado;
              $historial->fecha_modificacion = $tiempo->toDateTimeString();
              $historial->nombre_campo_modificado = "id_tipo_tramite";
              $historial->valor_anterior_campo = $oldModel->id_tipo_tramite;
              $historial->valor_nuevo_campo = $model->id_tipo_tramite;
              $historial->id_usuario_modifica = Yii::$app->user->identity->id ;
              $historial->tabla_modificada = "RADICADOS";
              $historial->save(false);
            }

            if($model->id_entidad_radicado != $oldModel->id_entidad_radicado){
              $historial = new Historial();
              $historial->nombre_evento = "CAMBIO DE ENTIDAD RADICADO";
              $historial->id_tabla_modificada = $model->id_radicado;
              $historial->fecha_modificacion = $tiempo->toDateTimeString();
              $historial->nombre_campo_modificado = "id_entidad_radicado";
              $historial->valor_anterior_campo = $oldModel->id_entidad_radicado;
              $historial->valor_nuevo_campo = $model->id_entidad_radicado;
              $historial->id_usuario_modifica = Yii::$app->user->identity->id ;
              $historial->tabla_modificada = "RADICADOS";
              $historial->save(false);
            }

            if($model->descripcion != $oldModel->descripcion){
              $historial = new Historial();
              $historial->nombre_evento = "CAMBIO DE DESCRIPCIÓN RADICADO";
              $historial->id_tabla_modificada = $model->id_radicado;
              $historial->fecha_modificacion = $tiempo->toDateTimeString();
              $historial->nombre_campo_modificado = "descripcion";
              $historial->valor_anterior_campo = $oldModel->descripcion;
              $historial->valor_nuevo_campo = $model->descripcion;
              $historial->id_usuario_modifica = Yii::$app->user->identity->id ;
              $historial->tabla_modificada = "RADICADOS";
              $historial->save(false);
            }

            if($model->n_radicado_interno != $oldModel->n_radicado_interno){
              $historial = new Historial();
              $historial->nombre_evento = "CAMBIO DE N° RADICADO INTERNO";
              $historial->id_tabla_modificada = $model->id_radicado;
              $historial->fecha_modificacion = $tiempo->toDateTimeString();
              $historial->nombre_campo_modificado = "n_radicado_interno";
              $historial->valor_anterior_campo = $oldModel->n_radicado_interno;
              $historial->valor_nuevo_campo = $model->n_radicado_interno;
              $historial->id_usuario_modifica = Yii::$app->user->identity->id ;
              $historial->tabla_modificada = "RADICADOS";
              $historial->save(false);
            }

            if($model->estado != $oldModel->estado){
              $historial = new Historial();
              $historial->nombre_evento = "CAMBIO DE ESTADO RADICADO";
              $historial->id_tabla_modificada = $model->id_radicado;
              $historial->fecha_modificacion = $tiempo->toDateTimeString();
              $historial->nombre_campo_modificado = "estado";
              $historial->valor_anterior_campo = $oldModel->estado;
              $historial->valor_nuevo_campo = $model->estado;
              $historial->id_usuario_modifica = Yii::$app->user->identity->id ;
              $historial->tabla_modificada = "RADICADOS";
              $historial->save(false);
            }

            if($model->id_usuario_tramita != $oldModel->id_usuario_tramita){
              $historial = new Historial();
              $historial->nombre_evento = "CAMBIO DE USUARIO QUE TRAMITA EL RADICADO";
              $historial->id_tabla_modificada = $model->id_radicado;
              $historial->fecha_modificacion = $tiempo->toDateTimeString();
              $historial->nombre_campo_modificado = "id_usuario_tramita";
              $historial->valor_anterior_campo = $oldModel->id_usuario_tramita;
              $historial->valor_nuevo_campo = $model->id_usuario_tramita;
              $historial->id_usuario_modifica = Yii::$app->user->identity->id ;
              $historial->tabla_modificada = "RADICADOS";
              $historial->save(false);
            }

            if($model->sade != $oldModel->sade){
              $historial = new Historial();
              $historial->nombre_evento = "CAMBIO DE N° SADE";
              $historial->id_tabla_modificada = $model->id_radicado;
              $historial->fecha_modificacion = $tiempo->toDateTimeString();
              $historial->nombre_campo_modificado = "sade";
              $historial->valor_anterior_campo = $oldModel->sade;
              $historial->valor_nuevo_campo = $model->sade;
              $historial->id_usuario_modifica = Yii::$app->user->identity->id ;
              $historial->tabla_modificada = "RADICADOS";
              $historial->save(false);
            }

            if($model->ubicacion != $oldModel->ubicacion){
              $historial = new Historial();
              $historial->nombre_evento = "CAMBIO DE UBICACIÓN";
              $historial->id_tabla_modificada = $model->id_radicado;
              $historial->fecha_modificacion = $tiempo->toDateTimeString();
              $historial->nombre_campo_modificado = "ubicacion";
              $historial->valor_anterior_campo = $oldModel->ubicacion;
              $historial->valor_nuevo_campo = $model->ubicacion;
              $historial->id_usuario_modifica = Yii::$app->user->identity->id ;
              $historial->tabla_modificada = "RADICADOS";
              $historial->save(false);
            }

            return $this->redirect(['view', 'id' => $model->id_radicado]);
        } else {
          $entidades = Entidades::find()->asArray()->all();
          for ($i=0; $i < count($entidades) ; $i++) {
            $entidades[$i]['nombre_entidad'] = $entidades[$i]['nombre_entidad'].' - '.$entidades[$i]['personeria_year'].'-'.$entidades[$i]['personeria_n'];
          }
            return $this->render('update', [
                'model' => $model,
                'entidades' => $entidades,
            ]);
        }
      }else {
              $session = Yii::$app->session;
              $session->set('mensaje',$mensaje);
              return $this->redirect(['index',
              'searchModel' => null,
              'dataProvider' => null]);

      }

    }

    /**
     * Deletes an existing Radicados model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */


    /**
     * Finds the Radicados model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Radicados the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Radicados::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionHistorial($id)
    {
        $this->redirect(Yii::$app->request->baseUrl."?r=historial%2Findex3&id=".$id);
    }

    public function actionTramitar($id){

      $radicado = Radicados::findOne($id);
      $prueba = Radicados::find()->where(['and', ['id_radicado' =>$id ],[ 'or',['estado' => 3],['estado' => 4]]])->one();
      if(!$prueba){
      $session = Yii::$app->session;
      $x = $session->get('id');
      $array =  $session->get('radicados');
      settype($array,'array');
        if(count($array) == 0 && !$radicado){
          $radicado = array();
          array_push($radicado,$id);
          $session->set('radicados',$radicado);
        }else{
          $pregunta = true; // preguntar si el id ya esta en la variable de session
                            // si ya esta no agregarlo
          for ($i=0; $i <count($array) ; $i++) {
            if($id == $array[$i]){
              $pregunta = false;
            }
          }
          if($pregunta){
            array_push($array,$id);
          }
          $session->set('radicados',$array);
        }
      if((empty($radicado->id_entidad_radicado) && $radicado->id_tipo_tramite == 3) || ( !(empty($radicado->id_entidad_radicado)) && $radicado->id_tipo_tramite != 3))
      { //validar que tengan entidad si no es un tramite de reconocimiento de personeria

     
        switch ($radicado->id_tipo_tramite) {
          case 3:
            //Reconocimiento Personerías

            $this->redirect(Yii::$app->request->baseUrl."?r=entidades%2Fcreate");
            break;

          case 4:
            // Certificación Historica
        /*    if($radicado->id_motivo == 0){
              $entidades = Entidades::find()->asArray()->all();
              for ($i=0; $i < count($entidades) ; $i++) {
                $entidades[$i]['nombre_entidad'] = $entidades[$i]['nombre_entidad'].' - '.$entidades[$i]['personeria_year'].'-'.$entidades[$i]['personeria_n'];
              }
              return $this->render('update', [
                  'model' => $radicado,
                  'entidades' => $entidades,
                  'msg' => "Ingrese el Motivo de uso del Certificado y dirigase de nuevo a realizar el tramite"
              ]);
            }else{ */
            $this->redirect(Yii::$app->request->baseUrl."?r=certificados-historicos%2Fview&id=".$radicado->id_entidad_radicado);
            //}
            break;

          case 9:
            // Reforma de Estatutos e Inscripción de Dignatarios
            $session = Yii::$app->session;
            $session->set('radicado',$radicado->id_radicado);
            $this->redirect(Yii::$app->request->baseUrl."?r=entidades%2Fupdate&id=".$radicado->id_entidad_radicado);
            break;

            case 10:
              // Cancelacion de perosneria juridica (a peticion)
              $session = Yii::$app->session;
              $session->set('radicado',$radicado->id_radicado);
              //$this->redirect(Yii::$app->request->baseUrl."?r=entidades%2Fupdate&id=".$radicado->id_entidad_radicado);
              $entidad = Entidades::findOne($radicado->id_entidad_radicado);
              $oldModel = $entidad;
              $entidad['estado_entidad'] = 2; //inactivo la entidad :VV:V:VV:V:V:V
              $entidad->save(false);
              //creo la resolucion
              $model = $entidad;

              $ultima_resolucion = Resoluciones::findOne(Resoluciones::find()->max('id_resolucion'));
              $numero_resolucion = 1;

              $tiempo = Carbon::now('America/Bogota');
              $año = $tiempo->year;
              if($ultima_resolucion['ano_resolucion'] == $año){
                  $numero_resolucion = $ultima_resolucion['numero_resolucion'];
                  $numero_resolucion = $numero_resolucion + 1;

              }

              $historial = new Historial();
              $historial->nombre_evento = "CAMBIO DE ESTADO ENTIDAD";
              $historial->id_tabla_modificada = $model->id_entidad;
              $historial->fecha_modificacion = $tiempo->toDateTimeString();
              $historial->nombre_campo_modificado = "estado_entidad";
              $historial->valor_anterior_campo = $oldModel->estado_entidad;
              $historial->valor_nuevo_campo = $model->estado_entidad;
              $historial->id_usuario_modifica = Yii::$app->user->identity->id ;
              $historial->tabla_modificada = "ENTIDADES";
              $historial->save(false);

              if($entidad->estado_entidad == 2){
              $resolucion = new Resoluciones();
              $resolucion->id_tipo_resolucion = 5; // inserto el id correspondiente a cancelacion de personería jurídica
              $resolucion->nombre_entidad = $entidad->nombre_entidad;
              $resolucion->id_entidad = $entidad->id_entidad;
              $resolucion->id_historial = $historial->id_historial;
              $resolucion->numero_resolucion = $numero_resolucion;
              $resolucion->ano_resolucion = $año;
              $resolucion->id_radicado = $radicado->id_radicado;
              $resolucion->fecha_creacion = $tiempo->toDateString();
              $resolucion->save(false);
              }
              //Entidades::actionView($entidad->id_entidad);
              $this->redirect(Yii::$app->request->baseUrl."?r=entidades%2Fview&id=".$entidad->id_entidad);
              break;

          case 5:
            //Reforma de Estatutos y Cambio de Razón Social
            $session = Yii::$app->session;
            $session->set('radicado',$radicado->id_radicado);
            $this->redirect(Yii::$app->request->baseUrl."?r=entidades%2Fupdate&id=".$radicado->id_entidad_radicado);
            break;

          case 16:
            //Reforma de Estatutos
            $session = Yii::$app->session;
            $session->set('radicado',$radicado->id_radicado);
            $this->redirect(Yii::$app->request->baseUrl."?r=entidades%2Fupdate&id=".$radicado->id_entidad_radicado);
            break;

          case 11:
            //Inscripción de Dignatarios
            $session = Yii::$app->session;
            $session->set('radicado',$radicado->id_radicado);
            $this->redirect(Yii::$app->request->baseUrl."?r=dignatarios%2Findex1&id=".$radicado->id_entidad_radicado);
            break;



          case 15:
            // Certificado de Reconocimiento

            $this->redirect(Yii::$app->request->baseUrl."?r=certificados-existencia%2Fview&id=".$radicado->id_entidad_radicado);
            break;

          case 20:
            // Certificado de Reconocimiento

            $this->redirect(Yii::$app->request->baseUrl."?r=certificados-existencia%2Fview&id=".$radicado->id_entidad_radicado);
            break;

          case 17:
            // Certificado de proponentes

            $this->redirect(Yii::$app->request->baseUrl."?r=certificados-proponentes%2Fview&id=".$radicado->id_entidad_radicado);
            break;

          case 18:
            // Certificado de diganatarios
            $this->redirect(Yii::$app->request->baseUrl."?r=certificados-dignatarios%2Fview&id=".$radicado->id_entidad_radicado);
            break;

          case 19:
            // Certificado individual
            $this->redirect(Yii::$app->request->baseUrl."?r=certificados-individuales%2Fabout&id=".$radicado->id_entidad_radicado);
            break;

          case 21:
            //registro de libros
          $this->redirect(Yii::$app->request->baseUrl."?r=entidades%2Fupdate&id=".$radicado->id_entidad_radicado);
            break;
          }// fin de swith
        }// fin de if que valida que haya un entidad si no es un tramite de reconocimiento
          else{
            $mensaje = "No se puede realizar el trámite correspondiente porque no hay una entidad asociada al radicado";
            return $this->render('view', [
                'model' => $this->findModel($id),
                'mensaje' => $mensaje,
            ]);
          }
      }else{
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
      }
    }

    public function actionReporte(){
    /*
     if( isset(Yii::$app->user->identity->id_rol) && Yii::$app->user->identity->id_rol == 2 ){
          $radicados = Radicados::find()->where( ['id_usuario_tramita' => Yii::$app->user->identity->id] )->asArray()->all();

        }else{
          $radicados = Radicados::find()->asArray()->all();

        }
      */
      $session = Yii::$app->session;
      $consulta = $session->get('query');
      $provider = new ActiveDataProvider([
          'query' => $consulta,
          'pagination' => [
              'pageSize' => 0,
            ],
      ]);
      $radicados = $provider->getModels();
    //  $tramites = array();
    //  $usuarios = array();

      $html = "

          <title> Radicados </title>

          <table style="."width:100%".">
          <tr>
              <td width='150'>".utf8_decode('N° RADICADO')."</td>
              <td width='150'>".utf8_decode('N° SADE')."</td>
              <td width='150'>".utf8_decode('DESCRIPCIÓN')."</td>
              <td width='150'>".utf8_decode('TIPO TRÁMITE')."</td>
              <td width='150'>".utf8_decode('ESTADO TRÁMITE')."</td>
              <td width='150'>".'USUARIO QUE TRAMITA'."</td>
          </tr> ";

      for($i =0; $i < sizeof($radicados);$i++){
          $user = User::findOne($radicados[$i]['id_usuario_tramita']);
          $tramite = TipoTramite::findOne($radicados[$i]['id_tipo_tramite']);
          $estado ;
          switch ($radicados[$i]['estado']) {
            case 1:
              $estado = 'en Reparto';
              break;
            case 2:
              $estado = 'en Trámite';
              break;
            case 3:
              $estado = 'Finalizado';
              break;
            case 4:
              $estado = 'Rechazado';
              break;
          }
          $html =$html."
          <tr>
              <td width='150'>".$radicados[$i]['id_radicado']."</td>
              <td width='150'>".$radicados[$i]['sade']."</td>
              <td width='300'>".utf8_decode($radicados[$i]['descripcion'])."</td>
              <td width='300'>".utf8_decode($tramite['descripcion'])."</td>
              <td width='150'>".utf8_decode($estado)."</td>
              <td width='300'>".utf8_decode($user['nombre_funcionario'])."</td>

          </tr>
          ";

      }

      $html = $html."</table>";

      header("Content-Type:application/vnd.ms-excelxls");
      header("Content-disposition:attachment; filename=Reporte_radicados.xls");
      echo $html;
    }


  public function actionCreateMPDF(){
        //$mpdf = new mPDF(['format' => 'Legal']);
        $mpdf = new mPDF();

        $mpdf-> writeHTML($this->renderPartial('mpdf'));
        $mpdf->Output();
        exit;
    }


    public function actionSamplepdf() {

        $mpdf = new mPDF();

        //$mpdf = new mPDF;
        $mpdf->setHeader('<div style="width: 100%; height: 80px;">

        <img src="img/logo2.png" width = "200px" >

          </div>');
        $mpdf->setFooter('Página {PAGENO}'. '   Generado por software personería juridíca');

        //Marca de agua
        $mpdf->SetWatermarkText('ÉSTE DOCUMENTO NO TIENE VALIDEZ LEGAL');
        $mpdf->showWatermarkText = true;
        $mpdf->SetWatermarkImage('img/escudovalle.png');
        //$mpdf->SetWatermarkImage('../img/logo.png');
        //$mpdf->SetWatermarkImage('https://upload.wikimedia.org/wikipedia/commons/thumb/6/68/Escudo_del_Valle_del_Cauca.svg/240px-Escudo_del_Valle_del_Cauca.svg.png');
        $mpdf->showWatermarkImage = true;


        $mpdf->SetTitle('Radicados'); //Título

        $mpdf->AddPageByArray(array(
            'sheet-size' => 'Letter',
            'resetpagenum' => '1',
            'pagenumstyle' => '1',
        ));
        /*Texto, aqui se escriben las páginas*/
        $session = Yii::$app->session;
        $consulta = $session->get('query');
        $provider = new ActiveDataProvider([
            'query' => $consulta,
            'pagination' => [
                'pageSize' => 0,
              ],
        ]);

        $radicados = $provider->getModels();


      /*  if( isset(Yii::$app->user->identity->id_rol) && Yii::$app->user->identity->id_rol == 2 ){

          //$radicados = Radicados::find()->where( ['id_usuario_tramita' => Yii::$app->user->identity->id] )->asArray()->all();
          $provider = new ActiveDataProvider([
              'query' => $consulta,
          ]);
          $provider->query->where( ['id_usuario_tramita' => Yii::$app->user->identity->id] )->asArray()->all();
          $radicados = $provider->getModels();
        }else{
          //$radicados = Radicados::find()->asArray()->all();
          $provider = new ActiveDataProvider([
              'query' => $consulta,
          ]);
          $radicados = $provider->getModels();
        } */

        $html = "

    <style type="."text/css".">
        body {
          position: relative;
          width: 21cm;
          height: 29.7cm;
          margin: 0 auto;
          color: #001028;
          background: #FFFFFF;
          font-family: Arial, sans-serif;
          font-size: 16px;
          font-family: Arial;
        }

        table {
          width: 100%;
          border-collapse: collapse;
          border-spacing: 0;
          margin-bottom: 20px;
        }

        table tr:nth-child(2n-1) td {
          background: #F5F5F5;
        }

        table th,
        table td {
          text-align: center;
        }

        table th {
          padding-top: 80px;
          color: #5D6975;
          border-bottom: 1px solid #C1CED9;
          white-space: nowrap;
          font-weight: normal;}
        }

        table td {
          padding: 20px;
          text-align: center;
        }


        table td.unit,
        table td.qty,
        table td.total {
          font-size: 1.2em;
        }

    </style>

        <h1>Radicados</h1>

          <table style="."width:100%".">
               <thead>
                  <tr>
                      <th width='150'>".'N° RADICADO'."</th>
                      <th width='150'>".'N° SADE'."</th>
                      <th width='300'>".'TIPO TRÁMITE'."</th>
                      <th width='150'>".'ESTADO TRÁMITE'."</th>
                      <th width='150'>".'USUARIO TRAMITA'."</th>

                  </tr>
                </thead>";


          for($i =0; $i < sizeof($radicados);$i++){
            $user = User::findOne($radicados[$i]['id_usuario_tramita']);
            $tramite = TipoTramite::findOne($radicados[$i]['id_tipo_tramite']);
            $estado ;
          switch ($radicados[$i]['estado']) {
            case 1:

              $estado = 'en Reparto';
              break;
            case 2:
              $estado = 'en Trámite';
              break;
            case 3:
              $estado = 'Finalizado';
              break;
            case 4:
              $estado = 'Rechazado';
              break;
          }
            $html =$html."
            <tbody>
              <tr>
                  <td width='150'>".$radicados[$i]['id_radicado']."</td>
                  <td width='150'>".$radicados[$i]['sade']."</td>
                  <td width='300'>".$tramite['descripcion']."</td>
                  <td width='150'>".$estado."</td>
                  <td width='150'>".$user['nombre_funcionario']."</td>
              </tr>

            </tbody>
            ";

        }
        $html = $html."</table>";
        $mpdf->WriteHTML($html);

        /*Fin de las páginas*/

        /*Se da la salida del PDF*/
        //$mpdf->Output();
        $mpdf->Output('Reporte radicados.pdf','D'); //Para que descargue automaticamente
        exit;
    }

    private function downloadFile($dir, $file, $extensions=[])
    {
     //Si el directorio existe
     if (is_dir($dir))
     {
      //Ruta absoluta del archivo
      $path = $dir.$file;

      //Si el archivo existe
      if (is_file($path))
      {
       //Obtener información del archivo
       $file_info = pathinfo($path);
       //Obtener la extensión del archivo
       $extension = $file_info["extension"];

       if (is_array($extensions))
       {
        //Si el argumento $extensions es un array
        //Comprobar las extensiones permitidas
        foreach($extensions as $e)
        {
         //Si la extension es correcta
         if ($e === $extension)
         {
          //Procedemos a descargar el archivo
          // Definir headers
          $size = filesize($path);
          header("Content-Type: application/force-download");
          header("Content-Disposition: attachment; filename=$file");
          header("Content-Transfer-Encoding: binary");
          header("Content-Length: " . $size);
          // Descargar archivo
          readfile($path);
          //Correcto
          return true;
         }
        }
       }

      }
     }
     //Ha ocurrido un error al descargar el archivo
     return false;
    }

    public function actionDownload()
    {
      $session = Yii::$app->session;
      $id = $session->get('id_radicado');

     if (Yii::$app->request->get("file"))
     {
      //Si el archivo no se ha podido descargar
      //downloadFile($dir, $file, $extensions=[])


        if (!$this->downloadFile("radicados/", Yii::$app->request->get('file'), ["pdf", "docx","doc"]))
        {
         //Mensaje flash para mostrar el error


        }


     }

     return $this->render('view', [
         'model' => $this->findModel($id),
     ]);
    }

    public function actionDownload1()
    {
      $session = Yii::$app->session;
      $id = $session->get('id_entidad');
      $idRadicado = $session->get('id_radicado');
      $radicado = Radicados::findOne($idRadicado);
      $entidad = Entidades::findOne($id);
     if (Yii::$app->request->get("file"))
     {
      //Si el archivo no se ha podido descargar
      //downloadFile($dir, $file, $extensions=[])


        if (!$this->downloadFile($entidad['id_entidad']."/", "Radicado $radicado->id_radicado", ["pdf", "docx","doc"]) )
        {
         //Mensaje flash para mostrar el error

         Yii::$app->session->setFlash("error");
        }



     }

     return $this->render('view', [
         'model' => $radicado,
     ]);
    }

    public function actionFinalizado(){

        if(Yii::$app->request->post('id')){
          $id = Yii::$app->request->post('id');

        $radicado = Radicados::findOne($id);
        if($radicado){
          $tiempo = Carbon::now('America/Bogota');
          $historial = new Historial();
          $historial->nombre_evento = "CAMBIO DE ESTADO RADICADO";
          $historial->id_tabla_modificada = $radicado->id_radicado;
          $historial->fecha_modificacion = $tiempo->toDateTimeString();
          $historial->nombre_campo_modificado = "estado";
          $historial->valor_anterior_campo = $radicado->estado;
          $historial->valor_nuevo_campo = 3;
          $historial->id_usuario_modifica = Yii::$app->user->identity->id ;
          $historial->tabla_modificada = "RADICADOS";
          $historial->save(false);
          $radicado->estado = 3;
          $radicado->save(false);

          $session = Yii::$app->session;
          $radicados = $session->get('radicados');
          $nradicados = count($radicados);
          for ($i=0; $i < $nradicados; $i++) {
            if($radicado->id_radicado == $radicados[$i]){
              unset($radicados[$i]);
            }
          }

          if($radicados){
            $nuevo = array_values($radicados);
            $session->set('radicados',$nuevo);
          }else{
            $session->set('radicados',array());
          }
        }
        }
    }

    public function actionRechazado(){
      if(Yii::$app->request->post('id')){
        $id = Yii::$app->request->post('id');
        $radicado = Radicados::findOne($id);
        if($radicado){
          $tiempo = Carbon::now('America/Bogota');
          $historial = new Historial();
          $historial->nombre_evento = "CAMBIO DE ESTADO RADICADO";
          $historial->id_tabla_modificada = $radicado->id_radicado;
          $historial->fecha_modificacion = $tiempo->toDateTimeString();
          $historial->nombre_campo_modificado = "estado";
          $historial->valor_anterior_campo = $radicado->estado;
          $historial->valor_nuevo_campo = 4;
          $historial->id_usuario_modifica = Yii::$app->user->identity->id ;
          $historial->tabla_modificada = "RADICADOS";
          $historial->save(false);
          $radicado->estado = 4;
          $radicado->save(false);

          $session = Yii::$app->session;
          $radicados = $session->get('radicados');
          $nradicados = count($radicados);
          for ($i=0; $i < $nradicados; $i++) {
            if($radicado->id_radicado == $radicados[$i]){
              unset($radicados[$i]);
            }
          }

          if($radicados){
            $nuevo = array_values($radicados);
            $session->set('radicados',$nuevo);
          }else{
            $session->set('radicados',array());
          }
        }
        }
    }

}
