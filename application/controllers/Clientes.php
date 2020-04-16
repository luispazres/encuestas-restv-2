<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once( APPPATH.'/libraries/REST_Controller.php' );
use Restserver\libraries\REST_Controller;


class Clientes extends REST_Controller {


  public function __construct(){

    header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
    header("Access-Control-Allow-Origin: *");


    parent::__construct();
    $this->load->database();

  }

  public function index(){

    echo "Hola Mundo";

  }

  public function obtener_clientes_get(){

    $query = $this->db->get('clientes_tbl');

    $respuesta = array(
            'error' => FALSE,
            'clientes' => $query->result_array()
          );

    $this->response( $respuesta );

  }

  public function obtener_notificaciones_get(){

      $curl = curl_init();

      curl_setopt_array($curl, array(
        CURLOPT_URL => "https://onesignal.com/api/v1/notifications?app_id=5dd271e1-494e-4d4c-983c-8714affeca24&limit=10&offset=0",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
          "authorization: Basic OTgzZTU1MTUtYmZiOC00M2QxLWE2ZGItYTFjN2I0OTgyOGYz",
        ),
      ));

      $response = curl_exec($curl);
      $err = curl_error($curl);

      curl_close($curl);

      $this->response( $response );

  }

}
