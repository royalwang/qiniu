# Qiniu

七牛SDK，它存在的原因只是为了简化。


## Install

有两种安装方法：

1. composer 安装

    在项目根目录运行以下命令：

    ```shell
    composer require "overtrue/qiniu"
    ```
    
    或者在你的项目`composer.json`里`require`加入：
    
    ```json
        "overtrue/qiniu":"*"
    ```
    然后`composer update`即可。

2. 手动下载安装

    手动[下载zip](https://github.com/overtrue/qiniu/archive/master.zip) 然后引入两个文件即可：

    ```php
    require "src/Qiniu/HttpClient.php";
    require "src/Qiniu/Qiniu.php";
    ```

    可以参考：[demo.php](https://github.com/overtrue/qiniu/blob/master/demo.php)

## Usage

引入命名空间：

```php
use \Overtrue\Qiniu\Qiniu;
```


使用配置实例化：

```php
$options =  [
    'access_key' => '您的access_key',
    'secret_key' => '您的secret_key',
    'bucket'     => 'mystorage', // bucket名称
    'domain'     => 'mystorage.qiniudn.com', //bucket域名
    'timeout'    => 3600,
    'is_private' => false, //是否为私有
];

$qiniu = Qiniu::make($options);
```

从哪儿得到access_key与secret_key? https://portal.qiniu.com/setting/key


#### 上传文件

语法： `$qiniu->upload(本地文件路径, [目标文件名])`

目标文件名可以省略，默认与本地文件同名。

ex: 
```php
$result = $qiniu->upload(__DIR__ . '/hello.jpg', 'hello.jpg');
```

返回值：

> 注意：所有有返回值的均为数组，本页示例只是为了方便查看以json形式展示。

```json
{
   "hash":"Ft20LMBOUcc_UuZrlwJB68HI4TIy",
   "key":"hello.jpg"
}
```

[官方文档](http://developer.qiniu.com/docs/v6/api/reference/up/upload.html)

#### 移动文件(修改文件名)


语法： `$qiniu->move(已经存在的文件名, 目标文件名)`

ex: 
```php
$result = $qiniu->move('hello.jpg', 'world.jpg');
```

无返回值

[官方文档](http://developer.qiniu.com/docs/v6/api/reference/rs/move.html)

#### 删除文件


语法： `$qiniu->delete(已经存在的文件名, 目标文件名)`

ex: 
```php
$result = $qiniu->delete('hello.jpg');
```

无返回值

[官方文档](http://developer.qiniu.com/docs/v6/api/reference/rs/delete.html)

#### 复制文件


语法： `$qiniu->copy(已经存在的文件名, 目标文件名)`

ex: 
```php
$result = $qiniu->move('hello.jpg', 'world.jpg');
```

无返回值

[官方文档](http://developer.qiniu.com/docs/v6/api/reference/rs/copy.html)

#### 列举文件


语法： `$qiniu->lists(array 选项)`

| 选项名称 | 是否必要 | 说明 |
| ----- |----- | ----- |
| limit  |  NO  | 本次列举的条目数，范围为1-1000。缺省值为1000。|
| prefix  |  NO  | 指定前缀，只有资源名匹配该前缀的资源会被列出。缺省值为空字符串。|
| delimiter | NO |  指定目录分隔符，列出所有公共前缀（模拟列出目录效果）。缺省值为空字符串。|
| marker  | NO  | 上一次列举返回的位置标记，作为本次列举的起点信息。缺省值为空字符串。|

ex: 
```php
$result = $qiniu->lists(['prefix' => 'hello']); //列出以hello开头的文件
```

[官方文档](http://developer.qiniu.com/docs/v6/api/reference/rs/list.html)

返回值：

```json
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
```

#### 文件信息


语法： `$qiniu->lists(array 选项)`

ex:

```php
$result = $qiniu->info('hello22.jpg');
```

返回值：

```json
 {
    "fsize": 105661,
    "hash": "Ft20LMBOUcc_UuZrlwJB68HI4TIy",
    "mimeType": "image/jpeg",
    "putTime": 14215623390959519
}      
```

[官方文档](http://developer.qiniu.com/docs/v6/api/reference/rs/stat.html)

## License

MIT