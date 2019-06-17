<?php
/** Copyright © 2019, Okta, Inc.
 *
 *  Licensed under the Apache License, Version 2.0 (the 'License');
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an 'AS IS' BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */
namespace Okta\SimpleJWTVerifier;
use Exception;

class SimpleJWTVerifier extends Exception
{
	protected $jwt, $audience = "", $clientId = "", $issuer = "", $error = "", $pem = "", $nonce = "";
	
	public function __construct($jwt)
	{
		$this->jwt = $jwt;
	}
	
	public function setAudience($audience)
	{
		$this->audience = $audience;
	}
	
	public function setClientId($clientId)
	{
		$this->clientId = $clientId;
	}
	
	public function setIssuer($issuer)
	{
		$this->issuer = $issuer;
	}
	
	public function setPem($pem)
	{
		$this->pem = $pem;
	}
	
	public function setNonce($nonce)
	{
		$this->nonce = $nonce;
	}
	
	public function verify()
	{
		if(!stristr($this->jwt, "."))
			$this->error("The JWT provided does not contain a delimiter between header, payload and signature.");
		
		$part = explode(".",$this->jwt);
		
		if(count($part)!=3)
			$this->error("The JWT provided does not contain the expected structure.");
		
		$head = json_decode(base64_decode($part[0]),1);
		$body = json_decode(base64_decode($part[1]),1);
		
		if($head['alg'] != "RS256")
			$this->error("The JWT token is generated through an unsupported algorithm.");
		
		if($body['iat'] > time())
			$this->error("The JWT was issued in the future.");
		
		if($body['exp'] < time())
			$this->error("The JWT is expired.");
		
		if($this->audience != "")
			if($this->audience != $body['aud'])
				$this->error("The JWT does not contain the expected audience.");

		if($this->clientId != "")
			if($this->clientId != $body['cid'])
				$this->error("The JWT does not contain the expected client ID.");
		
		if($this->issuer != "")
			if($this->issuer != $body['iss'])
				$this->error("The JWT does not contain the expected issuer.");

		if($this->nonce != "")
			if($this->nonce != $body['nonce'])
				$this->error("The JWT does not contain the expected nonce.");			
		
		$keys = json_decode(file_get_contents(json_decode(file_get_contents($body['iss'] . "/.well-known/openid-configuration"),1)['jwks_uri']),1)['keys'];
		
		foreach($keys as $key)
		{
			$kid_exists = 0;
			if($key['kid'] == $head['kid'])
			{
				$kid_exists = 1;
				if($this->pem != "")
					$pem = $this->pem;
				else
					$pem = $this->createPemFromModulusAndExponent($key['n'], $key['e']);
				break;
			}
		}
		
		if($kid_exists == 0)
			$this->error("The signing key for the token was not found under /keys endpoint.");		
			
		
		if(openssl_verify($part[0] . "." . $part[1], $this->urlsafeB64Decode($part[2]), $pem, OPENSSL_ALGO_SHA256))
		{
			return $body;
		}else{
			$this->error("The signature could not be verified.");
		}
	}

	private function error($message)
	{
		throw new \Exception(
			json_encode(array(
				"error" => array(
					"errorSummary" => $message
				)
			),JSON_UNESCAPED_SLASHES)
		);
	}

    public function createPemFromModulusAndExponent($n, $e)
    {
        $modulus = $this->urlsafeB64Decode($n);
        $publicExponent = $this->urlsafeB64Decode($e);
        $components = array(
            'modulus' => pack('Ca*a*', 2, $this->encodeLength(strlen($modulus)), $modulus),
            'publicExponent' => pack('Ca*a*', 2, $this->encodeLength(strlen($publicExponent)), $publicExponent)
        );
        $RSAPublicKey = pack(
            'Ca*a*a*',
            48,
            $this->encodeLength(strlen($components['modulus']) + strlen($components['publicExponent'])),
            $components['modulus'],
            $components['publicExponent']
        );

        $rsaOID = pack('H*', '300d06092a864886f70d0101010500'); 
        $RSAPublicKey = chr(0) . $RSAPublicKey;
        $RSAPublicKey = chr(3) . $this->encodeLength(strlen($RSAPublicKey)) . $RSAPublicKey;
        $RSAPublicKey = pack(
            'Ca*a*',
            48,
            $this->encodeLength(strlen($rsaOID . $RSAPublicKey)),
            $rsaOID . $RSAPublicKey
        );
        $RSAPublicKey = "-----BEGIN PUBLIC KEY-----\r\n" .
            chunk_split(base64_encode($RSAPublicKey), 64) .
            '-----END PUBLIC KEY-----';
        return $RSAPublicKey;
    }

    private function urlsafeB64Decode($input)
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }
	
    private function encodeLength($length)
    {
        if ($length <= 0x7F) {
            return chr($length);
        }
        $temp = ltrim(pack('N', $length), chr(0));
        return pack('Ca*', 0x80 | strlen($temp), $temp);
    }
}