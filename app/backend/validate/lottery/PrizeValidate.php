<?php
// +----------------------------------------------------------------------
// | WaitAdmin快速开发后台管理系统
// +----------------------------------------------------------------------
// | 欢迎阅读学习程序代码,建议反馈是我们前进的动力
// | 程序完全开源可支持商用,允许去除界面版权信息
// | gitee:   https://gitee.com/wafts/waitadmin-php
// | github:  https://github.com/topwait/waitadmin-php
// | 官方网站: https://www.waitadmin.cn
// | WaitAdmin团队版权所有并拥有最终解释权
// +----------------------------------------------------------------------
// | Author: WaitAdmin Team <2474369941@qq.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace app\backend\validate\lottery;

use app\common\basics\Validate;
use app\common\model\lottery\LdPrizes;

/**
 * 奖品验证器
 */
class PrizeValidate extends Validate
{
    protected $rule = [
        'id'    => 'require|integer|gt:0',
        'name'  => 'require|length:1,100',
        'img'   => 'length:0,500',
        'price' => 'float|egt:0',
        'stock' => 'integer|egt:0',
        'sort'  => 'integer|egt:0',
        'email' => 'length:0,100',
        'type'  => 'require|integer|in:' . LdPrizes::TYPE_VIRTUAL . ',' . LdPrizes::TYPE_ENTITY,
    ];

    protected $message = [
        'id.require'     => '参数缺失',
        'id.integer'     => 'ID必须是整数',
        'id.gt'          => 'ID必须大于0',
        'name.require'   => '奖品名称不能为空',
        'name.length'    => '奖品名称长度须在1-100位字符',
        'img.length'     => '奖品图片长度不能超过500字符',
        'price.float'    => '奖品价值必须是数字',
        'price.egt'      => '奖品价值不能小于0',
        'stock.integer'  => '库存数量必须是整数',
        'stock.egt'      => '库存数量不能小于0',
        'sort.integer'   => '排序必须是整数',
        'sort.egt'       => '排序不能小于0',
        'email.length'   => '联系邮箱长度不能超过100字符',
        'type.require'   => '奖品类型不能为空',
        'type.integer'   => '奖品类型必须是整数',
        'type.in'        => '奖品类型值不正确',
    ];

    protected $scene = [
        'detail' => ['id'],
        'add'    => ['name', 'img', 'price', 'stock', 'sort', 'email', 'type'],
        'edit'   => ['id', 'name', 'img', 'price', 'stock', 'sort', 'email', 'type'],
        'del'    => ['id'],
        'status' => ['id'],
    ];
}