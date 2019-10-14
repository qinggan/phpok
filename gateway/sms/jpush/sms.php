<?php
final class JSMS {

    const URL = 'https://api.sms.jpush.cn/v1/';

    private $appKey;
    private $masterSecret;
    private $options;

    public function __construct($appKey, $masterSecret, array $options = array()) {
        $this->appKey = $appKey;
        $this->masterSecret = $masterSecret;
        $this->options = array_merge([
            'ssl_verify'  => false,
            'disable_ssl' => true
        ], $options);
    }

    public function sendCode($mobile, $temp_id, $sign_id = null) {
        $url = self::URL . 'codes';
        $body = array('mobile' => $mobile, 'temp_id' => $temp_id);
        if (isset($sign_id)) {
            $body['sign_id'] = sign_id;
        }
        return $this->request('POST', $url, $body);
    }

    public function sendVoiceCode($mobile, $options = []) {
        $url = self::URL . 'voice_codes';
        $body = array('mobile' => $mobile);

        if (!empty($options)) {
            if (is_array($options)) {
                $body = array_merge($options, $body);
            } else {
                $body['ttl'] = $options;
            }
        }
        return $this->request('POST', $url, $body);
    }

    public function checkCode($msg_id, $code) {
        $url = self::URL . 'codes/' . $msg_id . "/valid";
        $body = array('code' => $code);
        return $this->request('POST', $url, $body);
    }

    public function sendMessage($mobile, $temp_id, array $temp_para = [], $time = null, $sign_id = null) {
        $path = 'messages';
        $body = array(
            'mobile'    => $mobile,
            'temp_id'   => $temp_id,
        );
        if (!empty($temp_para)) {
            $body['temp_para'] = $temp_para;
        }
        if (isset($time)) {
            $path = 'schedule';
            $body['send_time'] = $time;
        }
        if (isset($sign_id)) {
            $body['sign_id'] = $sign_id;
        }
        $url = self::URL . $path;
        return $this->request('POST', $url, $body);
    }

    public function sendBatchMessage($temp_id, array $recipients, $time = null, $sign_id = null, $tag = null) {
        $path = 'messages';
        foreach ($recipients as $mobile => $temp_para) {
            $r[] = array(
                'mobile'    => $mobile,
                'temp_para' => $temp_para
            );
        }
        $body = array(
            'temp_id'    => $temp_id,
            'recipients' => $r
        );
        if (isset($time)) {
            $path = 'schedule';
            $body['send_time'] = $time;
        }
        if (isset($sign_id)) {
            $body['sign_id'] = $sign_id;
        }
        if (isset($tag)) {
            $body['tag'] = $tag;
        }
        $url = self::URL . $path . '/batch';
        return $this->request('POST', $url, $body);
    }

    public function showSchedule($scheduleId) {
        $url = self::URL . 'schedule/' . $scheduleId;
        return $this->request('GET', $url);
    }

    public function deleteSchedule($scheduleId) {
        $url = self::URL . 'schedule/' . $scheduleId;
        return $this->request('DELETE', $url);
    }

    public function getAppBalance() {
        $url = self::URL . 'accounts/app';
        return $this->request('GET', $url);
    }

    public function request($method, $url, $body = [], $headers = [], $uploads = []) {
        $ch = curl_init();
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_HTTPHEADER => array_merge(array(
                'Connection: Keep-Alive'
            ), $headers),
            CURLOPT_USERAGENT => 'JSMS-API-PHP-CLIENT',
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_TIMEOUT => 120,

            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD => $this->appKey . ":" . $this->masterSecret,

            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => $method,
        );
        if (!$this->options['ssl_verify']
            || (bool) $this->options['disable_ssl']) {
            $options[CURLOPT_SSL_VERIFYPEER] = false;
            $options[CURLOPT_SSL_VERIFYHOST] = 0;
        }

        if (in_array('Content-Type: multipart/form-data', $options[CURLOPT_HTTPHEADER])) {
            $options[CURLOPT_POSTFIELDS] = array_merge($body, $uploads);
            if (class_exists('\CURLFile')) {
                $options[CURLOPT_SAFE_UPLOAD] = true;
            } else {
                if (defined('CURLOPT_SAFE_UPLOAD')) {
                    $options[CURLOPT_SAFE_UPLOAD] = false;
                }
            }
        } else {
            $options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
            if (!empty($body)) {
                $options[CURLOPT_POSTFIELDS] = json_encode($body);
            }
        }

        curl_setopt_array($ch, $options);
        $output = curl_exec($ch);

        if($output === false) {
            return "Error Code:" . curl_errno($ch) . ", Error Message:".curl_error($ch);
        } else {
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header_text = substr($output, 0, $header_size);
            $body = substr($output, $header_size);
            $headers = array();

            foreach (explode("\r\n", $header_text) as $i => $line) {
                if (!empty($line)) {
                    if ($i === 0) {
                        $headers[0] = $line;
                    } else if (strpos($line, ": ")) {
                        list ($key, $value) = explode(': ', $line);
                        $headers[$key] = $value;
                    }
                }
            }

            $response['headers'] = $headers;
            $response['body'] = json_decode($body, true);
            $response['http_code'] = $httpCode;
        }
        curl_close($ch);
        return $response;
    }
}
