<?php
// +----------------------------------------------------------------------
// | WaitAdmin Rapid Development Background Management System
// +----------------------------------------------------------------------
// | Welcome to read and learn the program code. Your feedback is our driving force.
// | The program is completely open source and supports commercial use, allowing the removal of interface copyright information.
// | gitee:   https://gitee.com/wafts/waitadmin-php
// | github:  https://github.com/topwait/waitadmin-php
// | Official Website: https://www.waitadmin.cn
// | Copyright by WaitAdmin team, all rights reserved.
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
 * Lottery Service Class
 */
class LotteryService extends Service
{
    /**
     * Get Prize List
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
     * Execute Draw
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
            throw new OperateException('Lottery code cannot be empty');
        }

        // 1. Verify lottery code
        $lotteryCode = LotteryCodes::where('code', $code)->find();
        if (!$lotteryCode) {
            throw new OperateException('Invalid lottery code');
        }

        if ($lotteryCode->is_used) {
            throw new OperateException('This lottery code has been used');
        }

        // 2. Get prize information
        $prize = Prizes::find($lotteryCode->prize_id);
        if (!$prize || $prize->is_delete) {
            $prize = ['id' => 0, 'name' => 'Thank you for participating', 'img' => ''];
        }

        Db::startTrans();
        try {
            // 3. Deduct stock
            if ($prize['id'] > 0 && $prize->stock >= 0) {
                if ($prize->stock < 1) {
                    throw new OperateException('Sorry, this prize has been fully distributed');
                }
                $prize->dec('stock');
            }

            // 4. Mark the lottery code as used
            $lotteryCode->is_used = 1;
            $lotteryCode->used_time = time();
            $lotteryCode->save();

            // 5. Create a lottery record
            DrawRecords::create([
                'code_id'    => $lotteryCode->id,
                'prize_id'   => $prize['id'] > 0 ? $prize['id'] : null,
                'status'     => $prize['id'] > 0 ? 1 : 0,
                'draw_time'  => time(),
            ]);

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw new OperateException('Lottery failed, please try again later: ' . $e->getMessage());
        }

        return [
            'id'   => $prize['id'],
            'name' => $prize['name'],
            'img'  => $prize['img'] ? UrlUtils::toAbsoluteUrl($prize['img']) : '',
        ];
    }

    /**
     * Get lottery record by lottery code
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
        // 1. Find the lottery code
        $lotteryCode = LotteryCodes::where('code', $code)->find();
        if (!$lotteryCode) {
            throw new OperateException('Invalid lottery code');
        }

        if (!$lotteryCode->is_used) {
            throw new OperateException('This lottery code has not been used yet');
        }

        // 2. Find the lottery record
        $drawRecord = DrawRecords::where('code_id', $lotteryCode->id)->find();
        if (!$drawRecord) {
            // Under normal circumstances, a used code must have a record. This is defensive programming.
            throw new OperateException('No corresponding lottery record found');
        }

        // 3. Get prize information
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
