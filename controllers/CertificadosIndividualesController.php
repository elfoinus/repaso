<?php

namespace app\controllers;

use Yii;
use app\models\Entidades;
use app\models\EntidadesSearch;
use app\models\Radicados;
use app\models\MotivoCertificado;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\TemplateProcessor;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\User;
use app\models\Valores;
use app\models\Cargos;
use app\models\GruposCargos;
use app\models\Municipios;
use app\models\TipoEntidad;
use app\models\Dignatarios;
use app\models\Historial;
use app\models\Resoluciones;
use app\models\Departamentos;
use app\models\DignatariosEntidades;
use app\models\DignatariosCargos;
use app\models\Profesional;
use yii\helpers\Html;
use Carbon\Carbon;
use yii\web\Response;
use DateTime;

/**
 * CertificadosIndividualesController implements the CRUD actions for Entidades model.
 */
class CertificadosIndividualesController extends Controller
{
    /**
     * @inheritdoc
     */
    private $codigo= "PR-M4-P2-03 V03";
    public function behaviors()
    {
    //Aqui se agregan los sitios que tendran restricción de acceso
    $only = ['index', 'create', 'update', 'view','index1'];
    return [
          'access' => [
              'class' => AccessControl::className(),
              'only' => $only,
              'rules' => [
                  [
                      'actions' => [ 'index', 'create', 'update', 'view','index1'],
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
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id,$dig)
    {
      $id_entidad = $id;
      $entidad = Entidades::findOne($id_entidad);
      $dignatario = Dignatarios::find()->where(['and', ['id_entidad'=>$id_entidad],['id_cargo' => 1],['estado' => 1] ])->all();
      $individual = Dignatarios::find()->where(['and',['id_dignatario'=>$dig],['id_entidad'=>$id_entidad]])->one();
      $cargox = Cargos::findOne($individual['id_cargo']);
      $grupo = GruposCargos::findOne($individual['id_grupo_cargos']);
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
        $fdignatario = new DateTime($individual['fin_periodo']);
        $diferencia = $fdignatario->diff($now);
        $b= $diferencia->format('%R%a');
        if($b <= 0){
          $situacion = "esta";
          $periodo = "Vigente";
        }else{
          $situacion = "estuvo";
          $periodo = "Vencido";
        }
        //date_add($gaceta, date_interval_create_from_date_string('4 years'));
        $interval = $gaceta->diff($now);

        $a= $interval->format('%R%a'); // intervalo de tiempo entre el pago de la  gaceta y la fecha_gaceta
        // si el tipo de entidad es un organismo comunal se debe omitiar la fecha de la gaceta
        if($entidad->id_tipo_entidad == 14 || $entidad->id_tipo_entidad == 9 || $entidad->id_tipo_entidad == 12 || $entidad->id_tipo_entidad == 48){
          $a= -1;
        }
        if($a <= 0  ){


          $templateWord = new TemplateProcessor('plantillas/certificado individual.docx');
          $valores = Valores::find()->all();
          $municipio_entidad = Municipios::findOne($entidad['municipio_entidad']);
          $estado = "ACTIVA";
          $tipoEntidad = TipoEntidad::findOne($entidad['id_tipo_entidad']);
          $usuario = Yii::$app->user->identity->nombre_funcionario;
          $cargo = Yii::$app->user->identity->cargo_funcionario;
          $session = Yii::$app->session;
          $profesional = Profesional::findOne(1);
          $radicado = $session->get('id_radicado');
          $uso = "";
          
            $uso = MotivoCertificado::findOne(Radicados::findOne($radicado)->id_motivo);
            $templateWord->setValue("uso",$uso->descripcion_motivo);
          
          $dignatario = Dignatarios::find()->where(['and', ['id_entidad'=>$id_entidad],['id_cargo' => 1],['estado' => 1] ])->all();
          $municipio = Municipios::findOne($dignatario[0]['id_municipio_expedicion']);
          $departamento = Departamentos::findOne($municipio['departamento_id']);
          $expedicion = $municipio['municipio'].",".$departamento['departamento'];
          $fecha_inscripcion = $entidad['fecha_reconocimiento'];
          list($año,$mes,$dia) = explode("-",$fecha_inscripcion);

          $resolucion = Resoluciones::find()->where(['and',['id_entidad'=>$entidad['id_entidad']],['id_tipo_resolucion'=>1]])->all();
          if(empty($resolucion)){

          }else{
              $templateWord->setValue("resolucion",$resolucion[0]['numero_resolucion']);
          }


          $templateWord->setValue("formato",$this->codigo);
          $templateWord->setValue("nombre_entidad",$entidad['nombre_entidad']);
          $templateWord->setValue("municipio_entidad",$municipio_entidad['municipio']);
          $templateWord->setValue("estado_entidad",$estado);
          $templateWord->setValue("tipo_entidad",$tipoEntidad['tipo_entidad']);
          $templateWord->setValue("retención_documental",$tipoEntidad['codigo_trd']);
          $templateWord->setValue("fecha",$tiempo->toDateString());
          $templateWord->setValue("nombre_representante",$dignatario[0]['nombre_dignatario']);
          $templateWord->setValue("numero_documento",$dignatario[0]['cedula_dignatario']);
          $templateWord->setValue("nombre",$individual['nombre_dignatario']);
          $templateWord->setValue("cedula",$individual['cedula_dignatario']);
          $templateWord->setValue("cargo",$cargox['nombre_cargo']);
          $templateWord->setValue("grupo_cargo",$grupo['nombre_grupo_cargo']);
          $templateWord->setValue("inicio",$individual['inicio_periodo']);
          $templateWord->setValue("situacion",$situacion);
          $templateWord->setValue("periodo",$periodo);
          $templateWord->setValue("fin",$individual['fin_periodo']);
          $templateWord->setValue("sdia",$dia);
          $templateWord->setValue("smes",$mes);
          $templateWord->setValue("saño",$año);
          $templateWord->setValue("resolucion","____");
          $templateWord->setValue("municipio_expedicion",$expedicion);
          $templateWord->setValue("dias",$tiempo->day);
          $templateWord->setValue("mes",$this->mes($tiempo->month));
          $templateWord->setValue("año",$tiempo->year);
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
          $templateWord->setValue("nombre_usuario",$usuario);
          $templateWord->setValue("cargo_usuario",$cargo);
          $templateWord->setValue("radicado",$radicado);
          $templateWord->setValue("nombre_profesional",$profesional['nombre_profesional']);
          $templateWord->setValue("cargo_profesional",$profesional['cargo_profesional']);
          $templateWord->setValue("nombre_profesional","IVONNE BEATRIZ CHAVERRA CARDONA");
          $templateWord->saveAs('Certificado existencia y representación legal '.$entidad['nombre_entidad'].'.docx');
          header('Content-Disposition: attachment; filename=Certificado existencia y representación legal '.$entidad['nombre_entidad'].'.docx; charset=iso-8859-1');
          echo file_get_contents('Certificado existencia y representación legal '.$entidad['nombre_entidad'].'.docx');

          $this->redirect(Yii::$app->request->baseUrl."?r=radicados");
          
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


    public function actionAbout($id)
    {
      $dignatarios = Dignatarios::find()->where(['id_entidad'=>$id])->asArray()->all();
        return $this->render('about', [
            'model' => $dignatarios,
        ]);
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
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
