# Validation component for uniondrug/framework

验证工具，用于验证数据是否符合指定的规范。

## 安装

```shell
$ cd project-home
$ composer require uniondrug/validation
```

修改 `app.php` 配置文件，注入服务。服务名称：`validationService`。

```php
return [
    'default' => [
        ......
        'providers'           => [
            ......
            \Uniondrug\Validation\ValidationServiceProvider::class,
        ],
    ],
];
```

### 验证规则说明

验证规则包括：

`type`: 字符串或者数组，用到的验证器，可以是单个验证器的名称，也可以是一组验证器。
`required`: true/false，验证是否必填
`empty`: true/false，是否可以为空
`default`: mixed，默认值，如果传入的数据中这个字段为空，或者不存在，则使用该默认值
`options`: 数组，传给各个验证器的参数，具体根据各个验证器的不同而不同。如果是一组验证器，他们公用这个数组，从里面各取所需。
`filters`: 字符串或者数组，定义用到的过滤器。用来过滤输入数据。

> 验证规则可以以数组的方式定义，每一个待验证的字段对应一组规则：

```php
<?php
$rules = [
            'id' => [
                'type' => 'int',
                'required' => true,
            ],
            'mobile' => [
                'type' => 'mobile',
                'required' => true,
        ];
```

> 验证器还可以结合结构体使用，在结构体定义属性的注释中，通过注解定义。

```php
<?php
class ApiRequestStruct extends Struct
{
    /**
     * @var int
     *
     * @Validator(type={int,"\App\Validators\MyValidator"})
     */
    public $id;

    /**
     * @var string
     */
    public $name;
}
```


## 使用

可以通过 `Param::check($data, $rules)` 静态方式调用，可以通过已经注册的服务，实例化调用：`$validationService->checkInput($data, $rules)`。


### 输入：

参数 `$data` 是待验证的输入数据，可以使数组，或者一个对象。

    数组：通常是 `$_GET`/`$_POST`，或者是 `$this->request->get()`/`$this->request->getPost()`。
    或者是一个对象：`$this->request->getJsonRawbody()`。

参数 `$rules` 是验证规则，可以是上述数组方式定义的验证规则，或者已经定义了相关注解的结构体类名。

### 输出：

如果`$rules`是一个数组，则返回一个经过验证的数组，键名是规则中每一个待验证的字段，键值是从`$data`中提取的对应的值。
如果`$rules`是一个结构体类名，则返回这个结构体的实例，定义了验证规则的属性，将使用`$data`中对应的值进行赋值。

### 异常：

验证方法发现错误，会抛出异常，可以在控制器中捕获，也可以交给框架处理。


> 数组方式的使用：

```php
class IndexController extents Controller
{
    public function indexAction()
    {
        $input = $this->request->getJsonRawBody();
        $rules = [
            'id' => [
                'type' => 'int',
                'required' => true,
            ],
            'mobile' => [
                'type' => 'mobile',
                'required' => true,
        ];
        $data = $this->validationService->checkInput($input, $rules);


        ....
    }
}
```

> 结构体方式的使用：

```php
class IndexController extents Controller
{
    public function indexAction()
    {
        try {
            $apiStruct = ApiRequestStruct::factory($this->request->get());
            return $this->serviceServer->withObject($data->toArray())->response();
        } catch (\Exception $e) {
            return $this->serviceServer->withError($e->getMessage(), $e->getCode())->response();
        }

        ....
    }
}
```

### 验证器

    'alnum'     => 输入只能是字母和数组
    'alpha'     => 输入只能是字母
    'digit'     => 输入只能是数字
    'json'      => 输入必须是json格式的字符串
    'url'       => 输入必须是一个url
    'regex'     => 用正则表达式来验证，参数：patter 正则表达式
    'length'    => 指定长度的字符串，参数：max 最长，min 最短
    'between'   => 输入必须在指定的范围，参数：minimum 最小值，maximum 最大值
    'numeric'   => 输入必须是数字，包括小数、负数
    'inclusion' => 输入值必须是列表中的之一，参数：domain 可选输入列表，数组格式
    'exclusion' => 输入值必须不在列表之中，参数：domain 禁止输入列表，数组格式
    'datetime'  => 时间日期格式，按照 YYYY-MM-DD HH:ii:ss 验证，可选的参数：max 最迟，min 最早
    'date'      => 日期格式，按照 YYYY-MM-DD 验证，可选的参数：max 最迟，min 最早
    'float'     => 小数验证，可选的参数：max 最迟，min 最早
    'double'    => 小数验证，可选的参数：max 最迟，min 最早
    'email'     => 输入必须是合法的Email格式
    'int'       => 输入必须是整数，可选的参数：max 最迟，min 最早
    'integer'   => 同上
    'mobile'    => 输入必须是一个合法的大陆手机号码
    'string'    => 输入必须是个字符串
    'telphone'  => 输入必须是个合法的电话号码
    'time'      => 时间格式， HH:ii:ss 验证，可选的参数：max 最迟，min 最早
    'callback'  => 回调函数验证。参数 callback 可调用的方法
    'samewith'  => 同步验证，当前验证字段，必须跟同一组输入数据的另一个指定字段一致。参数：with 需要一致的字段名