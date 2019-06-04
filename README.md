# Okta Simple JWT Verifier
Okta Simple JWT Verifier is a simple stand-alone PHP class that can be used to verify JWT tokens issued by Okta orgs.

## Copyright
This PHP class was built based on [okta/okta-jwt-verifier-php](https://github.com/okta/okta-jwt-verifier-php) and [firebase/php-jwt](https://github.com/firebase/php-jwt).

## Requirements
* An Okta account, called an _organization_ (you can sign up for a free [developer organization](https://developer.okta.com/signup/))
* A local web server that runs PHP 7.0

## Methods available
### __construct($jwt)
The PHP class can be initiated through `new OktaSimpleJWTVerifier($jwt)`, where $jwt is the JWT token that needs to be verified.

### setAudience($audience)
This method sets an audience to be checked in the JWT token.

### setClientId($clientId)
This method sets a client ID to be checked inside "cid" claim, which is present in access tokens.

### setIssuer($issuer)
This method sets an issuer to be checked in the JWT token.

### verify()
This method verifies the following details inside a JWT token:
* algorithm inside header to be set to RS256 (the current supported algorithm)
* issued time to not be in the future (rare cases in which the time of the server is not alligned correctly to UTC timezone)
* expiration time to not be in the past (in this case, the token provided would be expired)
* audience claim (if `setAudience($audience)` was added previously)
* client ID claim (if `setClientId($clientId)` was added previously)
* issuer claim (if `setIssuer($issuer)` was added previously)
* signature

The result of this method is boolean (`TRUE` or `FALSE`).

## Example
The following example takes a JWT token, verifies it and, based on the response, it returns a feedback.

```php
$jwt = new OktaSimpleJWTVerifier("eyJraWQiOiJfbTE0cC02emlzY3Vfd2RUekM4VmlKRDNBSTl1VU9qT3pDSHllMjNLcVF3IiwiYWxnIjoiUlMyNTYifQ.eyJ2ZXIiOjEsImp0aSI6IkFULlNid2ZReGNHQm5WVVFPTEN0bTg5S2RaUkJDeW9NOXBBRVZlLUwyWFpWeG8iLCJpc3MiOiJodHRwczovL2RyYWdvcy5va3RhcHJldmlldy5jb20vb2F1dGgyL2F1c2w2dG80NHkyNkp0eUdQMGg3IiwiYXVkIjoiaHR0cHM6Ly9kZXYub2t0YS5hZG1pbnBhbmVsLmJpeiIsImlhdCI6MTU1OTY1NTgzNSwiZXhwIjoxNTU5NjU5NDM1LCJjaWQiOiIwb2FsNnA4emdxT2VhTDg1QzBoNyIsInVpZCI6IjAwdWVheThqY2Q1a2tNV3MyMGg3Iiwic2NwIjpbIm9wZW5pZCIsInByb2ZpbGUiXSwic3ViIjoiZHJhZ29zLmdhZnRvbmVhbnVAZ21haWwuY29tIn0.EamQpMdyei-Zf-4NwbFHCoHfK8UncYFuvK5w-TpQIPdSVBsgxHqoQr_Ez5GqURniNKvt-XT4ZF2CAcSrW17R2Gdjyls7vXCyDWRKCV4D06a9qGXDdvxmkLJgzF5bE3d3TV3DHlqtts69IHDVugPZvYQRnSPaWG6MJFl2Sz80W38Quj0IUupz4AdSL-eUCB5gZkNbf73e0dwUO59MRGG_g_RGkZyyLROo_TwxMyXdTZiIqKeLCW-XA8KDn64DgJQydKTKRlFO4YXU1vLvK4qpCvi6JJf0zvqDKBt_KH9jBaM8qNLIOjB6ppzpCa6s7OrRSLNymhvAF_3IvTUKXOfxEg");

if($jwt->verify())
	echo "JWT has been successfully verified.";
else
	echo $jwt->getError();
```
	
The response returned will be

```
JWT has been successfully verified.
```

## Disclaimer
The script is provided AS IS without warranty of any kind. Okta disclaims all implied warranties including, without limitation, any implied warranties of fitness for a particular purpose. We highly recommend testing scripts in a preview environment if possible.