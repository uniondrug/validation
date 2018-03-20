<?php
/**
 * 框架级Helper
 *
 * @author wsfuyibing <websearch@163.com>
 * @date   2017-12-26
 */

namespace Uniondrug\Validation;

use Phalcon\Di;
use Phalcon\Filter;
use Phalcon\Validation\Validator\Alnum;
use Phalcon\Validation\Validator\Alpha;
use Phalcon\Validation\Validator\Between;
use Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\Confirmation;
use Phalcon\Validation\Validator\Digit;
use Phalcon\Validation\Validator\ExclusionIn;
use Phalcon\Validation\Validator\File;
use Phalcon\Validation\Validator\InclusionIn;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\Regex;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\Url;
use Phalcon\Validation\ValidatorInterface;
use Uniondrug\Structs\StructInterface;
use Uniondrug\Validation\Exceptions\ParamException;
use Uniondrug\Validation\Validators\DatetimeValidator;
use Uniondrug\Validation\Validators\DateValidator;
use Uniondrug\Validation\Validators\DoubleValidator;
use Uniondrug\Validation\Validators\EmailValidator;
use Uniondrug\Validation\Validators\IntegerValidator;
use Uniondrug\Validation\Validators\JsonValidator;
use Uniondrug\Validation\Validators\MobileValidator;
use Uniondrug\Validation\Validators\StringValidator;
use Uniondrug\Validation\Validators\TelphoneValidator;
use Uniondrug\Validation\Validators\TimeValidator;

/**
 * 参数检查, 运行以下类型
 */
class Param
{
    /**
     * @var array 验证类型与类关系
     */
    protected static $validatorConfig = [
        'alnum'     => Alnum::class,
        'alpha'     => Alpha::class,
        'digit'     => Digit::class,
        'json'      => JsonValidator::class,
        'url'       => Url::class,
        'regex'     => Regex::class,
        'length'    => StringLength::class,
        'between'   => Between::class,
        'file'      => File::class,
        'numeric'   => Numericality::class,
        'inclusion' => InclusionIn::class,
        'exclusion' => ExclusionIn::class,
        'datetime'  => DatetimeValidator::class,
        'date'      => DateValidator::class,
        'float'     => DoubleValidator::class,
        'double'    => DoubleValidator::class,
        'email'     => EmailValidator::class,
        'int'       => IntegerValidator::class,
        'integer'   => IntegerValidator::class,
        'mobile'    => MobileValidator::class,
        'string'    => StringValidator::class,
        'telphone'  => TelphoneValidator::class,
        'time'      => TimeValidator::class,
        'callback'  => Callback::class,
        'samewith'  => Confirmation::class,
    ];

    /**
     * 实例化调用方法
     *
     * @param array|object $data
     * @param array|string $rules
     *
     * @return array|StructInterface
     * @throws \Uniondrug\Validation\Exceptions\ParamException
     */
    public function checkInput($data, $rules)
    {
        return static::check($data, $rules);
    }

    /**
     * 静态调用方法
     *
     * @param array|object $data
     * @param array|string $rules
     *
     * @return array|StructInterface
     * @throws \Uniondrug\Validation\Exceptions\ParamException
     */
    public static function check($data, $rules)
    {
        $data = static::validate($data, $rules);

        return $data;
    }

    /**
     * 参数检查
     * <code>
     * // 以下示例应用于Controller
     * $json = $this->getJsonRawBody();
     * $rules = [
     *     'id' => [
     *         'type' => 'int',
     *         'required' => true,
     *         'min' => 1,
     *     ],
     *     'status' => [
     *         'type' => 'string',
     *         'required' => true,
     *         'options' => ['success', 'expired']
     *     ]
     * ];
     * $data = Param::check($json, $rules);
     * </code>
     *
     * @param array|object $input 数组或者JSON格式的RAW对象
     * @param array        $rules JSON
     *
     * @return array|\Uniondrug\Structs\StructInterface
     * @throws \Uniondrug\Validation\Exceptions\ParamException
     */
    protected static function validate($input, $rules)
    {
        $validation = new Validation();
        $filter = Di::getDefault()->has('filter') ? Di::getDefault()->get('filter') : new Filter();

        // 0. 输出结果
        $data = [];

        // 1. 遍历规则
        foreach ($rules as $key => $rule) {
            // 1.0 从输入按需获取数据
            if (is_array($input) && isset($input[$key])) {
                $data[$key] = $input[$key];
            } else if (is_object($input) && property_exists($input, $key)) {
                $data[$key] = $input->$key;
            }

            // 1.1 不限制
            if ($rule === null) {
                continue;
            }

            // 1.2 检查规则定义是否正确
            if (!is_array($rule) || !isset($rule['type'])) {
                throw new ParamException("字段 '{$key}' 的规则定义不合法", 20000);
            }

            // 1.3 附加默认值
            if (!isset($data[$key]) && isset($rule['default'])) {
                $data[$key] = $rule['default'];
            }

            // 1.4 处理必填
            if (isset($rule['required']) && $rule['required'] && !isset($data[$key])) {
                throw new ParamException("字段 '{$key}' 必填", 10000);
            }

            // 1.5 空值检查
            if (empty($data[$key]) && (!isset($rule['empty']) || '' === $rule['empty'])) {
                throw new ParamException("字段 '{$key}' 不能为空", 10000);
            }

            // 1.6 过滤入参（对一些不合法字符调用过滤器处理）
            if (isset($data[$key]) && isset($rule['filters'])) {
                $data[$key] = $filter->sanitize($data[$key], $rule['filters']);
            }

            // 1.7 加入验证
            $options = isset($rule['options']) ? $rule['options'] : $rule;
            if (!is_array($options)) {
                $options = [$options];
            }
            $options['cancelOnFail'] = true; // 遇到一个验证不通过，直接跳出验证
            if (!empty($data[$key])) { // 空值不需要验证
                $types = $rule['type'];
                if (!is_array($rule['type'])) {
                    $types = [$rule['type']];
                }
                foreach ($types as $validatorName) {
                    $typeName = strtolower($validatorName);
                    if (isset(static::$validatorConfig[$typeName])) {
                        $validatorClass = static::$validatorConfig[$typeName];
                    } else if (is_a($validatorName, ValidatorInterface::class, true)) {
                        $validatorClass = $validatorName;
                    } else {
                        throw new ParamException("规则 '{$validatorName}' 未定义", 20000);
                    }
                    $validation->add($key, new $validatorClass($options));
                }
            }
        }

        // 2. 批量验证
        $validation->validate($data);

        // 3. 验证过程有错误
        if ($validation->hasFailure()) {
            throw new ParamException($validation->getFailureMessage(), 10000);
        }

        // 4. 返回结果(数组格式，可以用于直接初始化结构体)
        return $data;
    }
}
