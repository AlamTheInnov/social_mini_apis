<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'media_type' => 'required|string|in:image,video',
            'media_link' => 'required',
            'media_thumbnail' => 'required_if:media_type,video',
            'visibility' => 'required|in:public,followers'
        ]);

        $post = new Post();

        if ($request->hasFile('media_link')) {
            if ($request->media_type == 'image') {
                $request->validate([
                    'media_link' => 'image|mimes:jpg,jpeg,png,gif|max:5120'
                ]);
            } else {
                $request->validate([
                    'media_link' => 'mimetypes:video/avi,video/mpeg,video/quicktime,video/mp4|max:5120'
                ]);

                if ($request->file('media_thumbnail')) {
                    $request->validate([
                        'media_thumbnail' => 'image|mimes:jpg,jpeg,png,gif|max:5120'
                    ]);

                    $post->media_thumbnail = $request->file('media_thumbnail')->store('media_link');
                }
            }

            $post->media_link = $request->file('media_link')->store('media_link');
        }

        $post->user_id = $request->user()->id;
        $post->media_type = $request->media_type;
        $post->visibility = $request->visibility;
        $post->body = $request->body;

        if ($post->save()) {
            return response()->json($post, 200);
        } else {
            return response()->json([
                'message' => 'Some error occurred, please try again'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function edit(Post $post)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Post $post)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
        //
    }
}
