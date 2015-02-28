[![Build Status](https://travis-ci.org/michalkopacz/oauth2-client.svg?branch=develop)](https://travis-ci.org/michalkopacz/oauth2-client)

# Usage

```php
use MostSignificantBit\OAuth2\Client\Client as OAuth2Client;
use MostSignificantBit\OAuth2\Client\Config\Config;
use MostSignificantBit\OAuth2\Client\Grant\ResourceOwnerPasswordCredentials\AccessTokenRequest;
use MostSignificantBit\OAuth2\Client\Grant\ResourceOwnerPasswordCredentials\ResourceOwnerPasswordCredentialsGrant;
use MostSignificantBit\OAuth2\Client\Parameter\Password;
use MostSignificantBit\OAuth2\Client\Parameter\Username;
use MostSignificantBit\OAuth2\Client\Exception\TokenException;
use Ivory\HttpAdapter\GuzzleHttpHttpAdapter;

$httpClient = new GuzzleHttpHttpAdapter(new \GuzzleHttp\Client());

$config = new Config(array(
    'endpoint' => array(
        'token_endpoint_uri' => 'http://127.0.0.1:8000/oauth2/token',
    ),
    'client' => array(
        'credentials' => array(
            'client_id' => 's6BhdRkqt3',
            'client_secret' => '7Fjfp0ZBr1KtDRbnfVdmIw',
        ),
    ),
));

$oauth2Client = new OAuth2Client($httpClient, $config);

$accessTokenRequest = new AccessTokenRequest(new Username('johndoe'), new Password('A3ddj3w'));

$grant = new ResourceOwnerPasswordCredentialsGrant($accessTokenRequest);

try {
    $accessTokenResponse = $oauth2Client->obtainAccessToken($grant);
} catch (TokenException $exception) {
    //log exception
}
```