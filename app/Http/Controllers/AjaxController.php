<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Dirape\Token\Token;
use Illuminate\Support\Facades\Storage;

use App\cms_users;
use App\cms_uploads;
use File;


class AjaxController extends Controller
{
    /**
     * Show the profile for the given user.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public $vars = array();

    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
            return $next($request);
        });
        set_time_limit(8000000);
    }



    public function register(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($request->all(), [
            "name" => 'required|max:50',
            "email" => 'required|max:200|unique:cms_users,email',
            "password" => 'required|max:40'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            if ($errors->has('email')) {
                $errorInfo = $errors->first('email');
            } else if ($errors->has('password')) {
                $errorInfo = $errors->first('password');
            } else if ($errors->has('name')) {
                $errorInfo = $errors->first('name');
            } else {
                $errorInfo = 'Missing information';
            }
            $return_response = new \stdClass();
            $return_response->is_success = false;
            $return_response->error_description = $errorInfo;

            return response()->json($return_response, 200);
        }

        $input['password'] = Hash::make($request->input('password'));
        $cms_users = new cms_users;
        $cms_users->name = $input['name'];
        $cms_users->email = $input['email'];
        $cms_users->password = $input['password'];
        $cms_users->save();

        $return_response = array(
            "is_success" => true,
            "data" => "User Successfully register. Please Login"
        );

        return response()->json($return_response);
    }

    public function login(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($request->all(), [
            "email" => 'required|max:200',
            "password" => 'required|max:40'
        ]);
        if ($validator->fails()) {

            $errors = $validator->errors();
            if ($errors->has('email')) {
                $errorInfo = $errors->first('email');
            } else if ($errors->has('password')) {
                $errorInfo = $errors->first('password');
            } else {
                $errorInfo = 'Missing information';
            }
            $return_response = new \stdClass();
            $return_response->is_success = false;
            $return_response->error_description = $errorInfo;

            return response()->json($return_response, 200);
        }
        $return_response = array(
            "is_success" => false,
            'error_description' => 'Invalid login information.'  //error handling
        );


        $email = $request->input('email');
        $password = $request->input('password');

        $user = cms_users::where(function ($query) use ($email) {
            $query->where('email', $email);
        })->first();
        if ($user) {
            $id = $user->username;
            if (Hash::check($password, $user->password)) {
                $user->user_token = $this->update_user_token($id);
                $user->msg = 'Login Success';
                $return_response = array(
                    "is_success" => true,
                    'data' => $user
                );
            }
        }

        return response()->json($return_response);
    }

    public function update_user_token($id)
    {
        $user_token = (new Token())->Unique('cms_users', 'user_token', 60);
        DB::table('cms_users')
            ->where('username', $id)
            ->update(
                [
                    'user_token' =>  $user_token
                ]
            );

        return $user_token;
    }

    public function logout(Request $request)
    {
        $username = $request->input('username');
        DB::table('cms_users')
            ->where('username', $username)
            ->update(['user_token' => '']);

        $return_response = array(
            "is_success" => true,
            "data" => "logout"
        );
        return response()->json($return_response);
    }

    public function upload(Request $request)
    {

        $api_token = $request->api_token;
        $remark = $request->remark;

        if ($api_token == null) {
            $api_token = "";
        }

        $files = $files_data = array();
        $error = false;

        $cms_users = cms_users::where('user_token', $api_token)->first();

        $username = 0;
        if ($cms_users != null) {
            $username = $cms_users->username;
        } elseif ($request->user()) {
            $username = $request->user()->username;
        }

        if (isset($_FILES) && is_array($_FILES) && sizeOf($_FILES) > 0) {

            foreach ($_FILES as $file) {
                if ($file['error'] == 1) {
                    $size = $this->file_upload_max_size() / 1024 / 1024;

                    $return_response = array(
                        "is_success" => false,
                        'error_description' => 'Only accept file format in png/jpg/svg, and the size limit is ' . $size . 'MB.'  //error handling
                    );
                    return response()->json($return_response);
                }
            }

            $directory = 'uploads' . ($api_token == 'undefined' ? '' : ('/' . $api_token));

            if (!Storage::disk('s3')->exists($directory)) {
                Storage::disk('s3')->makeDirectory($directory);
            }

            $path_parts = pathinfo($file['name']);
            $unqiue_flag = false;
            do {
                $uid = uniqid();
                if ($path_parts['extension'] != "") {
                    $uid .= '.' . $path_parts['extension'];
                }
                $ext = $path_parts['extension'];
                if (!Storage::disk('s3')->exists($directory . '/' . $uid)) {
                    $unqiue_flag = true;
                }
            } while (!$unqiue_flag);

            if ($ext == "svg" || $ext == "") {
                $info = array(100, 100);
            } else {
                $info = getimagesize($file['tmp_name']);
            }
            $files_data['info'] = $info;
            try {

                $tmpFileName = storage_path('app/tmp/' . md5($uid));
                file_put_contents($tmpFileName, file_get_contents($file['tmp_name']));

                Storage::disk('s3')->put($directory . '/' . $uid, file_get_contents($tmpFileName), 'public');
                @unlink($tmpFileName);
            } catch (\Exception $e) {

                $return_response = array(
                    "is_success" => false,
                    'error_description' => $e->getMessage()
                );
                return response()->json($return_response);
            }

            if (!is_array($info)) {
                $error = true;
            } else {
                $cms_uploads = new cms_uploads;
                $cms_uploads->username = $username;
                $cms_uploads->name = $uid;
                $cms_uploads->preview_url = Storage::disk('s3')->url($directory . '/' . $uid);
                $cms_uploads->remark = $remark;
                $cms_uploads->api_token = $api_token;
                $cms_uploads->created_at = date('Y-m-d H:i:s');
                $cms_uploads->updated_at = date('Y-m-d H:i:s');
                if ($username) {
                    $cms_uploads->save();
                }
            }
        } else {
            $error = true;
        }
        if (!$error) {
            $newdata = new \stdClass();
            $newdata->msg = 'Upload Success.';
        }
        $return_response = array(
            "is_success" => ($error ? false : true),
            "data" => ($error ? [] : $newdata),
            'error_description' => ($error ? "Cannot analyse file is in image format." : "")
        );

        return response()->json($return_response);
    }

    public function getUploadImages(Request $request)
    {
        $api_token = $request->input("api_token");
        $offset = $request->input("offset");
        $limit = $request->input("limit");
        $keyword = $request->input("keyword");

        $count_elememt = 0;
        $cms_users = cms_users::where('user_token', $api_token)->first();
        $username = 0;
        if ($cms_users != null) {
            $username = $cms_users->username;
        }
        $elements = [];
        if ($keyword) {
            $elements = DB::table('cms_uploads')
                ->leftJoin('cms_users', 'cms_uploads.username', '=', 'cms_users.username')
                ->where('cms_users.name', 'like', '%' . $keyword . '%')
                ->orWhere("cms_users.email", "like", "%" . $keyword . "%");
        } else {
            $elements = DB::table('cms_uploads')
                ->leftJoin('cms_users', 'cms_uploads.username', '=', 'cms_users.username');
        }
        $totalElements = $elements;

        $totalElements->orderby("cms_uploads.created_at", "desc");
        $totalElements = $totalElements->get();

        if ($offset != "" && intVal($offset) > -1) {
            $elements = $elements->offset($offset);
        } else {
            $offset = 0;
        }
        if ($limit != "" && intVal($limit) > 0) {
            $elements = $elements->limit($limit);
        } else {
            $limit = 12;
        }

        $elements->orderby("cms_uploads.created_at", "desc");
        $elements = $elements->get();

        $menu_elements = [];
        if (count($elements) >= 0) {
            foreach ($elements as $element) {
                $img = new \stdClass();
                $img->original = $element->preview_url;
                $img->thumbnail = $element->preview_url;
                $img->remark = $element->remark;
                $img->username = $element->name;
                $img->created_at = $element->created_at;
                array_push($menu_elements, $img);
            }

            $data = new \stdClass();
            $data->elements = $menu_elements;
            $data->elementCount = count($menu_elements);
            $data->totalElement = count($totalElements);
            $return_arr = array(
                "is_success" => true,
                'data' => $data
            );
        } else {
            $return_arr = array(
                "is_success" => false,
                'data' => $elements,
                'error_description' => "No Result or Invalid Parameter"
            );
        }

        return response()->json($return_arr);
    }
}
