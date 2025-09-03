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

use app\backend\service\lottery\PrizesService;
use app\common\basics\Backend;
use app\common\exception\OperateException;
use app\common\utils\AjaxUtils;
use think\db\exception\DbException;
use think\response\Json;
use think\response\View;

/**
 * 奖品控制器
 * @Apidoc\Group("lottery")
 */
class PrizeController extends Backend
{
    /**
     * 奖品管理
     *
     * @return View|Json
     * @throws DbException
     */
    public function index(): View|Json
    {
        if ($this->isAjaxGet()) {
            $list = PrizesService::lists($this->request->get());
            return json([
                'code'  => 0,
                'msg'   => 'success',
                'count' => $list['count'],
                'data'  => $list['list']
            ]);
        }
        return view('lottery/prize/index');
    }

    /**
     * 添加奖品
     *
     * @return View|Json
     */
    public function add(): View|Json
    {
        if ($this->isAjaxPost()) {
            try {
                PrizesService::add($this->request->post());
                return AjaxUtils::success('新增成功');
            } catch (OperateException $e) {
                return AjaxUtils::error($e->getMessage());
            }
        }
        return view('lottery/prize/add');
    }

    /**
     * 编辑奖品
     *
     * @return View|Json
     */
    public function edit(): View|Json
    {
        if ($this->isAjaxPost()) {
            try {
                PrizesService::edit($this->request->post());
                return AjaxUtils::success('更新成功');
            } catch (OperateException $e) {
                return AjaxUtils::error($e->getMessage());
            }
        }

        $id = intval($this->request->get('id'));
        return view('lottery/prize/edit', [
            'detail' => PrizesService::detail($id)
        ]);
    }

    /**
     * 删除奖品
     *
     * @return Json
     */
    public function del(): Json
    {
        if ($this->isAjaxPost()) {
            try {
                $ids = (array)$this->request->post('ids');
                PrizesService::del($ids);
                return AjaxUtils::success('删除成功');
            } catch (OperateException $e) {
                return AjaxUtils::error($e->getMessage());
            }
        }
        return AjaxUtils::error('请求方式错误');
    }

    /**
     * 获取奖品选项
     *
     * @return Json
     */
    public function options(): Json
    {
        try {
            $list = PrizesService::options();
            return AjaxUtils::success('获取成功', $list);
        } catch (DbException $e) {
            return AjaxUtils::error('查询失败: ' . $e->getMessage());
        }
    }
}
