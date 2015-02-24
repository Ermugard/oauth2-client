<?php
namespace MostSignificantBit\OAuth2\Client\Tests\Unit;

use MostSignificantBit\OAuth2\Client\Config\Config;
use MostSignificantBit\OAuth2\Client\Client as OAuth2Client;
use MostSignificantBit\OAuth2\Client\AccessToken\SuccessfulResponse as AccessTokenSuccessfulResponse;
use MostSignificantBit\OAuth2\Client\Grant\AuthorizationCode\AuthorizationCodeGrant;
use MostSignificantBit\OAuth2\Client\Grant\ResourceOwnerPasswordCredentials\AccessTokenRequest;
use MostSignificantBit\OAuth2\Client\Grant\ResourceOwnerPasswordCredentials\ResourceOwnerPasswordCredentialsGrant;
use MostSignificantBit\OAuth2\Client\Grant\AuthorizationCode\AuthorizationRequest;
use MostSignificantBit\OAuth2\Client\Http\Response;
use MostSignificantBit\OAuth2\Client\Parameter\AccessToken;
use MostSignificantBit\OAuth2\Client\Parameter\ExpiresIn;
use MostSignificantBit\OAuth2\Client\Parameter\Password;
use MostSignificantBit\OAuth2\Client\Parameter\RefreshToken;
use MostSignificantBit\OAuth2\Client\Parameter\Scope;
use MostSignificantBit\OAuth2\Client\Parameter\TokenType;
use MostSignificantBit\OAuth2\Client\Parameter\Username;

/**
 * @group unit
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{
    public function testGetAccessTokenResourceOwnerPasswordCredentialsGrant()
    {
        $httpClient = $this->getHttpClientMock();

        $oauth2Response = new Response();
        $oauth2Response->setStatusCode(200);
        $oauth2Response->setBody(array(
            'access_token' => '2YotnFZFEjr1zCsicMWpAA',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'refresh_token' => 'tGzv3JOkF0XG5Qx2TlKWIA',
            'scope' => 'example1 example2',
        ));

        $httpClient->expects($this->once())
            ->method('postAccessToken')
            ->with(
                $this->equalTo('https://auth.example.com/token'),
                $this->equalTo(array(
                    'body' => array(
                        'grant_type' => 'password',
                        'username' => 'johndoe',
                        'password' => 'A3ddj3w',
                    ),
                    'credentials' => array(
                        'client_id' => 's6BhdRkqt3',
                        'client_secret' => '7Fjfp0ZBr1KtDRbnfVdmIw',
                    )
                )),
                $this->equalTo(array(
                    'authentication_type' => Config::CLIENT_HTTP_BASIC_AUTHENTICATION_TYPE,
                    'client_type' => Config::CLIENT_CONFIDENTIAL_TYPE,
                ))
            )
            ->willReturn($oauth2Response);

        $config = $this->getConfig();

        $oauth2Client = new OAuth2Client($httpClient, $config);

        $accessTokenExpectedResponse = new AccessTokenSuccessfulResponse(new AccessToken('2YotnFZFEjr1zCsicMWpAA'), TokenType::BEARER());
        $accessTokenExpectedResponse->setExpiresIn(new ExpiresIn(3600));
        $accessTokenExpectedResponse->setRefreshToken(new RefreshToken('tGzv3JOkF0XG5Qx2TlKWIA'));
        $accessTokenExpectedResponse->setScope(new Scope(array('example1', 'example2')));

        $accessTokenRequest = new AccessTokenRequest(new Username('johndoe'), new Password('A3ddj3w'));

        $grant = new ResourceOwnerPasswordCredentialsGrant($accessTokenRequest);

        $accessTokenResponse = $oauth2Client->obtainAccessToken($grant);

        $this->assertEquals($accessTokenExpectedResponse, $accessTokenResponse);
    }

    /**
     * @expectedException \MostSignificantBit\OAuth2\Client\Exception\TokenException
     * @expectedExceptionCode 1
     * @expectedExceptionMessage Invalid request
     */
    public function testInvalidRequestAccessTokenResourceOwnerPasswordCredentialsGrant()
    {
        $httpClient = $this->getHttpClientMock();

        $oauth2Response = new Response();
        $oauth2Response->setStatusCode(400);
        $oauth2Response->setBody(array(
            'error' => 'invalid_request',
            'error_description' => 'Invalid request',
            'error_uri' => 'https://auth.example.com/oauth2/errors/invalid_request'
        ));

        $httpClient->expects($this->once())
            ->method('postAccessToken')
            ->with(
                $this->equalTo('https://auth.example.com/token'),
                $this->equalTo(array(
                    'body' => array(
                        'grant_type' => 'password',
                        'username' => 'johndoe',
                        'password' => 'wrong_password',
                    ),
                    'credentials' => array(
                        'client_id' => 's6BhdRkqt3',
                        'client_secret' => '7Fjfp0ZBr1KtDRbnfVdmIw',
                    )
                )),
                $this->equalTo(array(
                    'authentication_type' => Config::CLIENT_HTTP_BASIC_AUTHENTICATION_TYPE,
                    'client_type' => Config::CLIENT_CONFIDENTIAL_TYPE,
                ))
            )
            ->willReturn($oauth2Response);

        $config = $this->getConfig();

        $oauth2Client = new OAuth2Client($httpClient, $config);

        $accessTokenRequest = new AccessTokenRequest(new Username('johndoe'), new Password('wrong_password'));

        $grant = new ResourceOwnerPasswordCredentialsGrant($accessTokenRequest);

        try {
            $oauth2Client->obtainAccessToken($grant);
        } catch (TokenException $exception) {
            $this->assertSame('https://auth.example.com/oauth2/errors/invalid_request', $exception->getErrorUri());

            throw $exception;
        }
    }

    public function testGetAuthorizationRequestUriForCodeResponseType()
    {
        $httpClient = $this->getHttpClientMock();

        $config = $this->getConfig();

        $oauth2Client = new OAuth2Client($httpClient, $config);

        $authorizationRequest = new AuthorizationRequest();
        $authorizationRequest->setScope(new Scope(array('scope-token-1', 'scope-token-2')));

        $grant = new AuthorizationCodeGrant(null, $authorizationRequest);

        $uri = $oauth2Client->buildAuthorizationRequestUri($grant);

        $this->assertSame('https://auth.example.com/authorize?response_type=code&client_id=s6BhdRkqt3&scope=scope-token-1+scope-token-2', $uri);
    }

    protected function getHttpClientMock()
    {
        return $this->getMockBuilder('\MostSignificantBit\OAuth2\Client\Http\ClientInterface')
            ->setMethods(array('postAccessToken'))
            ->getMockForAbstractClass();
    }

    protected function getConfig()
    {
        return new Config(array(
            'endpoint' => array(
                'token_endpoint_uri' => 'https://auth.example.com/token',
                'authorization_endpoint_uri' => 'https://auth.example.com/authorize',
            ),
            'client' => array(
                'credentials' => array(
                    'client_id' => 's6BhdRkqt3',
                    'client_secret' => '7Fjfp0ZBr1KtDRbnfVdmIw',
                ),
            ),
        ));
    }
} 