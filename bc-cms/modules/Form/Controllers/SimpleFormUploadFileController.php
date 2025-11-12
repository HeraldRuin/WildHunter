<?php
namespace Modules\Form\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Form\FormBuilder;
use Modules\Form\Traits\HasFormFeatures;
use Modules\Media\Rules\FileExtRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class SimpleFormUploadFileController extends Controller
{
    use HasFormFeatures;

    public function index(Request $request)
    {
        $fileName = 'file';

        $file = $request->file($fileName);
        $driver = config('filesystems.default','uploads');

        try {
            $this->validatePrivateFile($request);
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage());
        }
        $check = $file->store('form-data/'.date('Y/m/d'));

        if ($check) {
            try {
                $path = str_replace('form-data/','',$check);
                return $this->sendSuccess(['data' => [
                    'path'=>$path,
                    'name'=>Str::slug($file->getClientOriginalName()),
                    'size'=>$file->getSize(),
                    'file_type'=>$file->getMimeType(),
                    'file_extension'=> $file->getClientOriginalExtension(),
                    'download'=>route('simple-form.upload-preview',['path'=>$path], false),
                    'driver'=>$driver,
                    'is_image'=>is_image_data([
                        'file_type'=>$file->getMimeType(),
                        'file_extension'=> $file->getClientOriginalExtension(),
                    ])
                ]]);

            } catch (\Exception $exception) {

                Storage::delete($check);

                return $this->sendError($exception->getMessage());
            }
        }
        return $this->sendError(__("Can not upload the file"));
    }

    public function validatePrivateFile($request)
    {
        $form = $this->getForm($request);
        if(empty($form)){
            throw new \Exception(__('Form not found'));
        }

        $field_id = $request->input('field_id');
        if(empty($field_id)){
            throw new \Exception(__('Field ID is required'));
        }

        $field = $this->findFieldInForm($form, $field_id);
        if(empty($field)){
            throw new \Exception(__('Field not found'));
        }

        $rules = ['required','file'];
        if(!empty($field['extensions'])){
            $rules[] = 'mimes:'.implode(',',$field['extensions']);
            $rules[] = 'extensions:'.implode(',',$field['extensions']);
        }
        if(!empty($field['mime_types'])){
            $rules[] = 'mimetypes:'.implode(',',$field['mime_types']);
        }
        if(!empty($field['max_size'])){
            $rules[] = 'max:'.round($field['max_size']);
        }
        // TODO: max_width and max_height


        $validator = Validator::make($request->all(),[
            'file' =>$rules
        ]);
        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first('file'));
        }

        return true;

    }


    protected function getForm($request)
    {
        $options = $request->input('options');
        $form = app(FormBuilder::class);
        if(!empty($options['provider'])){
            $provider = $form::getProvider($options['provider']);
            if($provider){
                return app()->call($provider);
            }
        }
        
    }

    public function preview()
    {
        $path = request()->input('path');
        if(empty($path)){
            abort(404);
        }
        $path = 'form-data/'.$path;

        if(!Storage::exists($path)){
            abort(404);
        }

        $mime = Storage::mimeType($path);
        return response(Storage::get($path), 200)
            ->header('Content-Type', $mime);
    }
}


