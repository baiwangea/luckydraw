<?php
declare (strict_types = 1);

namespace app\backend\controller\vote;

use app\backend\service\vote\VoteService;
use app\common\basics\Backend;
use app\common\exception\OperateException;
use app\common\utils\AjaxUtils;
use think\db\exception\DbException;
use think\response\Json;
use think\response\View;

/**
 * 投票管理控制器
 */
class VoteController extends Backend
{
    /**
     * 投票人列表
     *
     * @return View|Json
     * @throws DbException
     */
    public function index(): View|Json
    {
        if ($this->isAjaxGet()) {
            $list = VoteService::lists($this->request->get());
            return json([
                'code'  => 0,
                'msg'   => 'success',
                'count' => $list['count'],
                'data'  => $list['list']
            ]);
        }
        return view('vote/vote/index');
    }

    /**
     * 增加票数
     *
     * @return Json
     */
    public function addBallot(): Json
    {
        if ($this->isAjaxPost()) {
            try {
                VoteService::addBallot($this->request->post());
                return AjaxUtils::success('上票成功');
            } catch (OperateException $e) {
                return AjaxUtils::error($e->getMessage());
            }
        }
        return AjaxUtils::error('请求方式错误');
    }
}
