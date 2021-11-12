<?php

namespace Zbxin\GeeTest;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;
use Zbxin\GeeTest\Exceptions\CaptchaTimeoutException;
use Zbxin\GeeTest\Library\GeeTestLib;

class GeeTest
{
    /**
     * @var \Illuminate\Config\Repository
     */

    protected $config;

    /**
     * @var GeeTestLib
     */

    protected $gtLib;

    /**
     * GeeTest constructor.
     * @param $config
     */

    public function __construct($config)
    {
        $this->config = $config;
        $this->gtLib = new GeeTestLib($this->config->get('gee_test.id'), $this->config->get('gee_test.key'));
    }

    /**
     * 生成验证码
     *
     * @param string $type
     * @param null $userId
     * @return array|bool
     */

    public function generateCaptcha($type = 'web', $userId = null)
    {
        $params = ['client_type' => $type, 'ip_address' => Request::ip()];
        if ($userId) $params['user_id'] = sha1($userId);
        $result = $this->gtLib->pre_process($params);
        $response = $this->gtLib->get_response();
        $resultKey = str_random(40);
        try {
            if (!Cache::add($resultKey, json_encode(array_merge($params, ['status' => $result])), Carbon::now()->addMinutes(5))) {
                return false;
            }
            return array_merge($response, ['status' => $resultKey]);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 校验验证码
     *
     * @param $statusKey
     * @param $challenge
     * @param $validate
     * @param $secCode
     * @return int
     */

    public function verifyCaptcha($statusKey, $challenge, $validate, $secCode)
    {
        try {
            if (!$captchaInfo = Cache::get($statusKey)) {
                throw new CaptchaTimeoutException();
            }
            $captchaInfo = json_decode($captchaInfo, true);
            if ($captchaInfo['status'] === 1) {
                unset($captchaInfo['status']);
                return $this->gtLib->success_validate($challenge, $validate, $secCode, $captchaInfo) === 1;
            }
            return $this->gtLib->fail_validate($challenge, $validate, $secCode) === 1;
        } catch (\Exception $e) {
            throw new CaptchaTimeoutException();
        }
    }
}
