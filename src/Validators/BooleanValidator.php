<?php
/**
 * 框架级Validator
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-01-05
 */
namespace Uniondrug\Validation\Validators;

use Phalcon\Validation;
use Phalcon\Validation\Message;
use Uniondrug\Validation\Structs\DatetimeParseStruct;
use Uniondrug\Validation\Validator;

/**
 * 验证带日期的完整时间格式
 * <code>
 * $validation = new Validation();                  // 创建Validation实例
 * $attribute = 'field';                            // 参数名称
 * $options = [                                     // 验证选项
 *     'required' => 'true',                        // 是否必须
 *     'empty' => 'false',                          // 是否允许为空
 *     'default' => '2018-01-15 12:30',             // 当不传字段时赋默认值
 *     'min' => '2018-01-01 08:00',                 // 最小时间
 *     'max' => '2018-12-31 21:30'                  // 最大时间
 * ];
 * $validator = new DatetimeValidator($options);
 * $validation->add($attribute, $validator);
 * $validation->validate();
 * </code>
 * @package Pails\Validators
 */
class BooleanValidator extends Validator
{
    /**
     * 执行验证
     * @param Validation $validation Validation对象
     * @param string     $attribute  待验证的字段/参数名
     * @return bool
     */
    public function validate(\Phalcon\Validation $validation, $attribute)
    {
        return true;
    }
}
