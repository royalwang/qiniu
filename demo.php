<?php

include __DIR__ . '/src/Qiniu/HttpClient.php';
include __DIR__ . '/src/Qiniu/Qiniu.php';

use \Overtrue\Qiniu\Qiniu;

$options =  [
    'access_key' => 'R7XWZeqqwNA76AdKrffHRfWTn6S9S-jYN9N3-Kex',
    'secret_key' => 'VMQkEEuErwnav-NJnFRYHagOczdCH3gQ6dWbTrMp',
    'bucket'     => 'mystorage',
    'domain'     => 'mystorage.qiniudn.com',
    'timeout'    => 3600,
    'is_private' => false,
    'rs_url'     => 'http://rs.qbox.me',
    'rsf_url'    => 'http://rsf.qbox.me',
    'upload_url' => 'http://upload.qiniu.com',
];

$qiniu = Qiniu::make($options);

// 上传文件
// $result = $qiniu->upload(__DIR__ . '/1304280_091858005934_2.jpg');
/*
{
   "hash":"Ft20LMBOUcc_UuZrlwJB68HI4TIy",
   "key":"1304280_091858005934_2.jpg"
}
*/

// 移动文件
//$result = $qiniu->move('hello.jpg', 'hell111o.jpg');
// 成功返回null
//
// 删除文件
//$result = $qiniu->delete('hello.jpg');
// 成功返回null

// 复制内容
// $result = $qiniu->copy('hello.jpg', 'hello22.jpg');
// 成功返回null
//
// 列举文件
//$result = $qiniu->lists(['prefix' => 'hello']);
/*
{
  "items": [
    {
      "key": "hello.jpg",
      "hash": "Ft20LMBOUcc_UuZrlwJB68HI4TIy",
      "fsize": 105661,
      "mimeType": "image/jpeg",
      "putTime": 14215623214044197
    },
    {
      "key": "hello22.jpg",
      "hash": "Ft20LMBOUcc_UuZrlwJB68HI4TIy",
      "fsize": 105661,
      "mimeType": "image/jpeg",
      "putTime": 14215623390959519
    }
  ]
}
*/

// 获取资源信息
//$result = $qiniu->info('hello22.jpg');
/*
    {
      "fsize": 105661,
      "hash": "Ft20LMBOUcc_UuZrlwJB68HI4TIy",
      "mimeType": "image/jpeg",
      "putTime": 14215623390959519
    }
 */
var_dump(json_encode($result));
