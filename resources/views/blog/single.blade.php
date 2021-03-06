@extends('main')
<?php $titleTag = htmlspecialchars($post->title); ?>
@section('title', "| $titleTag")

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <img src="{{ asset('images/' . $post->image) }}" alt="Post Image">
            <h1>{{ $post->title }}</h1>
            <p>{{ strip_tags($post->body) }}</p>
            <hr>
            <p>Posted In: {{ $post->category->name }}</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h3 class="comments-title"><i class="fa fa-comment" aria-hidden="true"></i> {{ $post->comments()->count() }} Comments</h3>
            @foreach($post->comments as $comment)
                <div class="comment">
                    <div class="author-info">
                        <img src="{{'https://www.gravatar.com/avatar/' . md5(strtolower(trim($comment->email))) . '?s=50&d=mm'}}" alt="Author's image" class="author-image">
                        <div class="author-name">
                            <h4>{{ $comment->name }}</h4>
                            <p class="author-time">{{ date('F nS, Y - g:i A' ,strtotime($comment->created_at)) }}</p>
                        </div>
                    </div>
                    <div class="comment-content">
                        {{ $comment->comment }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 offset-md-2" id="comment-form" style="margin-top:50px;">
            {!! Form::open(['route' => ['comments.store', $post->id], 'method' => 'POST']) !!}
                <div class="row">
                    <div class="col-md-6">
                        {{Form::label('name', 'Name:')}}
                        {{Form::text('name', null, ['class' => 'form-control'])}}
                    </div>
                    <div class="col-md-6">
                        {{Form::label('email', 'Email:')}}
                        {{Form::email('email', null, ['class' => 'form-control'])}}
                    </div>
                    <div class="col-md-12">
                        {{Form::label('comment', 'Comment:')}}
                        {{Form::textarea('comment', null, ['class' => 'form-control', 'rows' => '5'])}}

                        {{Form::submit('Add Comment', ['class' => 'btn btn-success btn-block btn-h1-spacing'])}}
                    </div>
                </div>
            {!! Form::close() !!}
        </div>
    </div>
@endsection