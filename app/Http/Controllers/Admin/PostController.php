<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Post\CreatePostAction;
use App\Actions\Post\DeletePostAction;
use App\Actions\Post\ListAdminPostsAction;
use App\Actions\Post\RenderPostContentAction;
use App\Actions\Post\UpdatePostAction;
use App\Actions\Post\UploadPostImageAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ListPostsRequest;
use App\Http\Requests\Admin\PreviewPostRequest;
use App\Http\Requests\Admin\StorePostRequest;
use App\Http\Requests\Admin\UpdatePostRequest;
use App\Http\Requests\Admin\UploadPostImageRequest;
use App\Models\Post;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Admin › Chuyện Hoa: danh sách bài, khung soạn thảo (viết mới / chỉnh sửa), xem trước và chèn ảnh.
 */
class PostController extends Controller
{
    public function index(ListPostsRequest $request, ListAdminPostsAction $listPosts): View
    {
        $filters = $request->toFilters();

        return view('admin.posts.index', [
            ...$listPosts->execute($filters, CarbonImmutable::now()),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        return view('admin.posts.form', ['post' => new Post(['is_published' => false])]);
    }

    public function store(StorePostRequest $request, CreatePostAction $createPost): RedirectResponse
    {
        $post = $createPost->execute($request->toData(), (int) auth('admin')->id());

        return redirect()->route('admin.posts.edit', $post)
            ->with('status', __($post->is_published ? 'admin.posts.flash.published' : 'admin.posts.flash.drafted', ['title' => $post->title]));
    }

    public function edit(Post $post): View
    {
        return view('admin.posts.form', ['post' => $post]);
    }

    public function update(UpdatePostRequest $request, Post $post, UpdatePostAction $updatePost): RedirectResponse
    {
        $updatePost->execute($post, $request->toData());

        return redirect()->route('admin.posts.edit', $post)
            ->with('status', __($post->is_published ? 'admin.posts.flash.published' : 'admin.posts.flash.drafted', ['title' => $post->title]));
    }

    public function destroy(Post $post, DeletePostAction $deletePost): RedirectResponse
    {
        $deletePost->execute($post);

        return redirect()->route('admin.posts')->with('status', __('admin.posts.flash.deleted', ['title' => $post->title]));
    }

    public function preview(PreviewPostRequest $request, RenderPostContentAction $renderContent): JsonResponse
    {
        return response()->json(['html' => $renderContent->execute((string) $request->validated('content'))->html]);
    }

    public function uploadImage(UploadPostImageRequest $request, UploadPostImageAction $uploadImage): JsonResponse
    {
        return response()->json(['url' => $uploadImage->execute($request->file('image'))], 201);
    }
}
