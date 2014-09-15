<?php

require_once('Tiqr/API/Entity/APIResult.php');

class Tiqr_API_Client
{
    /**
     * The API Base URL
     *
     * @var string
     */
    protected $_apiBaseURL;

    /**
     * The API consumer key
     *
     * @var string
     */
    protected $_consumerKey;

    /**
     * Set API baseURL
     *
     * @param string $apiBaseURL The API end point
     */
    public function setBaseURL($apiBaseURL)
    {
        $this->_apiBaseURL = rtrim($apiBaseURL, '/') . '/';
    }

    /**
     * Set Consumer key
     *
     * @param string $consumerKey The consumer key
     */
    public function setConsumerKey($consumerKey)
    {
        $this->_consumerKey = $consumerKey;
    }

    /**
     * Calls the API
     *
     * @param string $resource	The resource URL
     * @param string $method	The HTTP Method (GET, POST, PUT, DELETE)
     * @param array  $data		Data send with request as key => value pairs
     *
     * @return Object
     *
     * @throws Exception
     */
    public function call($resource, $method = "GET", $data = array())
    {
        $headers['X-OATHService-ConsumerKey'] = $this->_consumerKey;

        $result = $this->callAPI($resource, $method, $data, $headers);

        if (2 == substr($result->code, 0, 1)) {
            return $result;
        } elseif (is_object($result->body)) {
            throw new Exception($result->body->message, $result->code);
        } else {
            throw new Exception('', $result->code);
        }
    }

    /**
     * Calls the API endpoint
     *
     * @param string $resource	The resource URL
     * @param string $method	The HTTP Method (GET, POST, PUT, DELETE)
     * @param array  $data		Data send with request as key => value pairs
     * @param array  $headers   HTTP Headers send with request as key => value pairs
     *
     * @return Tiqr_API_Entity_APIResult
     */
    protected function callAPI($resource, $method = "GET", $data = array(), $headers = array())
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_apiBaseURL . ltrim($resource, '/'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // Explicitly empty null values, because http_build_query will throw away
        // all null values, which would not let us make fields empty.
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if ($value == null) {
                    $data[$key] = '';
                }
            }
        }

        switch ($method) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                break;
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;
        }

        $headerArray = array();
        foreach ($headers as $key => $value) {
            $headerArray[] = $key . ': ' . $value;
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);

        $apiResult = curl_exec($ch);
        $resultCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = new Tiqr_API_Entity_APIResult();
        $result->code = $resultCode;
        $result->body = json_decode($apiResult);
        $result->rawBody = $apiResult;

        return $result;
    }
}