<?php
/**
 * 框架级Validator
 * @author wsfuyibing <websearch@163.com>
 * @date   2018-08-27
 */
namespace Uniondrug\Validation\Validators;

use Phalcon\Validation;
use Uniondrug\Validation\Validator;

/**
 * Boolean类型
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
