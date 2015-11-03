<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2015 Michał Kopacz.
 * @author Michał Kopacz <michalkopacz.mk@gmail.com>
 */

namespace MostSignificantBit\OAuth2\Client\Grant\RefreshToken;

use MostSignificantBit\OAuth2\Client\AccessToken\RequestInterface as AccessTokenRequestInterface;
use MostSignificantBit\OAuth2\Client\Parameter\GrantType;
use MostSignificantBit\OAuth2\Client\Parameter\RefreshToken;
use MostSignificantBit\OAuth2\Client\Parameter\Scope;

class AccessTokenRequest implements AccessTokenRequestInterface
{
    /**
     * The refresh token issued to the client.
     *
     * OAuth2: REQUIRED
     *
     * @var RefreshToken
     */
    protected $refreshToken;

    /**
     * OAuth2: OPTIONAL
     *
     * @var Scope
     */
    protected $scope;

    /**
     * @param RefreshToken $refreshToken
     */
    function __construct(RefreshToken $refreshToken)
    {
        $this->setRefreshToken($refreshToken);
    }

    /**
     * @return GrantType
     */
    public function getGrantType()
    {
        return GrantType::REFRESH_TOKEN();
    }

    /**
     * @param RefreshToken $refreshToken
     */
    public function setRefreshToken(RefreshToken $refreshToken)
    {
        $this->refreshToken = $refreshToken;
    }

    /**
     * @return RefreshToken
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * @param Scope $scope
     */
    public function setScope(Scope $scope)
    {
        $this->scope = $scope;
    }

    /**
     * @return Scope
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @return array
     */
    public function getBodyParameters()
    {
        $parameters = array(
            'grant_type' => $this->getGrantType()->getValue(),
            'refresh_token' => $this->getRefreshToken()->getValue(),
        );

        if (isset($this->scope)) {
            $parameters['scope'] = $this->getScope()->getScopeParameter();
        }

        return $parameters;
    }
}
