<?php

namespace Abrachist\Webadmin\Controllers;

use App\Http\Controllers\Controller;
use Artisan;
use File;
use Illuminate\Http\Request;
use Response;
use Session;
use View;

class GenerateMasterController extends Controller
{
    /**
     * Display generator.
     *
     * @return Response
     */
    public function getMaster()
    {
        return view('generator-master');
    }

    /**
     * Process generator.
     *
     * @return Response
     */
    public function postMaster(Request $request)
    {
        $commandArg = [];
        $commandArg['name'] = $request->crud_name;

        if ($request->has('fields')) {
            $fieldsArray = [];
            $validationsArray = [];
            $x = 0;
            foreach ($request->fields as $field) {
                if ($request->fields_required[$x] == 1) {
                    $validationsArray[] = $field;
                }

                $fieldsArray[] = $field . '#' . $request->fields_type[$x];

                $x++;
            }

            $commandArg['--fields'] = implode(";", $fieldsArray);
        }

        if (!empty($validationsArray)) {
            $commandArg['--validations'] = implode("#required;", $validationsArray) . "#required";
        }

        if ($request->has('route')) {
            $commandArg['--route'] = $request->route;
        }

        if ($request->has('view_path')) {
            $commandArg['--view-path'] = $request->view_path;
        }

        if ($request->has('controller_namespace')) {
            $commandArg['--controller-namespace'] = $request->controller_namespace;
        }

        if ($request->has('model_namespace')) {
            $commandArg['--model-namespace'] = $request->model_namespace;
        }

        if ($request->has('route_group')) {
            $commandArg['--route-group'] = $request->route_group;
        }

        try {
            Artisan::call('cyom:generate', $commandArg);

            $name = camel_case($commandArg['name']);
            $routeName = ($commandArg['--route-group']) ? $commandArg['--route-group'] . '/' . snake_case($name, '-') : snake_case($name, '-');

            \DB::table('module')->insert([
                'name' => $name,
                'section' => 'modules',
                'url' => $routeName
            ]);

            Artisan::call('migrate');
        } catch (\Exception $e) {
            return Response::make($e->getMessage(), 500);
        }

        Session::flash('flash_message', 'Your CRUD has been generated. See on the menu.');

        return redirect('generator/master');
    }
}
