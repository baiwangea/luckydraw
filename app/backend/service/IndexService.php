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

namespace app\backend\service;


use app\common\basics\Service;
use app\common\model\auth\AuthAdmin;
use app\common\model\auth\AuthMenu;
use app\common\model\auth\AuthPerm;
use app\common\model\lottery\DrawRecords;
use app\common\model\lottery\Prizes;
use app\common\utils\ConfigUtils;
use app\common\utils\UrlUtils;
use think\facade\Db;

/**
 * 主页服务类
 */
class IndexService extends Service
{
    /**
     * 主页
     *
     * @param int $adminId
     * @param int $roleId
     * @return array
     * @author zero
     */
    public static function index(int $adminId, int $roleId): array
    {
        $where = [];
        if ($adminId !== 1) {
            $authPermModel = new AuthPerm();
            $menuIds = $authPermModel->where(['role_id'=>$roleId])->column('menu_id');
            $where = [['id', 'in', $menuIds]];
        }

        $authMenuModel = new AuthMenu();
        $detail['menus'] = $authMenuModel
            ->withoutField('is_delete,delete_time')
            ->where($where)
            ->where(['is_menu'=>1])
            ->where(['is_delete'=>0])
            ->where(['is_disable'=>0])
            ->order('sort asc, id asc')
            ->select()
            ->toArray();

        $authAdminModel = new AuthAdmin();
        $detail['adminUser'] = $authAdminModel
            ->field('id,username,avatar')
            ->where(['id'=>$adminId])
            ->findOrEmpty()
            ->toArray();

        $sideLogo = ConfigUtils::get('backend', 'side_logo', '');
        $detail['config'] = [
            'side_logo' => UrlUtils::toAbsoluteUrl($sideLogo)
        ];

        return $detail;
    }

    /**
     * 控制台
     *
     * @return array
     * @author zero
     */
    public static function console(): array
    {
        $drawRecordsModel = new DrawRecords();
        $prizesModel = new Prizes();

        $todayStart = strtotime(date("Y-m-d 00:00:00"));
        $todayEnd = strtotime(date("Y-m-d 23:59:59"));

        // 卡片统计 (只统计 status in [0,1] 的有效抽奖)
        $detail['lottery'] = [
            'today_draw_count' => $drawRecordsModel->where('status', 'in', [0, 1])->where(Db::raw("draw_time BETWEEN {$todayStart} AND {$todayEnd}"))->count(),
            'today_win_count'  => $drawRecordsModel->where('status', 1)->where(Db::raw("draw_time BETWEEN {$todayStart} AND {$todayEnd}"))->count(),
            'prize_count'      => $prizesModel->where('is_delete', 0)->count(),
            'user_count'       => $drawRecordsModel->count('DISTINCT user_email'),
        ];

        // 系统版本
        $detail['version'] = config('project.version');

        return $detail;
    }

    /**
     * 控制台统计 (最近7日趋势)
     *
     * @return array
     * @author zero
     */
    public static function statistics(): array
    {
        // 1. 初始化最近7天的数据结构
        $dateMap = [];
        for ($i = 6; $i >= 0; $i--) {
            $dateKey = date('Y-m-d', strtotime("-{$i} days"));
            $dateMap[$dateKey] = [
                'date'  => date('m-d', strtotime($dateKey)),
                'draws' => 0,
                'wins'  => 0
            ];
        }

        // 2. 一次性获取最近7天所有有效抽奖的原始记录
        $sevenDaysAgoTimestamp = strtotime(date('Y-m-d 00:00:00', strtotime('-6 days')));
        $records = Db::table((new DrawRecords())->getTable())
            ->where('draw_time', '>=', $sevenDaysAgoTimestamp)
            ->where('status', 'in', [0, 1]) // 关键: 只统计有效抽奖
            ->field(['draw_time', 'status'])
            ->select()
            ->toArray();

        // 3. 在PHP中循环遍历, 精确累加每日数据
        foreach ($records as $record) {
            $dateKey = date('Y-m-d', $record['draw_time']);
            if (isset($dateMap[$dateKey])) {
                // 累加抽奖次数
                $dateMap[$dateKey]['draws']++;
                // 如果中奖, 则累加中奖次数
                if ($record['status'] == 1) {
                    $dateMap[$dateKey]['wins']++;
                }
            }
        }

        // 4. 格式化为图表所需的最终数组
        $dateMap = array_values($dateMap);
        return [
            'dates' => array_column($dateMap, 'date'),
            'draws' => array_column($dateMap, 'draws'),
            'wins'  => array_column($dateMap, 'wins'),
        ];
    }
}