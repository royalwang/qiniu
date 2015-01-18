<?php namespace Overtrue\Qiniu;

use Exception;

class Qiniu
{
    protected $options = array(
        'access_key' => null,
        'secret_key' => null,
        'bucket'     => null,
        'domain'     => null,
        'timeout'    => 3600,
        'is_private' => false,
        'rs_url'     => 'http://rs.qbox.me',
        'rsf_url'    => 'http://rsf.qbox.me',
        'upload_url' => 'http://upload.qiniu.com',
    );


    public function __construct(array $options)
    {
        $this->options = array_merge($this->options, $options);

        if (!$this->access_key || !$this->secret_key || !$this->bucket)
        {
            throw new Exception('缺少参数');
        }
        if (!$this->domain) {
            $this->domain = 'http://'.$this->bucket.'.qiniudn.com/';
        }
    }

    /**
     * 生成实例
     *
     * @param array $options
     *
     * @return Overtrue\Qiniu\Qiniu
     */
    public static function make(array $options)
    {
        return new static($options);
    }

    /**
     * 查看文件信息 (putTime 字段被转成科学计数法 自行用number_format解决)
     *
     * @param string $key
     *
     * @return array
     */
    public function info($key)
    {
        $url   = $this->rsUrl('stat', "{$this->bucket}:{$key}");
        $token = $this->accessToken($url);

        return $this->postRequest($url,[], ["Authorization: QBox {$token}"]);
    }

    /**
     * 复制文件
     *
     * @param string $key
     * @param string $target
     *
     * @return array
     */
    public function copy($key, $target)
    {
        $url   = $this->rsUrl('copy', "{$this->bucket}:{$key}", "{$this->bucket}:{$target}");
        $token = $this->accessToken($url);

        return $this->postRequest($url, [], ["Authorization: QBox {$token}"]);
    }

    /**
     * 移动文件(重命名)
     *
     * @param string $key
     * @param string $target
     *
     * @return array
     */
    public function move($key, $target)
    {
        $url   = $this->rsUrl('move', "{$this->bucket}:{$key}", "{$this->bucket}:{$target}");
        $token = $this->accessToken($url);

        return $this->postRequest($url, [], ["Authorization: QBox {$token}"]);
    }

    /**
     * 删除文件
     *
     * @param string $key
     *
     * @return array
     */
    public function delete($key)
    {
        $url = $this->rsUrl('delete', "{$this->bucket}:{$key}");
        $token = $this->accessToken($url);

        return $this->postRequest($url, [], ["Authorization: QBox {$token}"]);
    }

    /**
     * 列举资源 (see:http://developer.qiniu.com/docs/v6/api/reference/rs/list.html)
     *
     * @param array $query
     *
     * @return array
     */
    public function lists(array $query = array())
    {
        $query = array_merge(array(
            'bucket'    => $this->bucket,
            'limit'     => 1000,
            'perfix'    => '',
            'delimiter' => '',
            'marker'    => ''
        ) ,$query);

        $url   = $this->rsf_url . '/list?' . http_build_query($query);
        $token = $this->accessToken($url);

        return $this->postRequest($url, [], ["Authorization: QBox {$token}"]);
    }

    /**
     * 文件上传
     *
     * @param  string $path 文件路径
     * @param  string $key  云端文件名, 可选
     *
     * @return array
     */
    public function upload($path, $key = '')
    {
        !empty($key) || $key = basename($path);

        $params = [
            'token' => $this->uploadToken(),
            'key'   => $key,
        ];

        return $this->postRequest($this->upload_url, $params, [], ['file' => $path]);
    }

    /**
     * 文件下载
     *
     * @param  string  $key
     * @param  boolean $private
     *
     * @return array
     */
    public function download($key, $private = false)
    {
        $url = trim($this->domain, '/') . '/' . str_replace("%2F", "/", rawurlencode($key));

        if ($private) {
            $param = array('e' => time() + $this->timeout);
            $url   = $url . '?' .http_build_query($param);
            $token = $this->sign($url);
            $url   = "{$url}&token={$token}";
        }

        return $url;
    }

    /**
     * 生成rs_url
     *
     * @return string
     */
    protected function rsUrl()
    {
        $args   = func_get_args();
        $action = array_shift($args);
        $path   = join('/', array_map(array($this, 'urlSafeEncode'), $args));

        return "{$this->rs_url}/{$action}/{$path}";
    }

    /**
     * 生成访问令牌
     *
     * @param string $url
     * @param string $body
     *
     * @return string
     */
    public function accessToken($url, $body = '')
    {
        $url  = parse_url($url);
        $data = '';

        if (isset($url['path'])) {
            $data = $url['path'];
        }

        if (isset($url['query'])) {
            $data .= '?' . $url['query'];
        }

        $data .= "\n";

        if ($body) {
            $data .= $body;
        }

        return $this->sign($data);
    }

    # 上传令牌
    public function uploadToken($policy = array())
    {
        // 上传策略 http://developer.qiniu.com/docs/v6/api/reference/security/put-policy.html
        $policy = array_merge(array(
            'scope'    => $this->bucket,
            'deadline' => time() + $this->timeout
        ), $policy);

        $data = $this->urlSafeEncode(json_encode($policy));

        return $this->sign($data) . ':' . $data;
    }

    /**
     * 获取签名
     *
     * @param array $data
     *
     * @return string
     */
    public function sign($data)
    {
        $sign = hash_hmac('sha1', $data, $this->secret_key, true);

        return $this->access_key . ':' . $this->urlSafeEncode($sign);
    }

    /**
     * get 请求
     *
     * @param string $url
     * @param string $token
     *
     * @return array
     */
    protected function getRequest($url, $token)
    {
        return json_decode(HttpClient::get($url, [], array(
                'Authorization' => "QBox {$token}"
            )), true);
    }

    /**
     * post请求
     *
     * @param string $url
     * @param string $token
     * @param array  $params
     * @param array  $haders
     * @param string $file
     *
     * @return array
     */
    protected function postRequest($url, $params = [], $headers = [], $file = [])
    {
        return json_decode(HttpClient::post($url, $params, $headers, $file), true);
    }


    /**
     * URL安全的Base64编码
     *
     * @param string $str
     *
     * @return string
     */
    public function urlSafeEncode($str)
    {
        $find    = array('+', '/');
        $replace = array('-', '_');

        return str_replace($find, $replace, base64_encode($str));
    }

    public function __get($key)
    {
        if (array_key_exists($key, $this->options)) {
            return $this->options[$key];
        }
    }
}