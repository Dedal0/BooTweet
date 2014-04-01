<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>BooTweet</title>
	<link rel="stylesheet" href="css/normalize.css">
	<link rel="stylesheet" href="css/foundation.min.css">
</head>
<body> 
	<?php 

	$boot = new BooTwitter();

	$username 	= @htmlspecialchars($_POST['user']);
	$password 	= @htmlspecialchars($_POST['password']);
	$comment 	= @htmlspecialchars($_POST['comment']);

	if ($_POST && $username && $password && $comment) {
		 
		// Ingresa los accesos
		$boot->setUserName($username);
		$boot->setPassword($password);

		// Inicia boot
		$boot->startBoot();

		// Envia Mensaje
		$boot->sendMessage($comment);

		// Cierra conexion y session de boot
		$boot->endBoot();
	} else {
		// Muestra formulario 
		echo $boot->showLoginForm();	
	}

	?>
</body>
</html>

<?php

/**
 * BooTweet
 * 
 * By Camilo Galdos @SeguridadBlanca
 * Modified by Esteban Rodriguez @Pnkkito
 */

class BooTwitter
{
	private $_tweet 		= 	'BooTweet Message by @SeguridadBlanca Modified by @Pnkkito';
	private $_user 			= 	'';
	private $_passwd 		= 	'';

	private $_conn 			= 	null;
	private $_url 			= 	'';
	private $_token 		= 	''; 

	private $_user_agent	= 	'Mozilla/5.0 (compatible; MSIE 9.0; Windows Phone OS 7.5; Trident/5.0; IEMobile/9.0; NOKIA; Lumia 800)';

	private $_cookie		=	'cookies.txt_file';

	public function __construct(){ 
		$this->_startConnection();
	}

	public function __destruct(){
		$this->_tweet 	= 'BooTwitter Destroyed';
		$this->_user 	= '';
		$this->_passwd 	= '';

		$this->_conn 	= null;
	}

	private function _startConnection(){
		$this->_conn = curl_init();
	}

	private function _endConnection()
	{
		curl_close($this->_conn);
	}

	private function _sendRequest($post = null){

		if ($this->_conn) {
			curl_setopt($this->_conn, CURLOPT_URL, $this->_url);
			if($post){
				curl_setopt($this->_conn,CURLOPT_POSTFIELDS, $post);
				curl_setopt($this->_conn, CURLOPT_POST, 1);
			}
			else{
				curl_setopt($this->_conn, CURLOPT_POST, 0);
            }

             //Tor address & port
            $tor = '127.0.0.1:9050';

            //Set proxy type
            curl_setopt($this->_conn, CURLOPT_PROXY, $tor);


            curl_setopt($this->_conn, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);

            curl_setopt($this->_conn, CURLOPT_RETURNTRANSFER, true);
                
                curl_setopt($this->_conn, CURLOPT_COOKIEJAR, $this->_cookie);
			curl_setopt($this->_conn, CURLOPT_HEADER, 0);
			curl_setopt($this->_conn, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($this->_conn, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($this->_conn, CURLOPT_USERAGENT, $this->_user_agent);
			return curl_exec($this->_conn);
		}

		return null; 
	}

	private function _getToken($html){
		preg_match("/input name=\"authenticity_token\" type=\"hidden\" value=\"(.*?)\"/", $html, $authenticity_token);
	
	 	return $authenticity_token[1];
	}

	private function _getAccess(){
		//
		$this->setUrl('https://mobile.twitter.com/session/new');

		//
		$html_request 	= 	$this->_sendRequest();
		$this->_token 	=  	$this->_getToken($html_request); 
		 
	}

	private function _getLogin(){
		//
		$this->setUrl('https://mobile.twitter.com/session');

		//
		$html_request 	= 	$this->_sendRequest('authenticity_token='.$this->_token.'&username='.$this->_user.'&password='.$this->_passwd); 
	} 

	public function sendMessage($message = null){
		//
		$this->setUrl('https://mobile.twitter.com/');

		if ($message) {
			$this->_tweet = $message;
		}
		//
		$html_request 	= 	$this->_sendRequest('authenticity_token='.$this->_token.'&tweet[text]='.$this->_tweet.'&commit=Tweet'); 
	}

	private function _closeLogin(){
		//
		$this->setUrl('https://mobile.twitter.com/session/destroy');

		//
		$html_request 	= 	$this->_sendRequest('authenticity_token='.$this->_token.'&commit=Sign out'); 
	}

	// SETTERS
	public function setUserName($username){
		$this->_user 	= $username;
	}

	public function setPassword($password){
		$this->_passwd 	= $password;
	}

	public function setMessage($message){
		$this->_tweet 	= $message;
	}

	public function setUrl($url){
		$this->_url = $url;
	}

	public function setUserAgent($user_agent){
		$this->_user_agent = $user_agent;
	}

	public function startBoot(){
		$this->_getAccess();
		$this->_getLogin();
	}

	public function endBoot(){
		$this->_closeLogin();
		$this->_endConnection();
	}

	public function showLoginForm(){
		$form 	= '';
		$form  .= '<div class="row">';
			$form  .= '<div class="large-5 large-centered columns"> ';
				$form  .= '<form action="" method="post">';
			  		$form  .= '<fieldset>';
			    		$form  .= '<legend>Login</legend> ';
			    		$form  .= '<label>Username';
			      			$form  .= '<input type="text" name="user" id="" placeholder="Username" required>';
			    		$form  .= '</label>';
			    		$form  .= '<label>Password';
			      			$form  .= '<input type="password" name="password" id="" placeholder="******" required>';
			    		$form  .= '</label>';
			    		$form  .= '<label>Tweet';
			      			$form  .= '<input type="text" name="comment" id="" placeholder="Tweet Message" required>';
			    		$form  .= '</label>';
			    		$form  .= '<button type="submit" class="button right">Login</button>';
			  		$form  .= '</fieldset>';
				$form  .= '</form>';
			$form  .= '</div>';
		$form  .= '</div>';

		return $form;
	}
	// GETTERS
}
