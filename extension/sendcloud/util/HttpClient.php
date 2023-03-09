<?php
require_once 'Response.php';
class HttpClient {
	public function __construct($host) {
		$this->host = $host;
	}
	
	public function post($method, $url,$header,$param){
		
		$header = !empty( $header ) ? $header :'Content-type: application/x-www-form-urlencoded ';
		$options = array (
				'http' => array (
						'method' => 'POST',
						'header' => $header,
						'content' => http_build_query($param)
				)
		);
		$url = $this->host . "" . $url;
		$context = stream_context_create ( $options );
		$result = file_get_contents ( $url, false, $context );
		return new Response ( $result );
	}
	
	public function mutilpost($method, $url, $body,$header) {
		$header = !empty ( $header ) ? $header :'Content-type: application/x-www-form-urlencoded ';
		$options = array (
				'http' => array (
						'method' => 'POST',
						'header' => $header,
						'content' => $body
				) 
		);
		$url = $this->host . "" . $url;
		$context = stream_context_create ( $options );
		$result = file_get_contents ( $url, false, $context );
		return new Response ( $result );
	}
}