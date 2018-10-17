<?php

namespace app\controllers;

use Yii;
use app\models\Resoluciones;
use app\models\Entidades;
use app\models\Radicados;
use app\models\Historial;
use app\models\Dignatarios;
use app\models\Municipios;
use app\models\Departamentos;
use app\models\ResolucionesSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\models\TipoResolucion;
use app\models\User;
use app\models\Cargos;
use app\models\GruposCargos;
use yii\filters\AccessControl;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\TemplateProcessor;
use Carbon\Carbon;
use app\models\TipoEntidad;
use app\models\Profesional;
/**
 * ResolucionesController implements the CRUD actions for Resoluciones model.
 */
class ResolucionesController extends Controller
{
    /**
     * @inheritdoc
     */
     public function behaviors()
   {
   //Aqui se agregan los sitios que tendran restricción de acceso
   $only = ['index', 'xxx', 'create', 'update', 'view'];
   return [
         'access' => [
             'class' => AccessControl::className(),
             'only' => $only,
             'rules' => [
                 [
                     'actions' => ['xxx', 'index', 'create', 'update', 'view'],
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
     * Lists all Resoluciones models.
     * @return mixed
     */
    public function actionIndex($id)
    {

        $searchModel = new ResolucionesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['id_tipo_resolucion'=>$id]);
        $titulo = TipoResolucion::findOne($id);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'titulo' => $titulo['nombre_tipo_resolucion'],
        ]);

    }

    /**
     * Displays a single Resoluciones model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $resolucion = Resoluciones::findOne($id);
        $entidad = Entidades::findOne($resolucion['id_entidad']);
        $profesional = Profesional::findOne(1);
        $usuario = Yii::$app->user->identity->nombre_funcionario;
        $cargo = Yii::$app->user->identity->cargo_funcionario;
        $tipoEntidad = TipoEntidad::findOne($entidad['id_tipo_entidad']);
        $radicado = Radicados::findOne($resolucion['id_radicado']);
        $dignatario = Dignatarios::find()->where(['and', ['id_entidad'=>$entidad['id_entidad']],['id_cargo' => 1],['estado' => 1] ])->all(); //
        list($añoradi,$mesradi,$diaradi) = explode("-",$radicado->fecha_creacion);
      /*
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);*/

        if(empty($dignatario)){
          echo '<script language="javascript">alert("LA ENTIDAD '.$entidad['nombre_entidad'].' NO TIENE REPRESENTANTE LEGAL");
                </script>';
          //return $this->actionIndex($resolucion['id_tipo_resolucion']);
          $session = Yii::$app->session;
          $r = 'LA ENTIDAD '.$entidad['nombre_entidad'].' NO TIENE REPRESENTANTE LEGAL';
          $session->set('msg',$r);
          $this->redirect(Yii::$app->request->baseUrl."?r=entidades%2Fview&id=".$resolucion['id_entidad']);
               
        }else{
          switch ($resolucion['id_tipo_resolucion']) {

            case 1:
            //resolucion De Reconocimiento de Personeria Juridica
              $templateWord = new TemplateProcessor('plantillas/resolucion reconocimiento.docx');
              $resolucion = Resoluciones::findOne($id);
              $entidad = Entidades::findOne($resolucion['id_entidad']);
              $municipio_entidad = Municipios::findOne($entidad['municipio_entidad']);
              $fecha_creacion = $resolucion['fecha_creacion'];
              list($año,$mes,$dia) = explode("-",$fecha_creacion);


              $municipio_dignatario = Municipios::findOne($dignatario[0]['id_municipio_expedicion']);
              $departamento_dignatario = Departamentos::findOne($municipio_dignatario['departamento_id']);
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
              $templateWord->setValue("numero_resolucion",$resolucion['numero_resolucion']);
              $templateWord->setValue("fecha_creacion",$resolucion['fecha_creacion']);
              $templateWord->setValue("nombre_entidad",$entidad['nombre_entidad']);
              $templateWord->setValue("municipio_entidad",$municipio_entidad['municipio']);
              $templateWord->setValue("sdia",$dia);
              $templateWord->setValue("smes",ResolucionesController::mes($mes));
              $templateWord->setValue("saño",$año);

              if($entidad['periodo_entidad'] == 0){
                $templateWord->setValue("año_vencimiento",'INDEFINIDO');
                $templateWord->setValue("periodo",'');
                $templateWord->setValue("año_vence",'INDEFINIDO');
                $templateWord->setValue("mes_vence",'');
                $templateWord->setValue("dia_vence",'');
                $templateWord->setValue("año_inscripcion",'INDEFINIDO');
              }else{
                $templateWord->setValue("año_inscripcion",$año);
                $templateWord->setValue("año_vencimiento",$año+$entidad['periodo_entidad']);
                  $templateWord->setValue("periodo",$entidad['periodo_entidad']);
                  $templateWord->setValue("año_vence",$año+$entidad['periodo_entidad']);
                  $templateWord->setValue("mes_vence",ResolucionesController::mes($mes));
                  $templateWord->setValue("dia_vence",$dia);
              }



              $templateWord->setValue("dignatarios",$dig);
              $templateWord->setValue("numero_radicado",$radicado->id_radicado);
              $templateWord->setValue("sade",$radicado->sade);
              $templateWord->setValue("año_radicado",$añoradi);
              $templateWord->setValue("mes_radicado",ResolucionesController::mes($mesradi));
              $templateWord->setValue("dia_radicado",$diaradi);
              $templateWord->setValue("nombre_dignatario",$dignatario[0]['nombre_dignatario']);
              $templateWord->setValue("cedula_dignatario",$dignatario[0]['cedula_dignatario']);
              $templateWord->setValue("municipio_dignatario",$municipio_dignatario['municipio']);
              $templateWord->setValue("departamento_dignatario",$departamento_dignatario['departamento']);
              $templateWord->setValue("nombre_profesional",$profesional['nombre_profesional']);
              $templateWord->setValue("cargo_profesional",$profesional['cargo_profesional']);
              $templateWord->setValue("nombre_usuario",$usuario);
              $templateWord->setValue("cargo_usuario",$cargo);
              $templateWord->setValue("revision",$tipoEntidad['revision']);
              $templateWord->setValue("publicacion_gaceta",$tipoEntidad['gaceta']);
              $templateWord->saveAs('Reconocimiento personería jurídica '.$entidad['nombre_entidad'].'.docx');
              header('Content-Disposition: attachment; filename=Reconocimiento personería jurídica '.$entidad['nombre_entidad'].'.docx; charset=iso-8859-1');
              echo file_get_contents('Reconocimiento personería jurídica '.$entidad['nombre_entidad'].'.docx');

              break;

            case 2:
            //resolucion De reforma de estatutos cambio de razon social
              $templateWord = new TemplateProcessor('plantillas/resolucion reforma de estatutos.docx');
              $resolucion = Resoluciones::findOne($id);
              $tiporesolucion = TipoResolucion::findOne($resolucion->id_tipo_resolucion);
              $entidad = Entidades::findOne($resolucion['id_entidad']);
              $municipio_entidad = Municipios::findOne($entidad['municipio_entidad']);
              $fecha_creacion = $resolucion['fecha_creacion'];
              list($año,$mes,$dia) = explode("-",$fecha_creacion);

              $historial = Historial::findOne($resolucion->id_historial);
              $historial->valor_anterior_campo = str_replace("<", "", $historial->valor_anterior_campo);
              $historial->valor_anterior_campo = str_replace(">", "", $historial->valor_anterior_campo);
              $historial->valor_nuevo_campo = str_replace("<", "", $historial->valor_nuevo_campo);
              $historial->valor_nuevo_campo = str_replace(">", "", $historial->valor_nuevo_campo);
              $cambio = $historial->nombre_evento."<w:br w:type='line'/>"."valor anterior: "
              .$historial->valor_anterior_campo."<w:br w:type='line'/>"."valor nuevo: ".$historial->valor_nuevo_campo;
              $municipio_dignatario = Municipios::findOne($dignatario[0]['id_municipio_expedicion']);
              $departamento_dignatario = Departamentos::findOne($municipio_dignatario['departamento_id']);

              $dig = "";

              $templateWord->setValue("cambio",$cambio);
              $templateWord->setValue("numero_resolucion",$resolucion['numero_resolucion']);
              $templateWord->setValue("fecha_creacion",$resolucion['fecha_creacion']);
              $templateWord->setValue("nombre_entidad",$entidad['nombre_entidad']);
              $templateWord->setValue("municipio_entidad",$municipio_entidad['municipio']);
              $templateWord->setValue("sdia",$dia);
              $templateWord->setValue("smes",$mes);
              $templateWord->setValue("saño",$año);


              if($entidad['periodo_entidad'] == 0){
                $templateWord->setValue("año_vencimiento",'INDEFINIDO');
                $templateWord->setValue("periodo",'');
                $templateWord->setValue("año_vence",'INDEFINIDO');
                $templateWord->setValue("mes_vence",'');
                $templateWord->setValue("dia_vence",'');
                $templateWord->setValue("año_inscripcion",'INDEFINIDO');
              }else{
                $templateWord->setValue("año_inscripcion",$año);
                $templateWord->setValue("año_vencimiento",$año+$entidad['periodo_entidad']);
                  $templateWord->setValue("periodo",$entidad['periodo_entidad']);
                  $templateWord->setValue("año_vence",$año+$entidad['periodo_entidad']);
                  $templateWord->setValue("mes_vence",ResolucionesController::mes($mes));
                  $templateWord->setValue("dia_vence",$dia);
              }



              $templateWord->setValue("dignatarios",$dig);
              $templateWord->setValue("numero_radicado",$radicado->id_radicado);
              $templateWord->setValue("sade",$radicado->sade);
              $templateWord->setValue("año_radicado",$añoradi);
              $templateWord->setValue("mes_radicado",ResolucionesController::mes($mesradi));
              $templateWord->setValue("dia_radicado",$diaradi);
              $templateWord->setValue("nombre_dignatario",$dignatario[0]['nombre_dignatario']);
              $templateWord->setValue("cedula_dignatario",$dignatario[0]['cedula_dignatario']);
              $templateWord->setValue("municipio_dignatario",$municipio_dignatario['municipio']);
              $templateWord->setValue("departamento_dignatario",$departamento_dignatario['departamento']);
              $templateWord->setValue("nombre_profesional",$profesional['nombre_profesional']);
              $templateWord->setValue("cargo_profesional",$profesional['cargo_profesional']);
              $templateWord->setValue("nombre_usuario",$usuario);
              $templateWord->setValue("cargo_usuario",$cargo);
              $templateWord->setValue("revision",$tipoEntidad['revision']);
              $templateWord->setValue("publicacion_gaceta",$tipoEntidad['gaceta']);
              $templateWord->saveAs('Reforma de estatutos '.$tiporesolucion->nombre_tipo_resolucion.' '.$entidad['nombre_entidad'].'.docx');
              header('Content-Disposition: attachment; filename=Reforma de estatutos '.$tiporesolucion->nombre_tipo_resolucion.' '.$entidad['nombre_entidad'].'.docx; charset=iso-8859-1');
              echo file_get_contents('Reforma de estatutos '.$tiporesolucion->nombre_tipo_resolucion.' '.$entidad['nombre_entidad'].'.docx');

              break;

            case 3:
            //resolucion De reforma de estatutos cambio de domicilio
              $templateWord = new TemplateProcessor('plantillas/resolucion reforma de estatutos.docx');
              $resolucion = Resoluciones::findOne($id);
              $tiporesolucion = TipoResolucion::findOne($resolucion->id_tipo_resolucion);
              $entidad = Entidades::findOne($resolucion['id_entidad']);
              $municipio_entidad = Municipios::findOne($entidad['municipio_entidad']);
              $fecha_creacion = $resolucion['fecha_creacion'];
              list($año,$mes,$dia) = explode("-",$fecha_creacion);

              $historial = Historial::findOne($resolucion->id_historial);
              //$cambio ="";
              $historial->valor_anterior_campo = str_replace("<", "", $historial->valor_anterior_campo);
              $historial->valor_anterior_campo = str_replace(">", "", $historial->valor_anterior_campo);
              $historial->valor_nuevo_campo = str_replace("<", "", $historial->valor_nuevo_campo);
              $historial->valor_nuevo_campo = str_replace(">", "", $historial->valor_nuevo_campo);

               $cambio = $historial->nombre_evento."<w:br w:type='line'/>"."valor anterior: "
              .$historial->valor_anterior_campo."<w:br w:type='line'/>"."valor nuevo: ".$historial->valor_nuevo_campo;
            
              $municipio_dignatario = Municipios::findOne($dignatario[0]['id_municipio_expedicion']);
              $departamento_dignatario = Departamentos::findOne($municipio_dignatario['departamento_id']);

              $dig = "";

              $templateWord->setValue("cambio",$cambio);
              $templateWord->setValue("numero_resolucion",$resolucion['numero_resolucion']);
              $templateWord->setValue("fecha_creacion",$resolucion['fecha_creacion']);
              $templateWord->setValue("nombre_entidad",$entidad['nombre_entidad']);
              $templateWord->setValue("municipio_entidad",$municipio_entidad['municipio']);
              $templateWord->setValue("sdia",$dia);
              $templateWord->setValue("smes",$mes);
              $templateWord->setValue("saño",$año);


              if($entidad['periodo_entidad'] == 0){
                $templateWord->setValue("año_vencimiento",'INDEFINIDO');
                $templateWord->setValue("periodo",'');
                $templateWord->setValue("año_vence",'INDEFINIDO');
                $templateWord->setValue("mes_vence",'');
                $templateWord->setValue("dia_vence",'');
                $templateWord->setValue("año_inscripcion",'INDEFINIDO');
              }else{
                $templateWord->setValue("año_inscripcion",$año);
                $templateWord->setValue("año_vencimiento",$año+$entidad['periodo_entidad']);
                  $templateWord->setValue("periodo",$entidad['periodo_entidad']);
                  $templateWord->setValue("año_vence",$año+$entidad['periodo_entidad']);
                  $templateWord->setValue("mes_vence",ResolucionesController::mes($mes));
                  $templateWord->setValue("dia_vence",$dia);
              }



              $templateWord->setValue("dignatarios",$dig);
              $templateWord->setValue("numero_radicado",$radicado->id_radicado);
              $templateWord->setValue("sade",$radicado->sade);
              $templateWord->setValue("año_radicado",$añoradi);
              $templateWord->setValue("mes_radicado",ResolucionesController::mes($mesradi));
              $templateWord->setValue("dia_radicado",$diaradi);
              $templateWord->setValue("nombre_dignatario",$dignatario[0]['nombre_dignatario']);
              $templateWord->setValue("cedula_dignatario",$dignatario[0]['cedula_dignatario']);
              $templateWord->setValue("municipio_dignatario",$municipio_dignatario['municipio']);
              $templateWord->setValue("departamento_dignatario",$departamento_dignatario['departamento']);
              $templateWord->setValue("nombre_profesional",$profesional['nombre_profesional']);
              $templateWord->setValue("cargo_profesional",$profesional['cargo_profesional']);
              $templateWord->setValue("nombre_usuario",$usuario);
              $templateWord->setValue("cargo_usuario",$cargo);
              $templateWord->setValue("revision",$tipoEntidad['revision']);
              $templateWord->setValue("publicacion_gaceta",$tipoEntidad['gaceta']);
              $templateWord->saveAs('Reforma de estatutos '.$tiporesolucion->nombre_tipo_resolucion.' '.$entidad['nombre_entidad'].'.docx');
              header('Content-Disposition: attachment; filename=Reforma de estatutos '.$tiporesolucion->nombre_tipo_resolucion.' '.$entidad['nombre_entidad'].'.docx; charset=iso-8859-1');
              echo file_get_contents('Reforma de estatutos '.$tiporesolucion->nombre_tipo_resolucion.' '.$entidad['nombre_entidad'].'.docx');

              break;

            case 4:
              //resolucion inscripcion  de dignatarios JAC .docx
              $templateWord = new TemplateProcessor('plantillas/resolucion de inscripcion de dignatarios.docx');
              $entidad = Entidades::findOne($resolucion['id_entidad']);
              $resolucion = Resoluciones::findOne($id);
              $usuario = Yii::$app->user->identity->nombre_funcionario;
              $cargo = Yii::$app->user->identity->cargo_funcionario;

              $municipio_entidad = Municipios::findOne($entidad['municipio_entidad']);
              $fecha_creacion = $resolucion['fecha_creacion'];
              list($año,$mes,$dia) = explode("-",$fecha_creacion);
              $dignatario = Dignatarios::find()->where(['and', ['id_entidad'=>$entidad['id_entidad']],['id_cargo' => 1],['estado' => 1] ])->all(); //
              $municipio_dignatario = Municipios::findOne($dignatario[0]['id_municipio_expedicion']);
              $departamento_dignatario = Departamentos::findOne($municipio_dignatario['departamento_id']);
              $dignatarios = Dignatarios::find()->where( [ 'and', ['id_entidad'=>$entidad['id_entidad']], ['fecha_ingreso' => $fecha_creacion ] ])->all();
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
              $templateWord->setValue("numero_radicado",$radicado->id_radicado);
              $templateWord->setValue("sade",$radicado->sade);
              $templateWord->setValue("año_radicado",$añoradi);
              $templateWord->setValue("mes_radicado",ResolucionesController::mes($mesradi));
              $templateWord->setValue("dia_radicado",$diaradi);
              $templateWord->setValue("resolucion",$resolucion['numero_resolucion']);
              $templateWord->setValue("fecha_creacion",$resolucion['fecha_creacion']);
              $templateWord->setValue("nombre_entidad",$entidad['nombre_entidad']);
              $templateWord->setValue("municipio_entidad",$municipio_entidad['municipio']);
              $templateWord->setValue("sdia",$dia);
              $templateWord->setValue("smes",ResolucionesController::mes($mes));
              $templateWord->setValue("saño",$año);
              $templateWord->setValue("año_inicio",$año);
              $templateWord->setValue("año_inscripcion",$año);
              $templateWord->setValue("año_fin",$año+4);//suponiendo que son 4 años
              if($entidad['periodo_entidad'] == 0){
                $templateWord->setValue("año_vencimiento",'INDEFINIDO');
                $templateWord->setValue("periodo",'');
                $templateWord->setValue("año_vence",'INDEFINIDO');
                $templateWord->setValue("mes_vence",'');
                $templateWord->setValue("dia_vence",'');
              }else{
                $templateWord->setValue("año_vencimiento",$año+$entidad['periodo_entidad']);
                  $templateWord->setValue("periodo",$entidad['periodo_entidad']);
                  $templateWord->setValue("año_vence",$año+$entidad['periodo_entidad']);
                  $templateWord->setValue("mes_vence",ResolucionesController::mes($mes));
                  $templateWord->setValue("dia_vence",$dia);
              }
              $templateWord->setValue("dignatarios",$dig);
              $templateWord->setValue("nombre_dignatario",$dignatario[0]['nombre_dignatario']);
              $templateWord->setValue("cedula_dignatario",$dignatario[0]['cedula_dignatario']);
              $templateWord->setValue("municipio_dignatario",$municipio_dignatario['municipio']);
              $templateWord->setValue("departamento_dignatario",$departamento_dignatario['departamento']);
              $templateWord->setValue("nombre_usuario",$usuario);
              $templateWord->setValue("cargo_usuario",$cargo);
              $templateWord->setValue("nombre_profesional",$profesional['nombre_profesional']);
              $templateWord->setValue("cargo_profesional",$profesional['cargo_profesional']);
              $templateWord->setValue("revision",$tipoEntidad['revision']);
              $templateWord->setValue("publicacion_gaceta",$tipoEntidad['gaceta']);
              $templateWord->saveAs('inscripcion de dignatarios '.$entidad['nombre_entidad'].'.docx');
              header('Content-Disposition: attachment; filename=inscripcion de dignatarios '.$entidad['nombre_entidad'].'.docx; charset=iso-8859-1');
              echo file_get_contents('inscripcion de dignatarios '.$entidad['nombre_entidad'].'.docx');
                # code...
            break;

            case 5:
            //resolucion De reforma de estatutos cancelacion
              $templateWord = new TemplateProcessor('plantillas/resolucion cancelacion.docx');
              $resolucion = Resoluciones::findOne($id);
              $tiporesolucion = TipoResolucion::findOne($resolucion->id_tipo_resolucion);
              $entidad = Entidades::findOne($resolucion['id_entidad']);
              $municipio_entidad = Municipios::findOne($entidad['municipio_entidad']);
              $fecha_creacion = $resolucion['fecha_creacion'];
              list($año,$mes,$dia) = explode("-",$fecha_creacion);

              $historial = Historial::findOne($resolucion->id_historial);
              $historial->valor_anterior_campo = str_replace("<", "", $historial->valor_anterior_campo);
              $historial->valor_anterior_campo = str_replace(">", "", $historial->valor_anterior_campo);
              $historial->valor_nuevo_campo = str_replace("<", "", $historial->valor_nuevo_campo);
              $historial->valor_nuevo_campo = str_replace(">", "", $historial->valor_nuevo_campo);
              $cambio = $historial->nombre_evento."<w:br w:type='line'/>"."valor anterior: "
              .$historial->valor_anterior_campo."<w:br w:type='line'/>"."valor nuevo: ".$historial->valor_nuevo_campo;
              $municipio_dignatario = Municipios::findOne($dignatario[0]['id_municipio_expedicion']);
              $departamento_dignatario = Departamentos::findOne($municipio_dignatario['departamento_id']);

              $dig = "";
              $templateWord->setValue("n_resolucion",$resolucion->numero_resolucion);
              $templateWord->setValue("cambio",$cambio);
              $templateWord->setValue("numero_resolucion",$resolucion['numero_resolucion']);
              $templateWord->setValue("fecha_creacion",$resolucion['fecha_creacion']);
              $templateWord->setValue("nombre_entidad",$entidad['nombre_entidad']);
              $templateWord->setValue("municipio_entidad",$municipio_entidad['municipio']);
              $templateWord->setValue("sdia",$dia);
              $templateWord->setValue("smes",ResolucionesController::mes($mes));
              $templateWord->setValue("saño",$año);
              $templateWord->setValue("dia_resolucion",$dia);
              $templateWord->setValue("mes_resolucion",ResolucionesController::mes($mes));
              $templateWord->setValue("año_resolucion",$año);
              $templateWord->setValue("tipo_entidad",$tipoEntidad['tipo_entidad']);

              if($entidad['periodo_entidad'] == 0){
                $templateWord->setValue("año_vencimiento",'INDEFINIDO');
                $templateWord->setValue("periodo",'');
                $templateWord->setValue("año_vence",'INDEFINIDO');
                $templateWord->setValue("mes_vence",'');
                $templateWord->setValue("dia_vence",'');
                $templateWord->setValue("año_inscripcion",'INDEFINIDO');
              }else{
                $templateWord->setValue("año_inscripcion",$año);
                $templateWord->setValue("año_vencimiento",$año+$entidad['periodo_entidad']);
                  $templateWord->setValue("periodo",$entidad['periodo_entidad']);
                  $templateWord->setValue("año_vence",$año+$entidad['periodo_entidad']);
                  $templateWord->setValue("mes_vence",ResolucionesController::mes($mes));
                  $templateWord->setValue("dia_vence",$dia);
              }



              $templateWord->setValue("dignatarios",$dig);
              $templateWord->setValue("numero_radicado",$radicado->id_radicado);
              $templateWord->setValue("sade",$radicado->sade);
              $templateWord->setValue("año_radicado",$añoradi);
              $templateWord->setValue("mes_radicado",ResolucionesController::mes($mesradi));
              $templateWord->setValue("dia_radicado",$diaradi);
              $templateWord->setValue("nombre_dignatario",$dignatario[0]['nombre_dignatario']);
              $templateWord->setValue("cedula_dignatario",$dignatario[0]['cedula_dignatario']);
              $templateWord->setValue("municipio_dignatario",$municipio_dignatario['municipio']);
              $templateWord->setValue("departamento_dignatario",$departamento_dignatario['departamento']);
              $templateWord->setValue("nombre_profesional",$profesional['nombre_profesional']);
              $templateWord->setValue("cargo_profesional",$profesional['cargo_profesional']);
              $templateWord->setValue("nombre_usuario",$usuario);
              $templateWord->setValue("cargo_usuario",$cargo);
              $templateWord->setValue("revision",$tipoEntidad['revision']);
              $templateWord->setValue("publicacion_gaceta",$tipoEntidad['gaceta']);
              $templateWord->saveAs('Reforma de estatutos '.$tiporesolucion->nombre_tipo_resolucion.' '.$entidad['nombre_entidad'].'.docx');
              header('Content-Disposition: attachment; filename=Reforma de estatutos '.$tiporesolucion->nombre_tipo_resolucion.' '.$entidad['nombre_entidad'].'.docx; charset=iso-8859-1');
              echo file_get_contents('Reforma de estatutos '.$tiporesolucion->nombre_tipo_resolucion.' '.$entidad['nombre_entidad'].'.docx');

            break;

            case 6:
            //resolucion De reforma de estatutos cambio de objetivos
              $templateWord = new TemplateProcessor('plantillas/resolucion reforma de estatutos.docx');
              $resolucion = Resoluciones::findOne($id);
              $tiporesolucion = TipoResolucion::findOne($resolucion->id_tipo_resolucion);
              $entidad = Entidades::findOne($resolucion['id_entidad']);
              $municipio_entidad = Municipios::findOne($entidad['municipio_entidad']);
              $fecha_creacion = $resolucion['fecha_creacion'];
              list($año,$mes,$dia) = explode("-",$fecha_creacion);

              $historial = Historial::findOne($resolucion->id_historial);
              $historial->valor_anterior_campo = str_replace("<", "", $historial->valor_anterior_campo);
              $historial->valor_anterior_campo = str_replace(">", "", $historial->valor_anterior_campo);
              $historial->valor_nuevo_campo = str_replace("<", "", $historial->valor_nuevo_campo);
              $historial->valor_nuevo_campo = str_replace(">", "", $historial->valor_nuevo_campo);
              $cambio = $historial->nombre_evento."<w:br w:type='line'/>"."valor anterior: "
              .$historial->valor_anterior_campo."<w:br w:type='line'/>"."valor nuevo: ".$historial->valor_nuevo_campo;
              $municipio_dignatario = Municipios::findOne($dignatario[0]['id_municipio_expedicion']);
              $departamento_dignatario = Departamentos::findOne($municipio_dignatario['departamento_id']);

              $dig = "";

              $templateWord->setValue("cambio",$cambio);
              $templateWord->setValue("numero_resolucion",$resolucion['numero_resolucion']);
              $templateWord->setValue("fecha_creacion",$resolucion['fecha_creacion']);
              $templateWord->setValue("nombre_entidad",$entidad['nombre_entidad']);
              $templateWord->setValue("municipio_entidad",$municipio_entidad['municipio']);
              $templateWord->setValue("sdia",$dia);
              $templateWord->setValue("smes",ResolucionesController::mes($mes));
              $templateWord->setValue("saño",$año);


              if($entidad['periodo_entidad'] == 0){
                $templateWord->setValue("año_vencimiento",'INDEFINIDO');
                $templateWord->setValue("periodo",'');
                $templateWord->setValue("año_vence",'INDEFINIDO');
                $templateWord->setValue("mes_vence",'');
                $templateWord->setValue("dia_vence",'');
                $templateWord->setValue("año_inscripcion",'INDEFINIDO');
              }else{
                $templateWord->setValue("año_inscripcion",$año);
                $templateWord->setValue("año_vencimiento",$año+$entidad['periodo_entidad']);
                  $templateWord->setValue("periodo",$entidad['periodo_entidad']);
                  $templateWord->setValue("año_vence",$año+$entidad['periodo_entidad']);
                  $templateWord->setValue("mes_vence",ResolucionesController::mes($mes));
                  $templateWord->setValue("dia_vence",$dia);
              }



              $templateWord->setValue("dignatarios",$dig);
              $templateWord->setValue("numero_radicado",$radicado->id_radicado);
              $templateWord->setValue("sade",$radicado->sade);
              $templateWord->setValue("año_radicado",$añoradi);
              $templateWord->setValue("mes_radicado",ResolucionesController::mes($mesradi));
              $templateWord->setValue("dia_radicado",$diaradi);
              $templateWord->setValue("nombre_dignatario",$dignatario[0]['nombre_dignatario']);
              $templateWord->setValue("cedula_dignatario",$dignatario[0]['cedula_dignatario']);
              $templateWord->setValue("municipio_dignatario",$municipio_dignatario['municipio']);
              $templateWord->setValue("departamento_dignatario",$departamento_dignatario['departamento']);
              $templateWord->setValue("nombre_profesional",$profesional['nombre_profesional']);
              $templateWord->setValue("cargo_profesional",$profesional['cargo_profesional']);
              $templateWord->setValue("nombre_usuario",$usuario);
              $templateWord->setValue("cargo_usuario",$cargo);
              $templateWord->setValue("revision",$tipoEntidad['revision']);
              $templateWord->setValue("publicacion_gaceta",$tipoEntidad['gaceta']);
              $templateWord->saveAs('Reforma de estatutos '.$tiporesolucion->nombre_tipo_resolucion.' '.$entidad['nombre_entidad'].'.docx');
              header('Content-Disposition: attachment; filename=Reforma de estatutos '.$tiporesolucion->nombre_tipo_resolucion.' '.$entidad['nombre_entidad'].'.docx; charset=iso-8859-1');
              echo file_get_contents('Reforma de estatutos '.$tiporesolucion->nombre_tipo_resolucion.' '.$entidad['nombre_entidad'].'.docx');

            break;

            case 7:
            //resolucion De reforma de estatutos cambio de clase
              $templateWord = new TemplateProcessor('plantillas/resolucion reforma de estatutos.docx');
              $resolucion = Resoluciones::findOne($id);
              $tiporesolucion = TipoResolucion::findOne($resolucion->id_tipo_resolucion);
              $entidad = Entidades::findOne($resolucion['id_entidad']);
              $municipio_entidad = Municipios::findOne($entidad['municipio_entidad']);
              $fecha_creacion = $resolucion['fecha_creacion'];
              list($año,$mes,$dia) = explode("-",$fecha_creacion);

              $historial = Historial::findOne($resolucion->id_historial);
              $historial->valor_anterior_campo = str_replace("<", "", $historial->valor_anterior_campo);
              $historial->valor_anterior_campo = str_replace(">", "", $historial->valor_anterior_campo);
              $historial->valor_nuevo_campo = str_replace("<", "", $historial->valor_nuevo_campo);
              $historial->valor_nuevo_campo = str_replace(">", "", $historial->valor_nuevo_campo);
              $cambio = $historial->nombre_evento."<w:br w:type='line'/>"."valor anterior: "
              .$historial->valor_anterior_campo."<w:br w:type='line'/>"."valor nuevo: ".$historial->valor_nuevo_campo;
              $municipio_dignatario = Municipios::findOne($dignatario[0]['id_municipio_expedicion']);
              $departamento_dignatario = Departamentos::findOne($municipio_dignatario['departamento_id']);

              $dig = "";

              $templateWord->setValue("cambio",$cambio);
              $templateWord->setValue("numero_resolucion",$resolucion['numero_resolucion']);
              $templateWord->setValue("fecha_creacion",$resolucion['fecha_creacion']);
              $templateWord->setValue("nombre_entidad",$entidad['nombre_entidad']);
              $templateWord->setValue("municipio_entidad",$municipio_entidad['municipio']);
              $templateWord->setValue("sdia",$dia);
              $templateWord->setValue("smes",ResolucionesController::mes($mes));
              $templateWord->setValue("saño",$año);


              if($entidad['periodo_entidad'] == 0){
                $templateWord->setValue("año_vencimiento",'INDEFINIDO');
                $templateWord->setValue("periodo",'');
                $templateWord->setValue("año_vence",'INDEFINIDO');
                $templateWord->setValue("mes_vence",'');
                $templateWord->setValue("dia_vence",'');
                $templateWord->setValue("año_inscripcion",'INDEFINIDO');
              }else{
                $templateWord->setValue("año_inscripcion",$año);
                $templateWord->setValue("año_vencimiento",$año+$entidad['periodo_entidad']);
                  $templateWord->setValue("periodo",$entidad['periodo_entidad']);
                  $templateWord->setValue("año_vence",$año+$entidad['periodo_entidad']);
                  $templateWord->setValue("mes_vence",ResolucionesController::mes($mes));
                  $templateWord->setValue("dia_vence",$dia);
              }



              $templateWord->setValue("dignatarios",$dig);
              $templateWord->setValue("numero_radicado",$radicado->id_radicado);
              $templateWord->setValue("sade",$radicado->sade);
              $templateWord->setValue("año_radicado",$añoradi);
              $templateWord->setValue("mes_radicado",ResolucionesController::mes($mesradi));
              $templateWord->setValue("dia_radicado",$diaradi);
              $templateWord->setValue("nombre_dignatario",$dignatario[0]['nombre_dignatario']);
              $templateWord->setValue("cedula_dignatario",$dignatario[0]['cedula_dignatario']);
              $templateWord->setValue("municipio_dignatario",$municipio_dignatario['municipio']);
              $templateWord->setValue("departamento_dignatario",$departamento_dignatario['departamento']);
              $templateWord->setValue("nombre_profesional",$profesional['nombre_profesional']);
              $templateWord->setValue("cargo_profesional",$profesional['cargo_profesional']);
              $templateWord->setValue("nombre_usuario",$usuario);
              $templateWord->setValue("cargo_usuario",$cargo);
              $templateWord->setValue("revision",$tipoEntidad['revision']);
              $templateWord->setValue("publicacion_gaceta",$tipoEntidad['gaceta']);
              $templateWord->saveAs('Reforma de estatutos '.$tiporesolucion->nombre_tipo_resolucion.' '.$entidad['nombre_entidad'].'.docx');
              header('Content-Disposition: attachment; filename=Reforma de estatutos '.$tiporesolucion->nombre_tipo_resolucion.' '.$entidad['nombre_entidad'].'.docx; charset=iso-8859-1');
              echo file_get_contents('Reforma de estatutos '.$tiporesolucion->nombre_tipo_resolucion.' '.$entidad['nombre_entidad'].'.docx');

            break;

            case 8:
            //resolucion De reforma de estatutos cambio de tipo
              $templateWord = new TemplateProcessor('plantillas/resolucion reforma de estatutos.docx');
              $resolucion = Resoluciones::findOne($id);
              $tiporesolucion = TipoResolucion::findOne($resolucion->id_tipo_resolucion);
              $entidad = Entidades::findOne($resolucion['id_entidad']);
              $municipio_entidad = Municipios::findOne($entidad['municipio_entidad']);
              $fecha_creacion = $resolucion['fecha_creacion'];
              list($año,$mes,$dia) = explode("-",$fecha_creacion);

              $historial = Historial::findOne($resolucion->id_historial);
              $historial->valor_anterior_campo = str_replace("<", "", $historial->valor_anterior_campo);
              $historial->valor_anterior_campo = str_replace(">", "", $historial->valor_anterior_campo);
              $historial->valor_nuevo_campo = str_replace("<", "", $historial->valor_nuevo_campo);
              $historial->valor_nuevo_campo = str_replace(">", "", $historial->valor_nuevo_campo);
              $cambio = $historial->nombre_evento."<w:br w:type='line'/>"."valor anterior: "
              .$historial->valor_anterior_campo."<w:br w:type='line'/>"."valor nuevo: ".$historial->valor_nuevo_campo;
              $municipio_dignatario = Municipios::findOne($dignatario[0]['id_municipio_expedicion']);
              $departamento_dignatario = Departamentos::findOne($municipio_dignatario['departamento_id']);

              $dig = "";

              $templateWord->setValue("cambio",$cambio);
              $templateWord->setValue("numero_resolucion",$resolucion['numero_resolucion']);
              $templateWord->setValue("fecha_creacion",$resolucion['fecha_creacion']);
              $templateWord->setValue("nombre_entidad",$entidad['nombre_entidad']);
              $templateWord->setValue("municipio_entidad",$municipio_entidad['municipio']);
              $templateWord->setValue("sdia",$dia);
              $templateWord->setValue("smes",ResolucionesController::mes($mes));
              $templateWord->setValue("saño",$año);


              if($entidad['periodo_entidad'] == 0){
                $templateWord->setValue("año_vencimiento",'INDEFINIDO');
                $templateWord->setValue("periodo",'');
                $templateWord->setValue("año_vence",'INDEFINIDO');
                $templateWord->setValue("mes_vence",'');
                $templateWord->setValue("dia_vence",'');
                $templateWord->setValue("año_inscripcion",'INDEFINIDO');
              }else{
                $templateWord->setValue("año_inscripcion",$año);
                $templateWord->setValue("año_vencimiento",$año+$entidad['periodo_entidad']);
                  $templateWord->setValue("periodo",$entidad['periodo_entidad']);
                  $templateWord->setValue("año_vence",$año+$entidad['periodo_entidad']);
                  $templateWord->setValue("mes_vence",ResolucionesController::mes($mes));
                  $templateWord->setValue("dia_vence",$dia);
              }



              $templateWord->setValue("dignatarios",$dig);
              $templateWord->setValue("numero_radicado",$radicado->id_radicado);
              $templateWord->setValue("sade",$radicado->sade);
              $templateWord->setValue("año_radicado",$añoradi);
              $templateWord->setValue("mes_radicado",ResolucionesController::mes($mesradi));
              $templateWord->setValue("dia_radicado",$diaradi);
              $templateWord->setValue("nombre_dignatario",$dignatario[0]['nombre_dignatario']);
              $templateWord->setValue("cedula_dignatario",$dignatario[0]['cedula_dignatario']);
              $templateWord->setValue("municipio_dignatario",$municipio_dignatario['municipio']);
              $templateWord->setValue("departamento_dignatario",$departamento_dignatario['departamento']);
              $templateWord->setValue("nombre_profesional",$profesional['nombre_profesional']);
              $templateWord->setValue("cargo_profesional",$profesional['cargo_profesional']);
              $templateWord->setValue("nombre_usuario",$usuario);
              $templateWord->setValue("cargo_usuario",$cargo);
              $templateWord->setValue("revision",$tipoEntidad['revision']);
              $templateWord->setValue("publicacion_gaceta",$tipoEntidad['gaceta']);
              $templateWord->saveAs('Reforma de estatutos '.$tiporesolucion->nombre_tipo_resolucion.' '.$entidad['nombre_entidad'].'.docx');
              header('Content-Disposition: attachment; filename=Reforma de estatutos '.$tiporesolucion->nombre_tipo_resolucion.' '.$entidad['nombre_entidad'].'.docx; charset=iso-8859-1');
              echo file_get_contents('Reforma de estatutos '.$tiporesolucion->nombre_tipo_resolucion.' '.$entidad['nombre_entidad'].'.docx');

            break;

            case 9:
            // resolucion registro de libros
                $templateWord = new TemplateProcessor('plantillas/resolucion registro de libros.docx');
              $resolucion = Resoluciones::findOne($id);
              $entidad = Entidades::findOne($resolucion['id_entidad']);
              $municipio_entidad = Municipios::findOne($entidad['municipio_entidad']);
              $fecha_creacion = $resolucion['fecha_creacion'];
              list($año,$mes,$dia) = explode("-",$fecha_creacion);
              list($saño,$smes,$sdia) = explode("-",$entidad->fecha_reconocimiento);

              $municipio_dignatario = Municipios::findOne($dignatario[0]['id_municipio_expedicion']);
              $departamento_dignatario = Departamentos::findOne($municipio_dignatario['departamento_id']);
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
              $templateWord->setValue("n_personeria",$entidad->personeria_n);
              $templateWord->setValue("numero_resolucion",$resolucion['numero_resolucion']);
              $templateWord->setValue("fecha_creacion",$resolucion['fecha_creacion']);
              $templateWord->setValue("nombre_entidad",$entidad['nombre_entidad']);
              $templateWord->setValue("municipio_entidad",$municipio_entidad['municipio']);
              $templateWord->setValue("sdia",$dia);
              $templateWord->setValue("smes",ResolucionesController::mes($mes));
              $templateWord->setValue("saño",$año);
              $templateWord->setValue("ssdia",$sdia);
              $templateWord->setValue("ssmes",ResolucionesController::mes($smes));
              $templateWord->setValue("ssaño",$saño);

              if($entidad['periodo_entidad'] == 0){
                $templateWord->setValue("año_vencimiento",'INDEFINIDO');
                $templateWord->setValue("periodo",'');
                $templateWord->setValue("año_vence",'INDEFINIDO');
                $templateWord->setValue("mes_vence",'');
                $templateWord->setValue("dia_vence",'');
                $templateWord->setValue("año_inscripcion",'INDEFINIDO');
              }else{
                $templateWord->setValue("año_inscripcion",$año);
                $templateWord->setValue("año_vencimiento",$año+$entidad['periodo_entidad']);
                  $templateWord->setValue("periodo",$entidad['periodo_entidad']);
                  $templateWord->setValue("año_vence",$año+$entidad['periodo_entidad']);
                  $templateWord->setValue("mes_vence",ResolucionesController::mes($mes));
                  $templateWord->setValue("dia_vence",$dia);
              }



              $templateWord->setValue("dignatarios",$dig);
              $templateWord->setValue("numero_radicado",$radicado->id_radicado);
              $templateWord->setValue("sade",$radicado->sade);
              $templateWord->setValue("año_radicado",$añoradi);
              $templateWord->setValue("mes_radicado",ResolucionesController::mes($mesradi));
              $templateWord->setValue("dia_radicado",$diaradi);
              $templateWord->setValue("nombre_dignatario",$dignatario[0]['nombre_dignatario']);
              $templateWord->setValue("cedula_dignatario",$dignatario[0]['cedula_dignatario']);
              $templateWord->setValue("municipio_dignatario",$municipio_dignatario['municipio']);
              $templateWord->setValue("departamento_dignatario",$departamento_dignatario['departamento']);
              $templateWord->setValue("nombre_profesional",$profesional['nombre_profesional']);
              $templateWord->setValue("cargo_profesional",$profesional['cargo_profesional']);
              $templateWord->setValue("nombre_usuario",$usuario);
              $templateWord->setValue("cargo_usuario",$cargo);
              $templateWord->setValue("revision",$tipoEntidad['revision']);
              $templateWord->setValue("publicacion_gaceta",$tipoEntidad['gaceta']);
              $templateWord->saveAs('Registro de libros '.$entidad['nombre_entidad'].'.docx');
              header('Content-Disposition: attachment; filename=Registro de libros '.$entidad['nombre_entidad'].'.docx; charset=iso-8859-1');
              echo file_get_contents('Registro de libros '.$entidad['nombre_entidad'].'.docx');


            break;

            default:
              # code...
              break;
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


    protected function findModel($id)
    {
        if (($model = Resoluciones::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
