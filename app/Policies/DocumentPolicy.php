<?php
/**
 * Wizard
 *
 * @link      https://aicode.cc/
 * @copyright 管宜尧 <mylxsw@aicode.cc>
 */

namespace App\Policies;

use App\Repositories\Document;
use App\Repositories\Project;
use App\Repositories\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * 文档权限策略
 *
 * @package App\Policies
 */
class DocumentPolicy
{
    use HandlesAuthorization, GroupHasProjectPrivilege;

    /**
     * 文档编辑权限
     *
     * @param User         $user
     * @param Document|int $page
     *
     * @return bool
     */
    public function edit(User $user = null, $page)
    {
        return $this->groupHasEditPrv($user, $page);
    }

    /**
     * 文档分享权限
     *
     * @param User $user
     * @param      $page
     *
     * @return bool
     */
    public function share(User $user = null, $page)
    {
        return $this->groupHasEditPrv($user, $page);
    }

    /**
     * 分组是否有对页面的编辑权限
     *
     * @param User     $user
     * @param Document $page
     *
     * @return bool
     */
    private function groupHasEditPrv($user = null, $page): bool
    {
        if (empty($user)) {
            return false;
        }

        // 管理员
        if ($user->isAdmin()) {
            return true;
        }

        $page    = $this->getDocument($page);
        $project = $this->getProject($page->project);
        // 项目创建者拥有权限
        if ((int)$project->user_id === (int)$user->id) {
            return true;
        }

        // 分组用户有用写权限
        return $this->groupHasProjectPrivilege($project, $user);
    }

    /**
     * 文档还原权限
     *
     * @param User $user
     * @param      $page
     *
     * @return bool
     */
    public function recover(User $user = null, $page)
    {
        return $this->groupHasEditPrv($user, $page);
    }

    /**
     * 是否是文档创建者
     *
     * @param User $user
     * @param      $page
     *
     * @return bool
     */
    private function isOwner(User $user = null, $page)
    {
        if (empty($user)) {
            return false;
        }

        $page = $this->getDocument($page);

        return (int)$user->id === (int)$page->user_id;
    }

    private function getDocument($page): Document
    {
        if (!$page instanceof Document) {
            $page = Document::where('id', $page)->firstOrFail();
        }

        return $page;
    }

    private function getProject($project): Project
    {
        if (!$project instanceof Project) {
            $project = Project::where('id', $project)->firstOrFail();
        }

        return $project;
    }
}
