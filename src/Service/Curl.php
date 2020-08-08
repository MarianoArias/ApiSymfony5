<?php

namespace App\Service;

use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class Curl
{
    public function __construct()
    {
        $this->curl = null;
    }

    public function getCurl()
    {
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
        return $this->curl;
    }

    public function closeCurl()
    {
        curl_close($this->curl);
    }

    public function get(string $url, ?string $bearer)
    {
        $url = str_replace(' ', '%20', $url);
        $curl = $this->getCurl();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->getRequestHeaders($bearer));

        $oauthContent = curl_exec($curl);
        $oauthHttpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        return $this->codeAnalysis($oauthHttpCode, $oauthContent);
    }

    public function post(string $url, string $data_json, ?string $bearer)
    {
        $curl = $this->getCurl();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_json);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->getRequestHeaders($bearer));

        $oauthContent = curl_exec($curl);
        $oauthHttpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        return $this->codeAnalysis($oauthHttpCode, $oauthContent);
    }

    public function put(string $url, string $data_json, ?string $bearer)
    {
        $curl = $this->getCurl();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_json);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->getRequestHeaders($bearer));

        $oauthContent = curl_exec($curl);
        $oauthHttpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        return $this->codeAnalysis($oauthHttpCode, $oauthContent);
    }

    public function patch(string $url, string $data_json, ?string $bearer)
    {
        $curl = $this->getCurl();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_json);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->getRequestHeaders($bearer));

        $oauthContent = curl_exec($curl);
        $oauthHttpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        return $this->codeAnalysis($oauthHttpCode, $oauthContent);
    }

    public function delete(string $url, ?string $bearer)
    {
        $curl = $this->getCurl();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->getRequestHeaders($bearer));

        $oauthContent = curl_exec($curl);
        $oauthHttpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        return $this->codeAnalysis($oauthHttpCode, $oauthContent);
    }

    protected function getRequestHeaders(?string $bearer)
    {
        $arrayHttpHeaders = array();

        $arrayHttpHeaders[] = "Content-Type: application/json";
        $arrayHttpHeaders[] = 'Accept: application/json';

        if ($bearer) {
            $arrayHttpHeaders[] = 'Authorization: Bearer ' . $bearer;
        }

        return $arrayHttpHeaders;
    }

    protected function codeAnalysis(int $oauthHttpCode, string $oauthContent)
    {
        switch ($oauthHttpCode) {
            case 200:
                return json_decode($oauthContent);
            case 201:
                return json_decode($oauthContent);
            case 202:
                return json_decode($oauthContent);
            case 400:
                throw new BadRequestHttpException($this->getErrorMessage($oauthContent));
            case 404:
                throw new NotFoundHttpException($this->getErrorMessage($oauthContent));
            case 409:
                throw new ConflictHttpException($this->getErrorMessage($oauthContent));
            case 422:
                throw new UnprocessableEntityHttpException($this->getErrorMessage($oauthContent));
            case 500:
                throw new \Exception($this->getErrorMessage($oauthContent));
            case 503:
                throw new ServiceUnavailableHttpException();
            default:
                throw new ServiceUnavailableHttpException();
        }
    }

    private function getErrorMessage(?string $oauthContent)
    {
        return $this->isJson($oauthContent) && json_decode($oauthContent) && property_exists(json_decode($oauthContent), 'message') ? json_decode($oauthContent)->message : null;
    }

    private function isJson(?string $oauthContent)
    {
        json_decode($oauthContent);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}
