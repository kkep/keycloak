<?php

class HttpClient
{
    protected $ch;

    protected $response;

    public function __construct()
    {
        $this->ch = curl_init();

        curl_setopt_array($this->ch, [
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_NOBODY => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_ENCODING => '',
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_MAXREDIRS => 3,
        ]);
    }

    public function get($url, $params = [])
    {
        return $this->request($url, 'GET', $params);
    }

    public function post($url, $data = [])
    {
        return $this->request($url, 'POST', $data);
    }

    protected function request($url, $method, $data = [])
    {
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($this->ch, CURLOPT_URL, $url);

        if (!empty($data)) {
            if ($method == 'POST') {
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
            } else {
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($data));
            }
        }

        $this->response = curl_exec($this->ch);

        return $this;
    }

    public function getBody()
    {
        return substr($this->response, $this->getHeaderSize());
    }

    public function getHeader()
    {
        return substr($this->response, 0, $this->getHeaderSize());
    }

    public function setHeaders($headers = [])
    {
        $values = [];

        foreach ($headers as $key => $value) {
            $values[] = $key . ': ' . $value;
        }

        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $values);

        return $this;
    }

    public function getData($associative = null)
    {
        return json_decode($this->getBody(), $associative);
    }

    public function getHeaders()
    {
        $headers = [];

        $headerText = $this->getHeader();

        foreach (explode("\r\n", $headerText) as $i => $line) {
            if ($i === 0) {
                $headers['http_code'] = $line;
            } else {
                list ($key, $value) = explode(': ', $line);

                $headers[$key] = $value;
            }
        }

        return $headers;
    }

    public function getHeaderSize()
    {
        return curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
    }

    public function getCode()
    {
        return curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
    }

    public function getError()
    {
        return curl_error($this->ch);
    }

    public function __destruct()
    {
        curl_close($this->ch);
    }
}