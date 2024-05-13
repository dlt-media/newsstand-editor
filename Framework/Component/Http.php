<?php

namespace Framework\Component;

use Framework\Http\HeaderBag;
use JsonException;

/**
 * The Http class provides a simple interface for sending HTTP requests using cURL.
 *
 * This class provides methods to set custom headers and handle responses.
 *
 * @package Framework\Component
 */
class Http
{
    /**
     * The request headers.
     *
     * @var HeaderBag
     */
    private HeaderBag $headers;

    /**
     * The request response.
     *
     * @var bool|string
     */
    private $response;

    /**
     * Set headers for the request.
     *
     * @param HeaderBag $headers The custom headers for the request.
     * @return Http
     */
    public static function set_headers(HeaderBag $headers): Http
    {
        $instance = new self();
        $instance->headers = $headers;

        return $instance;
    }

    /**
     * Get the HeaderBag instance containing HTTP headers.
     *
     * @return HeaderBag The HeaderBag instance.
     */
    public function headers(): HeaderBag
    {
        return $this->headers;
    }

    /**
     * Get response.
     *
     * @return bool|string
     */
    public function response()
    {
        return $this->response;
    }

    /**
     * Batch process cURL options.
     *
     * @param resource $curl The cURL resource.
     * @param array $options The cURL options to set.
     * @return void
     */
    private function many_curl_setopt($curl, array $options): void
    {
        foreach ($options as $option) {
            curl_setopt($curl, $option[0], $option[1]);
        }
    }

    /**
     * Send a request to the API endpoint.
     *
     * @param string $method The HTTP method (POST, GET, etc.).
     * @param string $endpoint The API endpoint URL.
     * @param array $data [optional] The data to send with the request.
     * @return bool|string The response from the server.
     *
     * @throws JsonException
     */
    private function send_request(string $method, string $endpoint, array $data = [])
    {
        $curl = curl_init($endpoint);
        $headers = [];

        foreach ($this->headers()->all() as $key => $value) {
            $headers[] = $key . ': ' . $value;
        }

        $this->many_curl_setopt(
            $curl,
            [
                [CURLOPT_CUSTOMREQUEST, $method],
                [CURLOPT_RETURNTRANSFER, true],
                [CURLOPT_HTTPHEADER, $headers],
            ]
        );

        if (in_array($method, ['POST', 'PATCH'], true)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data, JSON_THROW_ON_ERROR));
        }

        $response = curl_exec($curl);

        if ($error = curl_error($curl)) {
            echo $error;
        }

        curl_close($curl);

        return $response;
    }

    /**
     * Send a PATCH request.
     *
     * @param string $endpoint The API endpoint URL.
     * @param array $data [optional] The data to send with the request.
     * @return Http
     */
    public function patch(string $endpoint, array $data = []): Http
    {
        $this->response = $this->send_request('PATCH', $endpoint, $data);

        return $this;
    }

    /**
     * Send a POST request.
     *
     * @param string $endpoint The API endpoint URL.
     * @param array $data [optional] The data to send with the request.
     * @return Http
     */
    public function post(string $endpoint, array $data = []): Http
    {
        $this->response = $this->send_request('POST', $endpoint, $data);

        return $this;
    }

    /**
     * Send a GET request.
     *
     * @param string $endpoint The API endpoint URL.
     * @param array $data [optional] The data to send with the request.
     * @return Http
     */
    public function get(string $endpoint, array $data = []): Http
    {
        $this->response = $this->send_request('GET', $endpoint, $data);

        return $this;
    }

    /**
     * Send a PUT request.
     *
     * @param string $endpoint The API endpoint URL.
     * @param array $data [optional] The data to send with the request.
     */
    public function put(string $endpoint, array $data = []): Http
    {
        $this->response = $this->send_request('PUT', $endpoint, $data);

        return $this;
    }

    /**
     * Send a UPDATE request.
     *
     * @param string $endpoint The API endpoint URL.
     * @param array $data [optional] The data to send with the request.
     * @return Http
     */
    public function update(string $endpoint, array $data = []): Http
    {
        $this->response = $this->send_request('UPDATE', $endpoint, $data);

        return $this;
    }

    /**
     * Send a DELETE request.
     *
     * @param string $endpoint The API endpoint URL.
     * @param array $data [optional] The data to send with the request.
     * @return Http
     */
    public function delete(string $endpoint, array $data = []): Http
    {
        $this->response = $this->send_request('DELETE', $endpoint, $data);

        return $this;
    }

    /**
     * Get the response as JSON.
     *
     * @return array|null The decoded JSON response, or null if decoding fails.
     */
    public function json(): ?array
    {
        try {
            return json_decode($this->response, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return null;
        }
    }
}
