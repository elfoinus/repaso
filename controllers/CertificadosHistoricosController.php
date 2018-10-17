<?php

namespace app\controllers;

namespace app\controllers;

use Yii;
use app\models\Entidades;
use app\models\EntidadesSearch;
use app\models\Radicados;
use app\models\MotivoCertificado;
use yii\web\Controller;
use app\models\GruposCargos;
use yii\web\NotFoundHttpException;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\TemplateProcessor;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\User;
use app\models\Valores;
use app\models\Municipios;
use app\models\TipoEntidad;
use app\models\ClaseEntidad;
use app\models\Cargos;
use app\models\Historial;
use app\models\Dignatarios;
use app\models\Resoluciones;
use app\models\Departamentos;
use app\models\DignatariosEntidades;
use app\models\DignatariosCargos;
use yii\helpers\Html;
use Carbon\Carbon;
use app\models\Profesional;
use yii\web\Response;
use DateTime;

/**
 * CertificadosHistoricosController implements the CRUD actions for Entidades model.
 */
class CertificadosHistoricosController extends Controller
{
    /**
     * @inheritdoc
     */
    private $codigo= "PR-M4-P2-03 V03";

    public function behaviors()
  {
  //Aqui se agregan los sitios que tendran restricción de acceso
  $only = ['index', 'xxx', 'create', 'update', 'view','index1'];
  return [
        'access' => [
            'class' => AccessControl::className(),
            'only' => $only,
            'rules' => [
                [
                    'actions' => ['xxx', 'index', 'create', 'update', 'view','index1'],
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


    /**
     * Lists all Entidades models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new EntidadesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionIndex1($id)
    {
        $searchModel = new EntidadesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['id_entidad'=>$id]);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Entidades model.
     * @param integer $id
     * @return mixed
     */
     public function actionView($id)
     {
       $id_entidad = $id;
       $entidad = Entidades::findOne($id_entidad);
       $dignatario = Dignatarios::find()->where(['and', ['id_entidad'=>$id_entidad],['id_cargo' => 1],['estado' => 1] ])->all();

       if(empty($dignatario)){
        echo '<script language="javascript">alert("LA ENTIDAD '.$entidad['nombre_entidad'].' NO TIENE REPRESENTANTE LEGAL");
               </script>';
         $session = Yii::$app->session;
         $r = $entidad['nombre_entidad'].' no tiene representante legal, por tal motivo no se puede realizar el trámite correspondiente';
          $session->set('msg',$r);
          $radicado = $session->get('id_radicado');
         $this->redirect(Yii::$app->request->baseUrl."?r=radicados%2Fview&id=".$radicado);
       }else{

         $tiempo = Carbon::now('America/Bogota');
         $gaceta = new DateTime($entidad['fecha_gaceta']);
         $now = new DateTime($tiempo->toDateString());
         //date_add($gaceta, date_interval_create_from_date_string('4 years'));
         $interval = $gaceta->diff($now);
         $historial = Historial::find()->where(['and',['id_tabla_modificada' => $entidad->id_entidad],['tabla_modificada' =>'ENTIDADES']])->asArray()->all();
         $history = "";
         foreach ($historial as $key => $value) {
           $campo1 = $value['valor_anterior_campo'];
           $campo2 = $value['valor_nuevo_campo'];
           switch ($value['nombre_campo_modificado']) {
             case 'id_tipo_entidad':
               $tipo = TipoEntidad::FindOne($value['valor_anterior_campo']);
               $campo1 = $tipo['tipo_entidad'];
               $tipo = TipoEntidad::FindOne($value['valor_nuevo_campo']);
               $campo2 = $tipo['tipo_entidad'];
               break;

             case 'id_usuario_tramita':
                 $usuario = User::findOne($value['valor_anterior_campo']);
                 $campo1 = $usuario['nombre_funcionario'];
                 $usuario1 = User::findOne($value['valor_nuevo_campo']);
                 $campo2 = $usuario1['nombre_funcionario'];
                 break;

             case 'id_clase_entidad':
               $clase = ClaseEntidad::FindOne($value['valor_anterior_campo']);
               $campo1 = $clase['clase_entidad'];
               $clase = ClaseEntidad::FindOne($value['valor_nuevo_campo']);
               $campo2 = $clase['clase_entidad'];
               break;

             case 'municipio_entidad':
               $municipio = Municipios::findOne($value['valor_anterior_campo']);
               $campo1 = $municipio['municipio'];
               $municipio = Municipios::findOne($value['valor_nuevo_campo']);
               $campo2 = $municipio['municipio'];
               break;

             case 'id_municipio_expedicion':
               $municipio = Municipios::findOne($value['valor_anterior_campo']);
               $campo1 = $municipio['municipio'];
               $municipio = Municipios::findOne($value['valor_nuevo_campo']);
               $campo2 = $municipio['municipio'];
               break;

             case 'id_cargo':
               $cargo = Cargos::findOne($value['valor_anterior_campo']);
               $campo1 = $cargo['nombre_cargo'];
               $cargo = Cargos::findOne($value['valor_nuevo_campo']);
               $campo2 = $cargo['nombre_cargo'];
               break;

             case 'id_grupo_cargos':
               $cargo = GruposCargos::findOne($value['valor_anterior_campo']);
               $campo1 = $cargo['nombre_grupo_cargo'];
               $cargo = GruposCargos::findOne($value['valor_nuevo_campo']);
               $campo2 = $cargo['nombre_grupo_cargo'];
               break;

             case 'id_tipo_tramite':
               $tramite = TipoTramite::findOne($value['valor_anterior_campo']);
               $campo1 = $tramite['descripcion'];
               $tramite = TipoTramite::findOne($value['valor_nuevo_campo']);
               $campo2 = $tramite['descripcion'];
               break;

             case 'estado_entidad':
               switch ($value['valor_anterior_campo']) {
                 case '1':
                   $campo1 = "ACTIVA";
                   break;
                 case '2':
                   $campo1 = "INACTIVA";
                   break;

               }
               switch ($value['valor_nuevo_campo']) {
                 case '1':
                   $campo2 = "ACTIVA";
                   break;
                 case '2':
                   $campo2 = "INACTIVA";
                   break;

               }

               break;
           }
              $history = $history."<w:br w:type='line'/>"."evento: ".$value['nombre_evento']." fecha: ".$value['fecha_modificacion']." valor anterior: ".$campo1." valor nuevo: ".$campo2."<w:br w:type='line'/>";
         }
         $a= $interval->format('%R%a'); // intervalo de tiempo entre el pago de la  gaceta y la fecha_gaceta
         // si el tipo de entidad es un organismo comunal se debe omitiar la fecha de la gaceta
         if($entidad->id_tipo_entidad == 14 || $entidad->id_tipo_entidad == 9 || $entidad->id_tipo_entidad == 12 || $entidad->id_tipo_entidad == 48){
           $a= -1;
         }
         if($a <= 0 ){


           $templateWord = new TemplateProcessor('plantillas/certificado historico.docx');
           $valores = Valores::find()->all();
           $municipio_entidad = Municipios::findOne($entidad['municipio_entidad']);
           $estado = "ACTIVA";
           $session = Yii::$app->session;
           $radicado = $session->get('id_radicado');
           $uso = "";
           
             $uso = MotivoCertificado::findOne(Radicados::findOne($radicado)->id_motivo);
             $templateWord->setValue("uso",$uso->descripcion_motivo);
           
           $tipoEntidad = TipoEntidad::findOne($entidad['id_tipo_entidad']);
           $claseEntidad = ClaseEntidad::findOne($entidad['id_clase_entidad']);
           $usuario = Yii::$app->user->identity->nombre_funcionario;
           $cargo = Yii::$app->user->identity->cargo_funcionario;
           $dignatario = Dignatarios::find()->where(['and', ['id_entidad'=>$id_entidad],['id_cargo' => 1],['estado' => 1] ])->all();
           $municipio = Municipios::findOne($dignatario[0]['id_municipio_expedicion']);
           $departamento = Departamentos::findOne($municipio['departamento_id']);
           $expedicion = $municipio['municipio'].",".$departamento['departamento'];
           $fecha_inscripcion = $entidad['fecha_reconocimiento'];
           list($año,$mes,$dia) = explode("-",$fecha_inscripcion);
           $profesional = Profesional::findOne(1);
           $resolucion = Resoluciones::find()->where(['and',['id_entidad'=>$entidad['id_entidad']],['id_tipo_resolucion'=>1]])->all();
           $dignatarios = Dignatarios::find()->where(['id_entidad'=>$entidad['id_entidad']])->all();
           $dig = "";

           for ($i=0; $i <sizeof($dignatarios) ; $i++) {
               $dig .= $dignatarios[$i]['nombre_dignatario']."<w:br w:type='line'/>".
               Cargos::findOne($dignatarios[$i]['id_cargo'])['nombre_cargo']." - ".GruposCargos::findOne($dignatarios[$i]['id_grupo_cargos'])['nombre_grupo_cargo']
               ."<w:br w:type='line'/>".
               "PERIODO ".$dignatarios[$i]['inicio_periodo'].
               " A ".$dignatarios[$i]['fin_periodo'].
               "<w:br w:type='line'/>".
               "<w:br w:type='line'/>";

           }
           if(empty($resolucion)){

           }else{
               $templateWord->setValue("resolucion",$resolucion[0]['numero_resolucion']);
           }


           $templateWord->setValue("formato",$this->codigo);
           if($historial){
             $templateWord->setValue("historial",$history);
           }
           $templateWord->setValue("nombre_entidad",$entidad['nombre_entidad']);
           $templateWord->setValue("municipio_entidad",$municipio_entidad['municipio']);
           $templateWord->setValue("estado_entidad",$estado);
           $templateWord->setValue("tipo_entidad",$tipoEntidad['tipo_entidad']);
           $templateWord->setValue("clase_entidad",$claseEntidad['clase_entidad']);
           $templateWord->setValue("fecha_actual",$tiempo->toDateString());
           $templateWord->setValue("nombre_representante",$dignatario[0]['nombre_dignatario']);
           $templateWord->setValue("numero_documento",$dignatario[0]['cedula_dignatario']);
           $templateWord->setValue("sdia",$dia);
           $templateWord->setValue("smes",$mes);
           $templateWord->setValue("saño",$año);
           $templateWord->setValue("retención_documental",$tipoEntidad['codigo_trd']);
           $templateWord->setValue("municipio_expedicion",$expedicion);
           $templateWord->setValue("dias",$tiempo->day);
           $templateWord->setValue("mes",$this->mes($tiempo->month));
           $templateWord->setValue("año",$tiempo->year);
           $templateWord->setValue("fecha_inicio",$fecha_inscripcion);
           $templateWord->setValue("fecha_fin",($año+4).'-'.$mes.'-'.$dia);
           $fecha_fin = new DateTime(($año+4).'-'.$mes.'-'.$dia);
           $intervalo = $fecha_fin->diff($now);
           if($intervalo->format('%R%a') >= 0){
              $templateWord->setValue("estado_periodo",'VENCIDO');
           }{
             $templateWord->setValue("estado_periodo",'VIGENTE');
           }
           $templateWord->setValue("s1",$valores[0]['Descripcion_valor']);
           $templateWord->setValue("v1",$valores[0]['valor']);
           $templateWord->setValue("s2",$valores[1]['Descripcion_valor']);
           $templateWord->setValue("v2",$valores[1]['valor']);
           $templateWord->setValue("s3",$valores[2]['Descripcion_valor']);
           $templateWord->setValue("v3",$valores[2]['valor']);
           $templateWord->setValue("s4",$valores[3]['Descripcion_valor']);
           $templateWord->setValue("v4",$valores[3]['valor']);
           $templateWord->setValue("s5",$valores[4]['Descripcion_valor']);
           $templateWord->setValue("v5",$valores[4]['valor']);
           $templateWord->setValue("s6",$valores[5]['Descripcion_valor']);
           $templateWord->setValue("v6",$valores[5]['valor']);
           $templateWord->setValue("s7",$valores[6]['Descripcion_valor']);
           $templateWord->setValue("v7",$valores[6]['valor']);
           $templateWord->setValue("s8",$valores[7]['Descripcion_valor']);
           $templateWord->setValue("v8",$valores[7]['valor']);
           $templateWord->setValue("dignatarios",$dig);
           $templateWord->setValue("nombre_profesional","IVONNE BEATRIZ CHAVERRA CARDONA");
           $templateWord->setValue("nombre_usuario",$usuario);
           $templateWord->setValue("cargo_usuario",$cargo);
           $templateWord->setValue("radicado",$radicado);
           $templateWord->setValue("nombre_profesional",$profesional['nombre_profesional']);
           $templateWord->setValue("cargo_profesional",$profesional['cargo_profesional']);
           $templateWord->saveAs('Certificado Historico '.$entidad['nombre_entidad'].'.docx');
           header('Content-Disposition: attachment; filename=Certificado Historico '.$entidad['nombre_entidad'].'.docx; charset=iso-8859-1');
           echo file_get_contents('Certificado Historico '.$entidad['nombre_entidad'].'.docx');

       }else{
          $session = Yii::$app->session;
          $r = $entidad['nombre_entidad'].' tiene la fecha de gaceta Vencida, por tal motivo no se puede realizar el trámite correspondiente';
          $session->set('msg',$r);
          $radicado = $session->get('id_radicado');
          $this->redirect(Yii::$app->request->baseUrl."?r=radicados%2Fview&id=".$radicado);
       }
     }

     }



     public function mes($mes){
       switch ($mes) {
         case 1:
           return "Enero";
           break;
         case 2:
           return "Febrero";
           break;

         case 3:
           return "Marzo";
           break;

         case 4:
           return "Abril";
           break;

         case 5:
           return "Mayo";
           break;

         case 6:
           return "Junio";
           break;

         case 7:
           return "Julio";
           break;

         case 8:
           return "Agosto";
           break;

         case 9:
           return "Septiembre";
           break;

         case 10:
           return "Octubre";
           break;

         case 11:
           return "Noviembre";
           break;

         case 12:
           return "Diciembre";
           break;


       }
     }

    /**
     * Creates a new Entidades model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Entidades();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id_entidad]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Entidades model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id_entidad]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Entidades model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Entidades model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Entidades the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Entidades::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
