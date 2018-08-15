<?php
namespace Heinlein;


use Firebase\JWT\JWT;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class JwtMiddleware implements MiddlewareInterface {

    /**
     * @var ContainerInterface
     */
    protected $_dc;

    public function __construct(ContainerInterface $container) {
        $this->_dc = $container;
    }

    public function __invoke(Request $request, Response $response, callable $next): Response {
        if (strtoupper($request->getMethod()) == "OPTIONS") {
            // Disable check on OPTIONS requests
            return $next($request, $response);
        }

        $authHeader = $request->getHeaderLine("Authorization");
        if (empty($authHeader)) {
            $authHeader = $request->getHeaderLine("KEYCLOAK_ACCESS_TOKEN");
            if (empty($authHeader)) {
                $authHeader = $request->getHeaderLine("X_KEYCLOAK_TOKEN");
            }

            if (!empty($authHeader)) {
                $authHeader = "Bearer " . $authHeader;
            }
        }

        if (empty($authHeader)) {
            return $this->unauthorized($response, "Missing Authorization header");
        }

        if (preg_match("/^Bearer\s(.*)$/i", $authHeader, $matches) !== 1) {
            return $this->unauthorized($response, "Invalid Authorization header");
        }

        $jwt = $matches[1];

        try {
            $keycloak = new Keycloak($this->_dc->get("settings")["keycloak"]);
            $decoded = JWT::decode($jwt, $keycloak->getPublicKeys(), ["RS256"]);

            $this->_dc["authorization"] = $decoded;
            $response = $response->withHeader("X-Used-Token", $jwt);
        } catch (\Exception $ex) {
            return $this->unauthorized($response, $ex->getMessage());
        }

        return $next($request, $response);
    }

    protected function unauthorized(Response $response, string $msg) : Response {
        return $response->withJson(["msg" => $msg], 400, JSON_PRETTY_PRINT);
    }
}