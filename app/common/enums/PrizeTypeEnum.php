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

namespace app\common\enums;

/**
 * 奖品类型枚举
 */
class PrizeTypeEnum
{
    // 实体奖品
    const ENTITY = 'ENTITY';
    // 虚拟奖品
    const VIRTUAL = 'VIRTUAL';

    /**
     * 获取枚举数据
     *
     * @return array[]
     */
    public static function data(): array
    {
        return [
            self::ENTITY  => ['msg' => '实体奖品'],
            self::VIRTUAL => ['msg' => '虚拟奖品'],
        ];
    }

    /**
     * 获取枚举描述
     *
     * @param string $code
     * @return string
     */
    public static function getMsgByCode(string $code): string
    {
        return self::data()[$code]['msg'] ?? '';
    }
}
