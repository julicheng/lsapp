@extends('layouts.app')

@section('content')
    <h1>Edit Post</h1>
    {{--  route says it needs to be put request but it can only be a post or get so got to spoof a request?  --}}
    {!! Form::open(['action' => ['PostsController@update', $post->id], 'method' => 'POST', 'enctype' => 'multipart/form-data']) !!}
        <div class="form-group">
            {{--  label for title then actual text for the label  --}}
            {{Form::label('title', 'Title')}} 
            {{--  name then value then attributes  --}}
            {{Form::text('title', $post->title, ['class' => 'form-control', 'placeholder' => 'Title'])}}
        </div>
        <div class="form-group">
            {{Form::label('body', 'Body')}} 
            {{Form::textarea('body', $post->body, ['id' => 'article-ckeditor', 'class' => 'form-control', 'placeholder' => 'Body Text'])}}
        </div>
        <div class="form-group">
            {{--  cover_image is the column name in table  --}}
            {{Form::file('cover_image')}}
        </div>
        {{--  here we spoof the request  --}}
        {{Form::hidden('_method', 'PUT')}}
        {{--  name and attributes  --}}
        {{Form::submit('Submit', ['class' => 'btn btn-primary'])}}
    {!! Form::close() !!}
@endsection