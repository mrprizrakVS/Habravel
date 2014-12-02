<?php namespace Habravel\Controllers;

use Habravel\Models\User as UserModel;

class User extends BaseController {
  function __construct() {
    parent::__construct();
    $this->beforeFilter('csrf', array(
      'only' => array(
        'voteUpByname',
        'voteDownByName',
        'editMyInfo',
        'changeMyPassword',
        'changeMyAvatar',
      ),
    ));
  }

  function voteUpByName($name = '') {
    return Poll::voteOn(UserModel::whereName($name)->first(), true);
  }

  function voteDownByName($name = '') {
    return Poll::voteOn(UserModel::whereName($name)->first(), false);
  }

  function showCurrent() {
    user() or App::abort(401);
    return Redirect::to(user()->url());
  }

  function showByName($name = '') {
    $user = UserModel::whereName($name)->first();
    return $this->show($user);
  }

  function showById($id = 0) {
    $user = UserModel::find($id);
    return $this->show($user);
  }

  function show($user) {
    if ($user) {
      return View::make('habravel::user', compact('user'));
    } else {
      App::abort(404);
    }
  }

  function logout() {
    user(false);
    return Redirect::to(\Habravel\url());
  }

  function showEditMyInfo() {
    if (user()) {
      $user = user();
      return View::make('habravel::edit.profile', compact('user'));
    } else {
      App::abort(404);
    }
  }

  function showChangeMyPassword() {
    if (user()) {
      $user = user();
      return View::make('habravel::edit.password', compact('user'));
    } else {
      App::abort(404);
    }
  }

  function showChangeMyAvatar() {
    if (user()) {
      $user = user();
      return View::make('habravel::edit.avatar', compact('user'));
    } else {
      App::abort(404);
    }
  }

  function editMyInfo() {
    $user = user();
    $input = Input::get();

    $errors = new MessageBag;

    $validation = new UserModel;

    $validation->setRawAttributes($input + $user->getAttributes());
    $validation->validateAndMerge($errors);

    if (count($errors)) {
      return View::make('habravel::edit.profile', compact('input', 'errors', 'user'));
    } else {
      $user->site = Input::get('site');
      $user->bitbucket = Input::get('bitbucket');
      $user->github = Input::get('github');
      $user->facebook = Input::get('facebook');
      $user->twitter = Input::get('twitter');
      $user->vk = Input::get('vk');
      $user->jabber = Input::get('jabber');
      $user->skype = Input::get('skype');
      $user->icq = Input::get('icq');
      $user->info = Input::get('info');

      $user->save();

      return Redirect::to($user->url());
    }
  }

  function changeMyPassword() {
    $user = user();
    $accepted = \Hash::check(Input::get('password'), $user->password);
    $minPassword = \Config::get('habravel::g.minPassword');
    $input = array(
      'password'                 => Input::get('password'),
      'hash'                     => $accepted,
      'newPassword'              => Input::get('newPassword'),
      'newPassword_confirmation' => Input::get('newPassword_confirmation'),
    );
    $rules = $user::$changePasswordRule;
    $rules['newPassword'] .= \Config::get('habravel::g.minPassword');

    $validator = \Validator::make($input, $rules);

    if ($validator->passes()) {
      $user->password = \Hash::make($input['newPassword']);
      $user->save();
      return Redirect::to($user->url());
    } else {
      return Redirect::back()->withErrors($validator->errors());
    }
  }

  function changeMyAvatar() {
    $user = user();
    $rules = $user::$avatarImageRule;

    $validator = \Validator::make(Input::all(), $rules);

    if ($validator->passes()) {
      $file = \Input::file('avatar');
      $dir = public_path('packages/proger/habravel/avatars/');
      $mime = $file->getClientOriginalExtension();
      $name = $user->id.'.png';
      $avatar = $user->avatar;
      if (is_dir($dir) === false) {
        \File::makeDirectory($dir, 0775, true);
      }
      $destination = $dir.$name;
      $width = \Config::get('habravel::g.avatarWidth');
      $height = \Config::get('habravel::g.avatarHeight');
      if (UserModel::imageResize($file, $destination, $width, $height)) {
        $user->avatar = $name;
        $user->save();
      } else {
        \App::abort(500, 'Cannot save '.get_class().'.');
      }
      \File::delete($file);
      return Redirect::to(user()->url());
    } else {
      return Redirect::back()->withErrors($validator->errors());
    }
  }

  // GET input:
  // - back=rel/url       - optional; relative to Habravel\url()
  // - bad=0/1            - optional
  function showLogin() {
    if (user()) {
      return Redirect::to(user()->url());
    } else {
      $vars = array(
        'backURL'         => Input::get('back'),
        'badLogin'        => Input::get('bad'),
      );

      return View::make('habravel::login', $vars);
    }
  }

  // POST input:
  // - email=a@b.c        - required if name/login not given
  // - name=nick          - required if name/login not given
  // - login=...          - required if email/name not given; if has '@' is
  //                        looked up as 'email', otherwise looked up by 'name'
  // - password=...
  // - remember=0/1       - optional; defaults to 0
  // - back=rel/url       - optional; relative to Habravel\url()
  function login() {
    \Session::regenerate();   // prevent session fixation.
    $input = Input::get();
    $back = $input['back'] = \Habravel\referer(array_get($input, 'back'));

    $auth = array_only($input, array('email', 'password', 'remember'));
    if (!isset($auth['email'])) {
      $login = array_get($input, 'login');
      if (strrchr($login, '@')) {
        $auth['email'] = $login;
      } else {
        $auth['name'] = $login ?: array_get($input, 'name');
      }
    }

    if (empty($auth['password']) or (empty($auth['email']) and empty($auth['name']))) {
      Input::merge(array('back' => $back));
      return $this->showLogin();
    } elseif ($user = user($auth)) {
      $user->loginTime = new \Carbon\Carbon;
      $user->loginIP = Request::getClientIp();
      return Redirect::to( array_get($input, 'back', $user->url()) );
    } else {
      Input::merge(array('bad' => 1, 'back' => $back));
      return $this->showLogin();
    }
  }

  function showRegister() {
    user(false);
    return View::make('habravel::register', array('input' => array()));
  }

  // POST input:
  // - password=...       - required
  // - email=a@b.c        - required
  // - name=nick          - required
  function register() {
    \Session::regenerate();   // prevent session fixation.

    $user = new UserModel;
    $input = Input::get();
    $errors = new MessageBag;

    $user->name = array_get($input, 'name');
    $user->email = array_get($input, 'email');
    $user->password = \Hash::make(array_get($input, 'password'));
    $user->regIP = Request::getClientIp();

    $copy = new UserModel;
    $copy->setRawAttributes(array_only($input, 'password') + $user->getAttributes());
    $copy->validateAndMerge($errors);

    if (count($errors)) {
      return View::make('habravel::register', compact('input', 'errors'));
    } else {
      if (!$user->poll) {
        $poll = new \Habravel\Models\Poll;
        // System poll captions don't matter, just for pretty database output.
        $poll->caption = '~'.$user->name;
        $poll->save();
        $user->poll = $poll->id;
      }

      $user->save();

      user(array('id' => $user->id, 'password' => $input['password']));
      return Redirect::to($user->url());
    }
  }
}