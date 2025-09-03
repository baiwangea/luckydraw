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

namespace app\api\service\lottery;

use app\common\basics\Service;
use app\backend\service\lottery\PrizesService as BackendPrizesService;
use app\common\exception\OperateException;
use app\common\model\lottery\DrawRecords;
use app\common\model\lottery\LotteryCodes;
use app\common\model\lottery\Prizes;
use app\common\utils\UrlUtils;
use think\facade\Db;

/**
 * 抽奖服务类
 */
class LotteryService extends Service
{
    /**
     * 获取奖品列表
     *
     * @return array
     * @throws \think\db\exception\DbException
     */
    public static function prizes(): array
    {
        $lists = BackendPrizesService::lists(['limit' => 1000]);
        return $lists['list'] ?? [];
    }

    /**
     * 执行抽奖
     *
     * @param array $post
     * @return array
     * @throws OperateException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function draw(array $post): array
    {
        $code = trim($post['code'] ?? '');

        if (empty($code)) {
            throw new OperateException('抽奖码不能为空');
        }

        // 1. 验证抽奖码
        $lotteryCode = LotteryCodes::where('code', $code)->find();
        if (!$lotteryCode) {
            throw new OperateException('抽奖码无效');
        }

        if ($lotteryCode->is_used) {
            throw new OperateException('该抽奖码已被使用');
        }

        // 2. 获取奖品信息
        $prize = Prizes::find($lotteryCode->prize_id);
        if (!$prize || $prize->is_delete) {
            $prize = ['id' => 0, 'name' => '谢谢参与', 'img' => ''];
        }

        Db::startTrans();
        try {
            // 3. 扣减库存
            if ($prize['id'] > 0 && $prize->stock >= 0) {
                if ($prize->stock < 1) {
                    throw new OperateException('抱歉，该奖品已派发完毕');
                }
                $prize->dec('stock');
            }

            // 4. 标记抽奖码为已使用
            $lotteryCode->is_used = 1;
            $lotteryCode->used_time = time();
            $lotteryCode->save();

            // 5. 创建抽奖记录
            DrawRecords::create([
                'code_id'    => $lotteryCode->id,
                'prize_id'   => $prize['id'] > 0 ? $prize['id'] : null,
                'status'     => $prize['id'] > 0 ? 1 : 0,
                'draw_time'  => time(),
            ]);

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw new OperateException('抽奖失败，请稍后重试: ' . $e->getMessage());
        }

        return [
            'id'   => $prize['id'],
            'name' => $prize['name'],
            'img'  => $prize['img'] ? UrlUtils::toAbsoluteUrl($prize['img']) : '',
        ];
    }

    /**
     * 根据抽奖码获取抽奖记录
     *
     * @param string $code
     * @return array
     * @throws OperateException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getRecordByCode(string $code): array
    {
        // 1. 查找抽奖码
        $lotteryCode = LotteryCodes::where('code', $code)->find();
        if (!$lotteryCode) {
            throw new OperateException('抽奖码无效');
        }

        if (!$lotteryCode->is_used) {
            throw new OperateException('该抽奖码尚未使用');
        }

        // 2. 查找抽奖记录
        $drawRecord = DrawRecords::where('code_id', $lotteryCode->id)->find();
        if (!$drawRecord) {
            // 正常情况下，已使用的code一定有记录，这里是防御性编程
            throw new OperateException('未找到对应的抽奖记录');
        }

        // 3. 获取奖品信息
        $prizeName = 'Thank You';
        if ($drawRecord->prize_id) {
            $prize = Prizes::find($drawRecord->prize_id);
            if ($prize) {
                $prizeName = $prize->name;
            }
        }

        return [
            'prize_name'  => $prizeName,
            'code_id'      => $drawRecord->code_id,
            'is_win'      => $drawRecord->status,
            'draw_time' => date('Y-m-d H:i:s', $drawRecord->draw_time),
        ];
    }
}
