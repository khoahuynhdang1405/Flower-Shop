<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Post\ListPublishedPostsAction;
use App\Actions\Post\ShowPublishedPostAction;
use Illuminate\View\View;

/**
 * Góc nhỏ: danh sách bài đã đăng và trang đọc bài.
 */
class PostController extends Controller
{
    public function index(ListPublishedPostsAction $listPosts): View
    {
        return view('posts.index', ['posts' => $listPosts->execute()]);
    }

    public function show(string $slug, ShowPublishedPostAction $showPost): View
    {
        return view('posts.show', $showPost->execute($slug));
    }
}
