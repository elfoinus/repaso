<?php

namespace app\models;
use yii\web\UploadedFile;
use Yii;

/**
 * This is the model class for table "entidades".
 *
 * @property integer $id_entidad
 * @property integer $personeria_year
 * @property integer $personeria_n
 * @property string $nombre_entidad
 * @property string $fecha_reconocimiento
 * @property integer $municipio_entidad
 * @property string $direccion_entidad
 * @property string $telefono_entidad
 * @property string $fax_entidad
 * @property string $email_entidad
 * @property integer $id_tipo_entidad
 * @property integer $id_clase_entidad
 * @property string $objetivos_entidad
 * @property string $fecha_estatutos
 * @property string $ubicacion_archivos_entidad
 * @property string $fecha_gaceta
 * @property resource $datos_digitales
 * @property integer $estado_entidad
 *
 * @property Dignatarios[] $dignatarios
 * @property TipoEntidad $idTipoEntidad
 * @property ClaseEntidad $idClaseEntidad
 * @property Municipios $municipioEntidad
 * @property Resoluciones[] $resoluciones
 */
class Entidades extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'entidades';
    }
     public $file;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['personeria_year', 'personeria_n', 'nombre_entidad', 'fecha_reconocimiento', 'direccion_entidad', 'telefono_entidad', 'email_entidad', 'id_tipo_entidad', 'id_clase_entidad', 'objetivos_entidad', 'fecha_estatutos','municipio_entidad', 'estado_entidad','periodo_entidad'], 'required'],
            [['personeria_year', 'personeria_n', 'municipio_entidad', 'id_tipo_entidad', 'id_clase_entidad', 'estado_entidad','periodo_entidad'], 'integer'],
            [['fecha_reconocimiento', 'fecha_estatutos', 'fecha_gaceta'], 'safe'],
            ['email_entidad','email'],
            [['file'], 'file', 'skipOnEmpty' => true, 'extensions' => 'doc,pdf,docx', 'maxSize'=> '10485760'], //10485760 = 10 * 1024 * 1024
            [['objetivos_entidad', 'datos_digitales'], 'string'],
            [['nombre_entidad', 'direccion_entidad'], 'string', 'max' => 255, 'min' => 4],
            [['nombre_entidad'], 'unique'],
            [['telefono_entidad', 'fax_entidad', 'ubicacion_archivos_entidad'], 'string', 'max' => 20],
            [['id_tipo_entidad'], 'exist', 'skipOnError' => true, 'targetClass' => TipoEntidad::className(), 'targetAttribute' => ['id_tipo_entidad' => 'id_tipo_entidad']],
            [['id_clase_entidad'], 'exist', 'skipOnError' => true, 'targetClass' => ClaseEntidad::className(), 'targetAttribute' => ['id_clase_entidad' => 'id_clase_entidad']],
            [['municipio_entidad'], 'exist', 'skipOnError' => true, 'targetClass' => Municipios::className(), 'targetAttribute' => ['municipio_entidad' => 'id_municipio']],
        ];
    }



    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id_entidad' => 'ID',
            'personeria_year' => 'Año Personeria',
            'personeria_n' => 'Personeria Numero',
            'nombre_entidad' => 'Nombre Entidad',
            'fecha_reconocimiento' => 'Fecha Reconocimiento',
            'municipio_entidad' => 'Municipio Entidad',
            'direccion_entidad' => 'Direccion Entidad',
            'telefono_entidad' => 'Telefono Entidad',
            'fax_entidad' => 'Fax Entidad',
            'email_entidad' => 'Email Entidad',
            'id_tipo_entidad' => 'Tipo Entidad',
            'id_clase_entidad' => 'Clase Entidad',
            'objetivos_entidad' => 'Objetivos Entidad',
            'fecha_estatutos' => 'Fecha Estatutos',
            'ubicacion_archivos_entidad' => 'Ubicacion Archivos Entidad',
            'fecha_gaceta' => 'Fecha Gaceta',
            'datos_digitales' => 'Datos Digitales',
            'file' => 'archivo',
            'estado_entidad' => 'Estado Entidad',
            'periodo_entidad' => 'Periodo de la entidad  años:'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDignatarios()
    {
        return $this->hasMany(Dignatarios::className(), ['id_entidad' => 'id_entidad']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIdTipoEntidad()
    {
        return $this->hasOne(TipoEntidad::className(), ['id_tipo_entidad' => 'id_tipo_entidad']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIdClaseEntidad()
    {
        return $this->hasOne(ClaseEntidad::className(), ['id_clase_entidad' => 'id_clase_entidad']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMunicipioEntidad()
    {
        return $this->hasOne(Municipios::className(), ['id_municipio' => 'municipio_entidad']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getResoluciones()
    {
        return $this->hasMany(Resoluciones::className(), ['id_entidad' => 'id_entidad']);
    }

    public function NombreTipoEntidad()
    {
        $data = TipoEntidad::findOne($this->id_tipo_entidad);

        return $data['tipo_entidad'];
    }

    public function NombreClaseEntidad()
    {
      $data = ClaseEntidad::findOne($this->id_clase_entidad);
      return $data['clase_entidad'];
    }

    public function getDatosdigitales(){
      return $this->datos_digitales;
    }
}
