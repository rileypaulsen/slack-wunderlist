<?php

class Auth {

	public static function addHeaders(){
		return array(
			'X-Access-Token' => Secret::ACCESS_TOKEN,
			'X-Client-ID' => Secret::CLIENT_ID
		);
	}

}