<?php /*
  - $errors           - optional; MessageBag instance
  - $post             - Post instance
  - $markups          - array of markup names ('githubmarkdown', 'uversewiki', etc.)
  - $textPlaceholder  - default text for textarea
*/?>

@extends('habravel::page')

@section('content')
  @include('habravel::part.uheader', array(), array())

  <form action="{{{ Habravel\Core::url() }}}/edit" method="post" class="hvl-pedit-form"
        data-hvl-post="{{{ $post->toJSON() }}}">
    <input type="hidden" name="_token" value="{{{ csrf_token() }}}">
    <input type="hidden" name="id" value="{{{ $post->id }}}">

    <aside>
      <div class="hvl-pedit-topbtn">
        <noscript>{{{ trans('habravel::g.needJS') }}}</noscript>

        <button class="hvl-btn hvl-pedit-preview" type="submit" name="preview" value="1">
          <i class="hvl-i-zoomw"></i> {{{ trans('habravel::g.edit.preview') }}}
        </button>

        <button class="hvl-btn hvl-pedit-expand" type="button">
          <i class="hvl-i-expandw"></i> {{{ trans('habravel::g.edit.expand') }}}
        </button>
      </div>

      <h1 class="hvl-h1">
        @if ($post->id)
          {{{ $post->caption }}}
        @else
          {{{ trans('habravel::g.edit.titleNew') }}}
        @endif
      </h1>
    </aside>

    <div class="hvl-split-left">
      @if (isset($errors))
        {{ HTML::ul($errors->all(), array('class' => 'hvl-errors')) }}
      @endif

      @if (count($markups) > 1)
        <div class="hvl-pedit-ctl">
          <div class="hvl-pedit-ctl-caption">
            <b>{{{ trans('habravel::g.edit.markup') }}}</b>
            @include('habravel::part.markups', compact('markup'), array('current' => $post->markup))
          </div>
        </div>
      @else
        @include('habravel::part.markups', compact('markup'), array())
      @endif

      <div class="hvl-pedit-ctl">
        <p>
          <input class="hvl-input" name="caption" value="{{{ $post->caption }}}"
                 placeholder="{{{ trans("habravel::g.edit.caption") }}}">
        </p>
      </div>

      <div class="hvl-pedit-ctl">
        <p class="hvl-pedit-ctl-caption">
          <b>{{{ trans('habravel::g.edit.source') }}}</b>
        </p>

        <p>
          <input class="hvl-input" name="sourceName" value="{{{ $post->sourceName }}}"
                 placeholder="{{{ trans("habravel::g.edit.sourceName") }}}">
        </p>

        <p>
          <input class="hvl-input" name="sourceURL" value="{{{ $post->sourceURL }}}"
                 placeholder="{{{ trans("habravel::g.edit.sourceURL") }}}">
        </p>
      </div>

      <div class="hvl-pedit-ctl hvl-pedit-tags">
        <p class="hvl-pedit-ctl-caption">
          <b>{{{ trans('habravel::g.edit.tags') }}}</b>
          <noscript>{{{ trans('habravel::g.needJS') }}}</noscript>
        </p>
      </div>

      <div class="hvl-pedit-ctl hvl-pedit-polls">
        <p class="hvl-pedit-ctl-caption">
          <b>{{{ trans('habravel::g.edit.polls') }}}</b>
          <noscript>{{{ trans('habravel::g.needJS') }}}</noscript>
        </p>

        <p>
          <input class="hvl-input" name="polls[0][caption]"
                 placeholder="{{{ trans('habravel::g.edit.poll') }}}">
        </p>

        <p>
          <label>
            <input type="radio" name="polls[0][multiple]" value="0">
            {{{ trans('habravel::g.edit.pollSingle') }}}
          </label>

          <label>
            <input type="radio" name="polls[0][multiple]" value="1">
            {{{ trans('habravel::g.edit.pollMultiple') }}}
          </label>
        </p>

        <p class="hvl-pedit-poll-opt">
          <b class="hvl-pedit-poll-opt-num">1)
          </b><input class="hvl-input" name="options[0][0][caption]"
                     placeholder="{{{ trans('habravel::g.edit.option') }}}">
        </p>

        <p>
          <button type="button" class="hvl-btn hvl-pedit-poll-add">
            {{{ trans('habravel::g.edit.addPoll') }}}
          </button>
        </p>
      </div>

      <div class="hvl-pedit-ctl">
        <p class="hvl-pedit-ctl-caption">
          <button type="submit" class="hvl-btn hvl-btn-orange hvl-btn-20" name="publish" value="1">
            {{{ trans('habravel::g.edit.publish') }}}
          </button>
        </p>
      </div>
    </div>

    <div class="hvl-split-right">
      <textarea class="hvl-input hvl-pedit-text" name="text" data-sqa="wr - w$body{pb}"
                rows="20" cols="50"
                placeholder="{{{ $textPlaceholder }}}">{{{ $post->text }}}</textarea>
    </div>
  </form>
@stop