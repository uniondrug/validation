# Cache component for uniondrug/framework

## 安装

```shell
$ cd project-home
$ composer require uniondrug/validation
```

修改 `app.php` 配置文件

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

## 使用

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
