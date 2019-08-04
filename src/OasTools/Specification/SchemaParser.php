<?php

namespace DanBallance\OasTools\Specification;

use DanBallance\OasTools\Exceptions\NetworkError;
use DanBallance\OasTools\Exceptions\ConfigurationError;
use DanBallance\OasTools\Exceptions\FileNotFound;
use DanBallance\OasTools\Exceptions\JsonParseError;
use Symfony\Component\Yaml\Yaml;

trait SchemaParser
{
    protected $guzzle;

    /**
     * @param string $schemaLocation
     * @return array
     * @throws ConfigurationError
     * @throws FileNotFound
     * @throws JsonParseError
     * @throws NetworkError
     */
    protected function parse(string $schemaLocation): array
    {
        $contents = $this->getSpecContents($schemaLocation);
        switch (pathinfo($schemaLocation, PATHINFO_EXTENSION)) {
            case 'json':
                $array = @json_decode($contents, true);
                $msg = json_last_error_msg();
                if ($array === null && $msg !== 'No error') {
                    throw new JsonParseError("JSON parse error: '{$msg}'");
                }
                break;
            case 'yaml':
            case 'yml':
                $array = Yaml::parse($contents);
                break;
            default:
                throw new \Exception(
                    'Specification end with one of: json, yaml or yml.'
                );
        }
        return $array;
    }

    /**
     * @param string $schemaLocation
     * @return string
     * @throws ConfigurationError
     * @throws FileNotFound
     * @throws NetworkError
     */
    protected function getSpecContents(string $schemaLocation): string
    {
        if (strtolower(substr($schemaLocation, 0, 4)) == 'http') {
            if (!$this->guzzle) {
                $msg = 'Set a Guzzle client at $this->guzzle ' .
                    'to parse a network location.';
                throw new ConfigurationError($msg);
            }
            try {
                $response = $this->getGuzzle()->get($schemaLocation);
            } catch (\Exception $e) {
                $err = strtr(
                    "Network error calling ':location': :err",
                    [
                        ':location' => $schemaLocation,
                        ':err' => $e->getMessage()
                    ]
                );
                throw new NetworkError($err);
            }
            return (string)$response->getBody();
        } else {  // try as a file path
            if (!file_exists($schemaLocation)) {
                $msg = "Could not find a file at '{$schemaLocation}'";
                throw new FileNotFound($msg);
            }
            return file_get_contents($schemaLocation);
        }
    }

    /**
     * @return mixed
     */
    protected function getGuzzle()
    {
        return $this->guzzle;
    }
}
