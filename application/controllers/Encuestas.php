<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once( APPPATH.'/libraries/REST_Controller.php' );
use Restserver\libraries\REST_Controller;


class Encuestas extends REST_Controller {


  public function __construct(){

    header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
    header("Access-Control-Allow-Origin: *");


    parent::__construct();
    $this->load->database();

  }

  public function encuestas_hoy_get( $usuario_id = 0 ){

    $encuestas_retornar = array( );

    $condicion = array( 'usuario_id' => $usuario_id, 'fecha' => date('Y-m-d'));

    $this->db->select('id, coordenada_x, coordenada_y');
    $this->db->where($condicion);
    $query = $this->db->get('tbl_encuestas_realizadas');

    $encuestas_id = $query->result();

    foreach ($encuestas_id as $key => $value) {

      $query = $this->db->query('select p.campo, rr.respuesta
        from tbl_preguntas as p, tbl_respuestas_realizadas as rr where rr.encuesta_realizada_id = '.$value->id.' and p.id = rr.pregunta_id');

      $respuestas = $query->result_array();

      $encuestas_retornar[] = array( 'id' => $value->id, 'coordenada_x' => $value->coordenada_x, 'coordenada_y' => $value->coordenada_y, 'respuestas' => $respuestas);

     }

    $respuesta = array(
            'error' => FALSE,
            'encuestas' => $encuestas_retornar
          );

    $this->response( $respuesta );
  }

  public function encuestas_todas_get( $usuario_id ){

    $encuestas_retornar = array( );

    $this->db->select('id, coordenada_x, coordenada_y');
    $this->db->where('usuario_id', $usuario_id);
    $query = $this->db->get('tbl_encuestas_realizadas');

    $encuestas_id = $query->result();

    foreach ($encuestas_id as $key => $value) {

      $query = $this->db->query('select p.campo, rr.respuesta
        from tbl_preguntas as p, tbl_respuestas_realizadas as rr where rr.encuesta_realizada_id = '.$value->id.' and p.id = rr.pregunta_id');

      $respuestas = $query->result_array();

      $encuestas_retornar[] = array( 'id' => $value->id, 'coordenada_x' => $value->coordenada_x, 'coordenada_y' => $value->coordenada_y, 'respuestas' => $respuestas);

     }

    $respuesta = array(
            'error' => FALSE,
            'encuestas' => $encuestas_retornar
          );

    $this->response( $respuesta );
  }

  public function guardar_encuestas_post(){

    $data = array();

    $data_post = $this->post();

    foreach ($data_post as $key => $value) {

      if ( $key ) {
        if ( is_int( $key) ) {
          $new_key =  str_replace(' ', '_', $key);
          $data[$new_key] = $value;
        }
      }

    }

    $data_post['fecha'] = date('Y-m-d');

    $encuesta = array( 'usuario_id' => $data_post['usuario_id'], 'encuesta_id' => $data_post['encuesta_id'], 'fecha' => $data_post['fecha'], 'coordenada_x' => $data_post['x'], 'coordenada_y' => $data_post['y'] );

    $this->db->insert('tbl_encuestas_realizadas',$encuesta);

    $encuesta_id = $this->db->insert_id();

    foreach ($data as $key => $value) {

     if ( is_array( $value )) {
       foreach ($value as $value_array) {

         if ( is_array($value_array)) {
           
         
          $respuesta2 = array( 'respuesta' => $value_array['respuesta'], 'pregunta_id' => $key, 'encuesta_realizada_id' => $encuesta_id);
          $this->db->insert('tbl_respuestas_realizadas', $respuesta2);

          $respuesta_id = $this->db->insert_id();

          
          foreach ($value_array['detalles'] as  $detalle) {

            $respuesta2 = array( 'respuesta_detalle' => $detalle['value'], 'id_campo_detalle' => $detalle['id'], 'id_respuesta_realizada' => $respuesta_id);
            $this->db->insert('tbl_respuestas_detalles', $respuesta2);
          }


         }else {
            $respuesta2 = array( 'respuesta' => $value_array, 'pregunta_id' => $key, 'encuesta_realizada_id' => $encuesta_id);
            $this->db->insert('tbl_respuestas_realizadas', $respuesta2);
         }

         
       }
     }else {
       $respuesta2 = array( 'respuesta' => $value, 'pregunta_id' => $key, 'encuesta_realizada_id' => $encuesta_id);
       $this->db->insert('tbl_respuestas_realizadas', $respuesta2);
     }
    }

    $respuesta = array(
                  'error' => FALSE,
                  'encuestas' => $data_post
                );

    $this->response( $respuesta );
  }

  public function obtener_encuestas_get(){

    $this->db->where('fecha_inicial<=', date('Y-m-d'))->where('fecha_final>=', date('Y-m-d'));
    $query = $this->db->get('tbl_encuestas');

    $encuestas = $query->result();

    $encuestas = json_decode(json_encode($encuestas), True);

    foreach ($encuestas as $key => $value) {

      $query = $this->db->query('select p.id, p.campo, tp.tipo_pregunta , p.dependencia_pregunta_id "depende" from public.tbl_preguntas as p, tbl_tipos_preguntas as tp
        where p.encuesta_id = '.$value['id'].' and tp.id = p.tipo_pregunta_id order by p.posicion asc');

      $preguntas = $query->result_array();

      $preguntas = json_decode(json_encode($preguntas), True);

      $encuestas[$key]['campos'] = array();

      foreach ($preguntas as $key2 => $value2) {
        //$value2['id'] = $key2+1;
        $value2['value'] = '';
        $value2['respuestas'] = array();
        $value2['dependencias'] = array();

        if ( $value2['depende'] === null) {
          unset($value2['depende']);
        }else {
          $value2['depende'] = intval($value2['depende']);
        }

        $query = $this->db->query('select id, respuesta from public.tbl_respuestas as r where r.pregunta_id = '.$value2['id']);

        $respuestas = $query->result_array();

        $respuestas = json_decode(json_encode($respuestas), True);

        foreach ($respuestas as $respuesta) {

          if ( $value2['tipo_pregunta'] === 'select_multiple [detail]'){
           
            $query = $this->db->query("select id, campo_detalle, '' as value from tbl_campos_detalles_respuestas where id_respuesta =".$respuesta['id']);
            
            $detalles = $query->result_array();
  
            $detalles = json_decode(json_encode($detalles), True);

            $value2['respuestas'][] = array( 'respuesta' => $respuesta['respuesta'],
                                              'detalles' => $detalles);

          }else {
            $value2['respuestas'][] = $respuesta['respuesta'];
          }

        }

        $query = $this->db->query('select r.respuesta
        from tbl_preguntas_dependencias as pd
        inner join tbl_respuestas as r on pd.respuesta_id=r.id
        where pd.pregunta_id = '.$value2['id']);

        $dependencias = $query->result_array();

        $dependencias = json_decode(json_encode($dependencias), True);

        foreach ($dependencias as $dependencia) {
          $value2['dependencias'][] = $dependencia['respuesta'];
        }

        $encuestas[$key]['campos'][] = $value2;

      }
    }

    $respuesta = array(
            'error' => FALSE,
            'encuestas' => $encuestas
          );

    $this->response( $encuestas );

  }

}
