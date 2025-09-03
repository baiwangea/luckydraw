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

namespace app\backend\controller\lottery;

use app\backend\service\lottery\DrawRecordsService;
use app\backend\service\lottery\PrizesService;
use app\common\basics\Backend;
use app\common\utils\AjaxUtils;
use think\db\exception\DbException;
use think\response\Json;
use think\response\View;

/**
 * 抽奖记录控制器
 * @Apidoc\Group("lottery")
 */
class RecordController extends Backend
{
    /**
     * 抽奖记录管理
     *
     * @return View|Json
     * @throws DbException
     */
    public function index(): View|Json
    {
        if ($this->isAjaxGet()) {
            try {
                $list = DrawRecordsService::lists($this->request->get());
                return json([
                    'code'  => 0,
                    'msg'   => 'success',
                    'count' => $list['count'],
                    'data'  => $list['list']
                ]);
            } catch (DbException $e) {
                return AjaxUtils::error($e->getMessage());
            }
        }

        return view('lottery/draw_record/index', [
            'prizes' => PrizesService::options()
        ]);
    }

    /**
     * 抽奖记录详情
     *
     * @return View
     */
    public function detail(): View
    {
        $id = intval($this->request->get('id'));
        try {
            $detail = DrawRecordsService::detail($id);
        } catch (DbException $e) {
            $this->error($e->getMessage());
        }

        return view('lottery/draw_record/detail', [
            'detail' => $detail ?? []
        ]);
    }
}
